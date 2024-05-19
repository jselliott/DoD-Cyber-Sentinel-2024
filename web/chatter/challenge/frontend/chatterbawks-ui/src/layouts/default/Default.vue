<template>
  <v-app id="inspire">
    <v-app-bar flat>
      <v-container class="mx-auto d-flex align-center justify-center">
        <v-avatar
          class="me-4 "
          color="grey-darken-1"
          size="32"
          :image="b_icon"
        ></v-avatar>

        <v-spacer></v-spacer>
      </v-container>
    </v-app-bar>

    <v-main class="bg-orange-lighten-2">
      <v-container>
        <v-row>
          <v-col cols="2">
            <v-sheet rounded="lg">
              <div class="pa-5">
                <v-img :src="logo"/>
              </div>
              <v-list rounded="lg">
                <v-divider class="my-2"></v-divider>
                <v-list-item title="Status">
                  <v-chip :color="chat.isConnected ? 'green' : 'grey'" variant="flat" :text="chat.isConnected ? 'Connected' : 'Disconnected'" class="align-center justify-center"></v-chip>
                  <v-chip @click="dialog = true" :color="blue"><v-icon>mdi-help</v-icon></v-chip>
                </v-list-item>
                <v-divider class="my-2"></v-divider>
              </v-list>
            </v-sheet>
          </v-col>

          <v-col>
            <v-sheet
              min-height="70vh"
              rounded="lg"
              id="chatWindow"
            >
              <v-tabs
                v-model="chat.activeChat"
                bg-color="orange-lighten-5"
              >
                <v-tab v-for="c in Object.keys(chat.chats)" 
                :value="c"
                :key="c"><v-icon>mdi-chat</v-icon>{{ chat.chats[c].from_name }}</v-tab>
              </v-tabs>
            <v-window v-model="chat.activeChat" :id="c" height="100%" class="pa-5">
              <v-window-item 
              v-for="c in Object.keys(chat.chats)"
              :value="c"
              :key="c">
                <v-card
                v-for="msg in chat.chats[c].messages"
                :key="msg"
                :subtitle="Date()"
                :text="msg.message"
                variant="outlined"
                :append-icon="msg.dir == 'out' ? 'mdi-arrow-right-bold-circle' : 'mdi-arrow-left-bold-circle'"
                :class="msg.dir == 'out' ? ' my-5 ml-10' : 'my-5 mr-10'"></v-card>
              </v-window-item>
            </v-window>
            </v-sheet>
            <v-sheet rounded="lg" class="mt-5 pa-5">
              <v-form ref="chatForm">
                <v-text-field
                  clearable
                  label="Bawk Bawk"
                  prepend-icon="mdi-message"
                  append-inner-icon="mdi-arrow-right-bold-circle"
                  variant="outlined"
                  :disabled="chat.isConnected ? false : true"
                  v-model="chatInput"
                  @keydown.enter.prevent="chat.sendChat(chatInput,chat.activeChat)"
                ></v-text-field>
            </v-form>
            </v-sheet>
          </v-col>
        </v-row>
      </v-container>
      <v-dialog
      v-model="dialog"
      width="auto"
      >
      <v-card
        max-width="400"
        prepend-icon="mdi-help"
        text="ChatterBawks is a secure chat app that uses a multilayer approach to protecting your communications. Each message is encrypted with a random AES-128-CFB key, and then that key is encrypted using the 2048-bit RSA public key of the recipient, and both are sent. This means that your communications are end-to-end encrypted from browser to browser without ever being visible to others along the way, and only the intended recipient is able to decrypt both the AES key and the chat message you sent."
        title="How Does This Work?"
      >
        <template v-slot:actions>
          <v-btn
            class="ms-auto"
            text="Ok"
            @click="dialog = false"
          ></v-btn>
        </template>
      </v-card>
    </v-dialog>
    <v-snackbar
      v-model="chat.errorBar"
      timeout="2000"
      :text="chat.errorMessage"
    >
    </v-snackbar>
    </v-main>
  </v-app>
</template>

<script setup>

import { useChatStore } from '@/store/chat'
import { emitter } from "@/emitter";

const chat = useChatStore()

// Initialize connection
chat.initChat()

emitter.on("chat_received", (data) => {
  var container = document.querySelector(".v-window");
  container.scrollTop = container.scrollHeight;
});

</script>

<script>

  import b_logo from "@/assets/logo.png"
  import b_icon from "@/assets/icon.png"

  export default {
    data: () => ({
      links: [
        'ChatterBawks',
      ],
      logo: b_logo,
      icon: b_icon,
      dialog: false,
      chatInput: "",
    }),
    mounted(){
      
    },
    actions: {
      sendChat(){
        chat.sendChat(this.chatInput,chat.activeChat)
        this.chatInput = "";
      },
    }
  }
</script>