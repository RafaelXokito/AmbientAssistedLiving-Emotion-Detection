<template>
  <div>
    <back-button></back-button>
    <div class="mt-5 ml-5 text-center">
      <h1 class="text-red-400">Conteúdos da interação nº {{ id }}</h1>
      <h4>Grupo de emoções - {{ firstCapitalLetter(emotion.name) }}</h4>
    </div>
    <v-container>
      <v-row no-gutters>
        <v-col
          v-for="(content, index) in contents"
          :key="content.id"
          cols="12"
          lg="2"
          md="3"
          sm="4"
          class="px-4 pa-4"
        >
          <v-card
            class="mx-auto"
            max-width="344"
          >
            <v-img v-if="content.base64" :src="content.base64"></v-img>
            <v-card-text>
              <form @submit.prevent="classify(content.id, content.base64, index)">
                <div class="text-center">
                  <p v-if="content.text">Texto: "{{content.text}}"</p>
                  <v-select
                    v-model="emotionsClassified[index]"
                    :items="humanLabelEmotions"
                    required
                  >
                  </v-select>
                  <v-btn type="submit" color="primary" class="mt-lg-2">
                    Classificar
                  </v-btn>
                </div>
              </form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>

import BackButton from "~/components/utils/BackButton"

export default {
  components: {BackButton},
  middleware: ("auth", "client"),
  data() {
    return {
      contents: [],
      humanLabelEmotions: [],
      emotionsClassified: [],
      socket: null,
      emotion: "",
      reveal: [],
    }
  },
  computed: {
    id() {
      return this.$route.params.id
    },
  },
  created() {
    this.$axios.$get("/api/iterations/" + this.id).then(({data}) => {
      this.emotion = data.emotion
      this.$axios
        .$get("/api/emotions/groups/" + this.emotion.name)
        .then(({data}) => {
          this.humanLabelEmotions.push({ value: null, text: '-', disabled: true, })
          data.forEach(e => {
            if (e.name !== e.category)
              this.humanLabelEmotions.push({ value: e.name, text: this.firstCapitalLetter(e.name) })
          })

          this.$axios
            .$get("/api/emotions/groups/" + 'invalid')
            .then(({data}) => {
              data.forEach(e => {
                this.humanLabelEmotions.push({ value: e.name, text: this.firstCapitalLetter(e.name) })
              })
            })
        })
        .catch(error => {
          // We have a personalized emotion, so we need to find its group first
          if(error.response.status === 404){
            this.$axios.$get("/api/emotions/" + this.emotion).then(emotion => {
              this.$axios
                .$get("/api/emotions/groups/" + emotion.group)
                .then(emotions => {
                  this.humanLabelEmotions.push({ value: null, text: 'None',disabled: true, })
                  emotions.forEach(e => {
                    if (e.name !== e.category)
                      this.humanLabelEmotions.push({ value: e.name, text: this.firstCapitalLetter(e.name) })
                  })
                })
            })
          }else{
            this.$toast
              .info(error.response.data)
              .goAway(3000)
          }
        })
    })

    this.$axios
      .$get("/api/iterations/" + this.id)
      .then(({data}) => {
        data.contents.forEach(content => {
          this.emotionsClassified.push(content.emotion_name !== undefined ? content.emotion_name : null)
          if(content.childable_type == "App\Models\Frame"){
            this.$axios
            .$get("/api/frames/download/" + content.childable_id)
            .then(imageBase64 => {
              this.contents.push({
                id: content.childable_id,
                filename: content.filename,
                base64: imageBase64
              })
              this.reveal.push(false)
            })
          }else{
            this.$axios
            .$get("/api/speeches/" + content.childable_id)
            .then(data => {
              this.contents.push({
                id: content.childable_id,
                text: data.data.text
              })
              this.reveal.push(false)
            })
          }
        })
      })
  },
  mounted() {
    this.socket = this.$nuxtSocket({ persist: 'mySocket'})
  },
  methods: {
    firstCapitalLetter(str=""){
      return str.toString().charAt(0).toUpperCase() + str.toString().slice(1)
    },
    classify(id, base64, index) {
      if(base64 != undefined){
        this.$axios
        .$patch("/api/frames/" + id + "/classify", {
          name: this.emotionsClassified[index],
        })
        .then(() => {
          this.$toast
            .success("Frame nº " + id + " classified successfully")
            .goAway(3000)

          // Connection opened
          // console.log(this.socket)
          const jsonData =
            '{ "userId": ' + this.$auth.user.id +
            ',"emotion" : "' +
            this.emotionsClassified[index] +
            '", "image": "' +
            base64 +
            '"}'

          this.socket.emit('newFrameMessage',jsonData)
      })
      }else{
        console.log(id)
        this.$axios
        .$patch("/api/speeches/" + id + "/classify", {
          name: this.emotionsClassified[index],
        })
        .then(() => {
          this.$toast
            .success("Speech nº " + id + " classified successfully")
            .goAway(3000)
        })
      }
    },
  }
}
</script>

<style>
</style>
