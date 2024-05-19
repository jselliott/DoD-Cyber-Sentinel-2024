package main

import (
	"bytes"
	"crypto/aes"
	"crypto/cipher"
	"crypto/rand"
	"crypto/rsa"
	"crypto/sha512"
	"crypto/x509"
	"encoding/base64"
	"encoding/pem"
	"fmt"
	"io"
	"log"
	"net/http"
	"time"

	"github.com/google/uuid"
	"github.com/gorilla/mux"
	"github.com/gorilla/websocket"
)

var upgrader = websocket.Upgrader{
	CheckOrigin: func(r *http.Request) bool {
		return true
	},
}

type Chat struct {
	From         uuid.UUID `json:"from"`
	FromName     string    `json:"from_name"`
	To           uuid.UUID `json:"to"`
	Encrypted    bool      `json:"encrypted"`
	EncryptedKey string    `json:"key,omitempty"`
	Message      string    `json:"message"`
}

type IncomingMessage struct {
	MessageType        string            `json:"type"`
	SubscribeCommand   *SubscribeCommand `json:"subscribe,omitempty"`
	UnsubscribeCommand *SubscribeCommand `json:"unsubscribe,omitempty"`
	PubkeyCommand      *PubkeyCommand    `json:"update_pubkey,omitempty"`
	Chat               *Chat             `json:"chat,omitempty"`
}

type Message struct {
	MessageType string      `json:"type"`
	Data        interface{} `json:"data,omitempty"`
}

type Hello struct {
	ClientID uuid.UUID `json:"client_id"`
}

type SubscribeCommand struct {
	Channel uuid.UUID `json:"client_id"`
}

type PubkeyCommand struct {
	ClientID  uuid.UUID `json:"client_id"`
	PubkeyPEM string    `json:"pem"`
}

type GenericCommandResponse struct {
	Message string `json:"message,omitempty"`
	Error   string `json:"error,omitempty"`
}

type Client struct {
	Conn          *websocket.Conn
	ID            uuid.UUID
	Key           *rsa.PublicKey
	AdminKey      *rsa.PublicKey
	FBKey         *rsa.PublicKey
	SubbedToAdmin bool
	Closed        bool
	Send          chan Message
}

var FLAG string
var AdminID = uuid.New()
var FlagbotID = uuid.New()
var AdminPubkey *rsa.PublicKey
var AdminPrivkey *rsa.PrivateKey

var clients = make(map[uuid.UUID]*Client)
var register = make(chan *Client)
var unregister = make(chan *Client)

func main() {

	FLAG = "C1{4_l1ttl3_b1t_ch4tty}"

	log.Println("Generating placeholder admin keypair...")
	AdminPrivkey, AdminPubkey = GenerateKeyPair(2048)
	log.Println("Done!")

	r := mux.NewRouter()
	r.HandleFunc("/", healthCheck)
	r.HandleFunc("/ws", handleConnections)
	http.Handle("/", r)

	// Start handling messages in various channels
	go handleMessages()

	log.Println("server started on :8080")
	err := http.ListenAndServe(":8080", nil)
	if err != nil {
		log.Fatal("ListenAndServe: ", err)
	}
}

func healthCheck(w http.ResponseWriter, r *http.Request) {
	w.Write([]byte("Backend is healthy."))
}

func handleConnections(w http.ResponseWriter, r *http.Request) {
	ws, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		return
	}

	client := &Client{
		ID:            uuid.New(),
		Conn:          ws,
		AdminKey:      AdminPubkey,
		SubbedToAdmin: false,
		Closed:        false,
		Send:          make(chan Message, 10),
	}

	register <- client

	defer func() {
		client.Closed = true
		unregister <- client
		ws.Close()
	}()

	go client.writePump()

	// Send initial messages
	msg := Message{
		MessageType: "hello",
		Data: &Hello{
			ClientID: client.ID,
		},
	}

	client.Send <- msg

	msg = Message{
		MessageType: "system",
		Data: &GenericCommandResponse{
			Message: "Initiating secure connection. Waiting for client to provide RSA key.",
		},
	}

	client.Send <- msg

	go client.flagPump()
	client.readPump()
}

func (c *Client) readPump() {
	for {
		var msg IncomingMessage
		var resp Message
		var adminPubkeyPEM bytes.Buffer

		err := c.Conn.ReadJSON(&msg)
		if err != nil {
			log.Println(err)
			break
		}

		//log.Println("Recieved message type:", msg.MessageType)

		// Handle different command types
		switch msg.MessageType {
		case "subscribe":
			if msg.SubscribeCommand == nil {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "Malformed or invalid command message.",
					},
				}
				c.Send <- resp
			} else {
				// If they are subscribing to the admin channel we start sending them admin's flag messages
				if msg.SubscribeCommand.Channel == AdminID {
					c.SubbedToAdmin = true
					resp = Message{
						MessageType: "success",
						Data: &GenericCommandResponse{
							Message: "Successfully subscribed to channel.",
						},
					}
					c.Send <- resp
				} else if msg.SubscribeCommand.Channel == c.ID {
					resp = Message{
						MessageType: "success",
						Data: &GenericCommandResponse{
							Message: "Successfully subscribed to channel.",
						},
					}
					c.Send <- resp
				} else {
					resp = Message{
						MessageType: "error",
						Data: &GenericCommandResponse{
							Error: "You cannot subscribe to this channel.",
						},
					}
					c.Send <- resp
				}
			}
		case "unsubscribe":
			if msg.SubscribeCommand == nil {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "Malformed or invalid command message.",
					},
				}
				c.Send <- resp
			} else {
				if msg.SubscribeCommand.Channel == AdminID {
					c.SubbedToAdmin = false
					resp = Message{
						MessageType: "success",
						Data: &GenericCommandResponse{
							Message: "Successfully unsubscribed from channel.",
						},
					}
					c.Send <- resp
				} else {
					resp = Message{
						MessageType: "error",
						Data: &GenericCommandResponse{
							Error: "You cannot unsubscribe from this channel.",
						},
					}
					c.Send <- resp
				}
			}
		case "update_pubkey":
			if msg.PubkeyCommand == nil {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "Malformed or invalid command message.",
					},
				}

				c.Send <- resp
				continue
			}
			// Decode from PEM format
			pubPem, _ := pem.Decode([]byte(msg.PubkeyCommand.PubkeyPEM))
			if pubPem == nil {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "Unable to decode PEM format.",
					},
				}
				c.Send <- resp
				continue
			}
			if pubPem.Type != "PUBLIC KEY" {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "PEM type must be RSA public key.",
					},
				}
				c.Send <- resp
				continue
			}
			ifc, key_err := x509.ParsePKIXPublicKey(pubPem.Bytes)
			if key_err != nil {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "Error parsing PEM bytes.",
					},
				}
				c.Send <- resp
				continue
			}
			key, ok := ifc.(*rsa.PublicKey)
			if !ok {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "Malformed or invalid RSA public key.",
					},
				}
				c.Send <- resp
				continue
			}

			// No ID was passed, we assume this is for our own client
			if msg.PubkeyCommand.ClientID == uuid.Nil || msg.PubkeyCommand.ClientID == c.ID {

				// Fill it in just in case they didn't enter anything
				msg.PubkeyCommand.ClientID = c.ID

				c.Key = key
				rmsg := fmt.Sprintf("Public key for client_id '%s' updated.", msg.PubkeyCommand.ClientID)
				resp = Message{
					MessageType: "success",
					Data: &GenericCommandResponse{
						Message: rmsg,
					},
				}

				c.Send <- resp

				temp_pem := &pem.Block{
					Type:  "RSA PUBLIC KEY",
					Bytes: x509.MarshalPKCS1PublicKey(c.AdminKey),
				}

				_ = pem.Encode(&adminPubkeyPEM, temp_pem)

				msg := Message{
					MessageType: "update_pubkey",
					Data: &PubkeyCommand{
						ClientID:  AdminID,
						PubkeyPEM: base64.RawStdEncoding.EncodeToString(adminPubkeyPEM.Bytes()),
					},
				}

				c.Send <- msg

				CT, kCT := encryptMessage(c.Key, "Hello! This is a test message for end-to-end encryption.")

				msg = Message{
					MessageType: "chat",
					Data: &Chat{
						To:           c.ID,
						From:         AdminID,
						FromName:     "Admin",
						Encrypted:    true,
						EncryptedKey: kCT,
						Message:      CT,
					},
				}
				c.Send <- msg

				continue

			} else if msg.PubkeyCommand.ClientID == AdminID {
				c.AdminKey = key
				rmsg := fmt.Sprintf("Public key for client_id '%s' updated.", msg.PubkeyCommand.ClientID)
				resp = Message{
					MessageType: "success",
					Data: &GenericCommandResponse{
						Message: rmsg,
					},
				}

				c.Send <- resp
				continue

			} else {
				resp = Message{
					MessageType: "error",
					Data: &GenericCommandResponse{
						Error: "Invalid client_id specified.",
					},
				}

				c.Send <- resp
			}
		}
		// We discard all regular text messages (more anti-griefing on a shared server)
	}
}

func (c *Client) writePump() {
	// Loop forever and pull messages from channel
	for msg := range c.Send {
		err := c.Conn.WriteJSON(msg)
		if err != nil {
			log.Printf("error: %v", err)
			break
		}
	}
}

func (c *Client) flagPump() {
	for {
		if c.Closed {
			break
		}
		if c.SubbedToAdmin {

			flag_msg := fmt.Sprintf("Hey admin, here is the flag you wanted: %s", FLAG)
			CT, kCT := encryptMessage(c.AdminKey, flag_msg)

			msg := Message{
				MessageType: "chat",
				Data: &Chat{
					To:           AdminID,
					From:         FlagbotID,
					FromName:     "Flagbot",
					EncryptedKey: kCT,
					Message:      CT,
					Encrypted:    true,
				},
			}

			c.Send <- msg

		}
		msg := Message{MessageType: "ping"}
		c.Send <- msg
		time.Sleep(10 * time.Second)
	}
}

func encryptMessage(rsaKey *rsa.PublicKey, stringMsg string) (string, string) {

	msg := []byte(stringMsg)

	// Generate random AES key
	key := make([]byte, 16)
	rand.Read(key)

	// Encrypt message with AES
	block, _ := aes.NewCipher(key)

	cipherText := make([]byte, aes.BlockSize+len(msg))
	iv := cipherText[:aes.BlockSize]
	io.ReadFull(rand.Reader, iv)

	stream := cipher.NewCFBEncrypter(block, iv)
	stream.XORKeyStream(cipherText[aes.BlockSize:], msg)

	// Encrypt key with RSA public key
	hash := sha512.New()
	keyCiphertext, _ := rsa.EncryptOAEP(hash, rand.Reader, rsaKey, key, nil)

	// Return encrypted message and key in base64
	return base64.StdEncoding.EncodeToString(cipherText), base64.StdEncoding.EncodeToString(keyCiphertext)
}

func handleMessages() {
	for {
		select {
		case client := <-register:
			clients[client.ID] = client
		case client := <-unregister:
			if _, ok := clients[client.ID]; ok {
				delete(clients, client.ID)
				close(client.Send)
			}
		}
	}
}

func GenerateKeyPair(bits int) (*rsa.PrivateKey, *rsa.PublicKey) {
	privkey, _ := rsa.GenerateKey(rand.Reader, bits)
	return privkey, &privkey.PublicKey
}
