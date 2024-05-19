import { defineStore } from "pinia";
import { socket } from "@/socket";
import { emitter } from "@/emitter";

import forge from 'node-forge';

export const useChatStore = defineStore("chat", {
  state: () => ({
    isConnected: false,
    clientID: null,
    pubKey: null,
    pubKeyPEM: null,
    privKey: null,
    privKeyPEM: null,
    chats: {},
    contacts: {},
    activeChat: null,
    errorBar: false,
    errorMessage: "",
  }),

  actions: {
    initChat() {

    //Load RSA keys
    this.pubKeyPEM = window.localStorage.getItem("pubKey")
    this.privKeyPEM = window.localStorage.getItem("privKey")

    // Generate keypair if needed
    if(!this.pubKeyPEM || !this.privKeyPEM){
        this.genRSAKeypair()
    } else {
        try {
            this.pubKey = forge.pki.publicKeyFromPem(this.pubKeyPEM)
            this.privKey = forge.pki.privateKeyFromPem(this.privKeyPEM)
        } catch (e) {
            //An error happened loading keypair, start over
            this.errorMessage = "Error loading saved RSA keypair, generating a new one..."
            this.errorBar = true
            
            this.genRSAKeypair()
        }
    }
    
    // Socket is opened
    socket.addEventListener("open", () => {
        this.isConnected = true
    });

    // Socket is closed
    socket.addEventListener("close", () => {
        this.isConnected = false
    });

    // Receive messages
    socket.addEventListener("message", (event) => {

        const msg = JSON.parse(event.data)

        switch(msg.type){
            case 'hello':

                this.clientID = msg.data.client_id

                // Subscribe to messages for this client ID
                socket.send(JSON.stringify({type:"subscribe",subscribe:{client_id:this.clientID}}))

                // Send public key to server and other clients
                socket.send(JSON.stringify({type:"update_pubkey",update_pubkey:{pem:this.pubKeyPEM}}))

                break
            case 'system':
                this.errorMessage = msg.data.message
                this.errorBar = true
                break
            case 'ping':
                socket.send(JSON.stringify({type:"pong"}))
                break
            case 'chat':

                if(!this.chats[msg.data.from])
                {
                    this.chats[msg.data.from] = {from_name:msg.data.from_name,messages:[]}
                }

                if(!msg.data.encrypted){

                    this.chats[msg.data.from].messages.push({message:msg.data.message,encrypted:false,dir:"in"})

                } else {

                    try {
                        // Decrypt AES Key for Message
                        var AES_Key = this.privKey.decrypt(atob(msg.data.key), 'RSA-OAEP', {
                            md: forge.md.sha512.create(),
                            mgf1: {
                            md: forge.md.sha512.create()
                            }
                        });

                        var msg_bytes = atob(msg.data.message)
                        var iv = msg_bytes.slice(0,16)
                        var ct = msg_bytes.slice(16)

                        // Decrypt chat message with key
                        var decipher = forge.cipher.createDecipher('AES-CFB', AES_Key);
                        decipher.start({iv: iv});
                        decipher.update(forge.util.createBuffer(ct));
                        var result = decipher.finish()
                        if(result){
                            this.chats[msg.data.from].messages.push({message:decipher.output.data,encrypted:true,dir:"in"})
                        } else {
                            console.log("AES Decyption Error.")
                        }
                    } catch(e){
                        this.errorMessage = "You received an encrypted message which failed to decrypt properly."
                        this.errorBar = true
                    }

                }

                //Switch to this chat if none is currently active
                if(this.activeChat==null){
                    this.activeChat=msg.data.from
                }

                emitter.emit("chat_received",{});

                break
            case 'update_pubkey': // Another user has sent you their public key
                try{
                    var client_pubkey = forge.pki.publicKeyFromPem(atob(msg.data.pem))
                    this.contacts[msg.data.client_id] = client_pubkey
                } catch(e){
                    this.errorMessage = "Invalid RSA public key received."
                    this.errorBar = true
                }
                break
        }

        console.log(event.data);
    });

    },
    genRSAKeypair(){

        // Generate strong RSA keypair, may take a sec
        var keypair = forge.pki.rsa.generateKeyPair({bits: 2048, e: 0x10001});

        this.pubKey = keypair.publicKey
        this.privKey = keypair.privateKey

        // Save credentials in PEM format to localStorage for later
        this.pubKeyPEM = forge.pki.publicKeyToPem(this.pubKey)
        this.privKeyPEM = forge.pki.privateKeyToPem(this.privKey)

        window.localStorage.setItem("pubKey",this.pubKeyPEM)
        window.localStorage.setItem("privKey",this.privKeyPEM)

    },
    sendChat(msg,client_id){

        var recpPubkey = this.contacts[client_id]

        var outmsg = {from:this.clientID,
                    from_name:"C1",
                    to:client_id,
                    encrypted:false,
                    key:"",
                    message:msg}

        if (!recpPubkey){
            console.log("No public key set for user. Sending message without encryption.");
        } else {

            // AES encryption
            var key = forge.random.getBytesSync(16);
            var iv = forge.random.getBytesSync(16);

            var cipher = forge.cipher.createCipher('AES-CFB', key);
            cipher.start({iv: iv});
            cipher.update(forge.util.createBuffer(msg));
            cipher.finish();
            outmsg.message = btoa(cipher.output.getBytes());

            // RSA encryption
            outmsg.key = btoa(recpPubkey.encrypt(key, 'RSA-OAEP', {
                md: forge.md.sha512.create(),
            }));

            outmsg.encrypted = true
        } 
        
        socket.send(JSON.stringify({type:"chat",chat:outmsg}))
        this.chats[client_id].messages.push({message:msg,dir:"out"})
        emitter.emit("chat_received",{});
    },
  },
});