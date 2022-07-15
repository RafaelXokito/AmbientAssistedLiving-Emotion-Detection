<template>
  <div>
    <back-button></back-button>
    <div class="mt-5 ml-5 text-center">
      <h1 class="text-red-400">Frames of Iteration nº {{ id }}</h1>
      <h4>Emotion Group - {{ firstCapitalLetter(emotion.name) }}</h4>
    </div>
    <v-container>
      <v-row no-gutters>
        <v-col
          v-for="(frame, index) in frames"
          :key="frame.id"
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
            <v-img
              :src="frame.base64"></v-img>
            <v-card-text>
              <form @submit.prevent="classify(frame.id, frame.base64, index)">
                <div class="text-center">
                  <v-select
                    v-model="emotionsClassified[index]"
                    :items="humanLabelEmotions"
                    required
                  >
                  </v-select>
                  <v-btn type="submit" color="primary" class="mt-lg-2">
                    Classify
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
      frames: [],
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
          this.humanLabelEmotions.push({ value: null, text: 'None', disabled: true, })
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
      .$get("/api/frames/iteration/" + this.id)
      .then(({data}) => {
        data.forEach(frame => {
          this.emotionsClassified.push(frame.emotion.name !== undefined ? frame.emotion.name : null)
          this.$axios
            .$get("/api/frames/download/" + frame.id)
            .then(imageBase64 => {
              this.frames.push({
                id: frame.id,
                filename: frame.filename,
                base64: imageBase64,
              })
              this.reveal.push(false)
            })
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
    },
  }
}
</script>

<style>
</style>
