# Chatter (Hard)

A CTFd compatible docker image for a web challenge. Scenario:

ChatterBawks is the coolest new end-to-end encrypted chat app. But is it really totally safe to use?

## Setup

Run the included build-docker.sh script to build and deploy the container in docker.

## Solution

This challenge is an end-to-end encrypted chat app with a Vue.JS frontend and a Golang backend server. On load, the browser generates a new RSA keypair and registers the user with the server via websocket and transmits their public key. The admin user then sends them a welcome message which is encrypted using AES-128-CFB and then sent along with the AES key which is encrypted using the user’s public RSA key. On the receiving end, the browser will decrypt the message key using the RSA private key, and then decrypt the message itself and display it.

By examining the flow of messages in the websocket connection, they may start poking and prodding and trying different things but the key is to take the UUID for the admin user and send a command to subscribe to messages for that UUID instead of their own. Then they start seeing a new chat pop up from FlagBot every few seconds but they are unable to decrypt the messages.

The next step is to send an “update public key” command via the websocket but insert the UUID of the admin user and their own public key. This will mean that future messages to the admin user will be encrypted using the PLAYER’s public key instead of the real admin.

After waiting 10 or so seconds, the player sees a message from FlagBot which is decrypted correctly and displayed in their chat window with the flag.
