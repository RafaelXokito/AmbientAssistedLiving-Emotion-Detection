<template>
  <div>
    <back-button></back-button>
    <div class="mt-5 ml-5 text-center">
      <h2 class="mb-5">Conteúdos da iteração nº {{ id }}</h2>
      <h4>Grupo de emoções - {{ firstCapitalLetter(emotion.display_name) }}</h4>
    </div>
    <v-container>
      <v-row no-gutters>
        <v-col v-for="(content, index) in contents" :key="content.id" cols="4" lg="4" md="3" sm="4" class="px-4 pa-4">
          <v-card class="mx-auto">
            <v-card-title>{{ content.title }}</v-card-title>
            <div v-if="content.base64">
              <v-img :src="content.base64"></v-img>
              <v-card-text>
                <p>Emoção: {{ content.emotion }}</p>
                <p>{{ content.createdate }}</p>
                <form @submit.prevent="classifyImage(content.id, content.base64, index)">
                  <v-select v-model="emotionsClassified[index]" :items="humanLabelEmotions" required>
                  </v-select>
                  <div class="text-center">
                    <v-btn type="submit" color="primary" class="mt-lg-2">
                      Classificar
                    </v-btn>
                  </div>
                </form>
              </v-card-text>
            </div>
            <div v-if="content.questionnaire">
              <v-card-text>
                <p>Pergunta nº {{ content.question }}</p>
                <p>Resposta: "{{ content.response }}"</p>
                <p>Emoção: {{ content.emotion }}</p>
                <p>{{ content.createdate }}</p>
                <v-select v-model="emotionsClassified[index]" :items="humanLabelEmotions" required>
                </v-select>
                <div class="text-center">
                  <v-btn type="submit" color="primary" class="mt-lg-2">
                    Classificar
                  </v-btn>
                </div>
              </v-card-text>
            </div>
            <div v-if="content.text">
              <v-card-text>
                <p>Texto: {{ content.text }}</p>
                <p>Emoção: {{ content.emotion }}</p>
                <p>{{ content.createdate }}</p>
                <v-select v-model="emotionsClassified[index]" :items="humanLabelEmotions" required>
                </v-select>
                <div class="text-center">
                  <v-btn type="submit" color="primary" class="mt-lg-2">
                    Classificar
                  </v-btn>
                </div>
              </v-card-text>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>

import BackButton from "~/components/utils/BackButton"

export default {
  components: { BackButton },
  middleware: ("auth", "client"),
  data() {
    return {
      contents: [],
      humanLabelEmotions: [],
      emotionsClassified: [],
      socket: null,
      emotion: "",
      reveal: []
    }
  },
  computed: {
    id() {
      return this.$route.params.id
    },
  },
  created() {
    this.$axios
      .$get("/api/emotions")
      .then(({ data }) => {
        this.humanLabelEmotions.push({ value: null, text: '-', disabled: true, })
        data.forEach(e => {
          if (e.name !== e.category)
            this.humanLabelEmotions.push({ value: e.name, text: this.firstCapitalLetter(e.display_name) })
        })
      });
    this.$axios.$get("/api/iterations/" + this.id + "?details=true").then(response => {
      this.emotion = response.data.emotion
      response.data.contents.forEach(content => {
        var data = content.content
        this.emotionsClassified.push(data.emotion_name !== undefined ? content.emotion_name : null)
        var result = {
          id: data.id,
          emotion: content.emotion.display_name,
          createdate: new Date(content.createdate * 1000).toLocaleString("pt-PT")
        }
        switch (content.type) {
          case "ResponseQuestionnaire":
            result['title'] = "Resposta do questionário nº " + data.questionnaire_id;
            result['response'] = data.response;
            result['questionnaire'] = data.questionnaire_id;
            result['question'] = data.question;
            break;
          case "Frame":
            this.$axios
              .$get("/api/frames/download/" + data.id)
              .then(imageBase64 => {
                result['title'] = "Imagem";
                result['filename'] = data.filename;
                result['base64'] = imageBase64;
              })
            break;
          case "Speech":
            result['title'] = "Diálogo";
            result['text'] = data.text;
            break;
        } 
        this.contents.push(result);
        this.reveal.push(false);
      })
    })
  },
  mounted() {
    this.socket = this.$nuxtSocket({ persist: 'mySocket' })
  },
  methods: {
    firstCapitalLetter(str = "") {
      return str.toString().charAt(0).toUpperCase() + str.toString().slice(1)
    },
    classifyImage(id, base64, index) {
      if (base64 != undefined) {
        this.$axios
          .$patch("/api/frames/" + id + "/classify", {
            name: this.emotionsClassified[index],
          })
          .then(() => {
            this.$toast
              .success("Conteudo do tipo 'imagem' com o nº " + id + " classificado com sucesso")
              .goAway(3000)
            const jsonData =
              '{ "userId": ' + this.$auth.user.id +
              ',"emotion" : "' +
              this.emotionsClassified[index] +
              '", "image": "' +
              base64 +
              '"}'

            this.socket.emit('newFrameMessage', jsonData)
          })
      } else {
        this.$axios
          .$patch("/api/speeches/" + id + "/classify", {
            name: this.emotionsClassified[index],
          })
          .then(() => {
            this.$toast
              .success("Conteudo do tipo 'voz' com o nº " + id + " classificado com sucesso")
              .goAway(3000)
          })
      }
    },
  }
}
</script>

<style></style>
