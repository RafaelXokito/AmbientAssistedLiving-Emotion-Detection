<template>
  <div>
    <navbar />
    <div class="mt-5 ml-5 text-center font-mono">
      <h1 class="text-red-400">Frames of Iteration nº {{ id }}</h1>
      <h4>Emotion Group - {{ firstCapitalLetter(emotion) }}</h4>
    </div>
    <b-container class="p-lg-4">
      <div class="d-flex flex-row flex-wrap rounded-md backdrop-blur-md bg-black/5 pb-lg-5">
        <b-card-group
        class="pt-5 col-md-2"
        v-for="(frame, index) in frames"
        :key="frame.id"
      >
        <b-card
          :img-src="frame.base64"
          img-alt="Image"
          tag="article"
          style="max-width: 150px"
          class="mb-2 mx-auto"
          img-top
        >
          <b-card-text>
            <form v-on:submit.prevent="classify(frame.id, frame.base64, index)">
              <div class="text-center">
                <b-select
                  v-model="emotionsClassified[index]"
                  :options="humanLabelEmotions"
                  required
                >
                </b-select>

                <b-button type="submit" variant="outline-danger" class="mt-lg-2">
                  Classify
                </b-button>
              </div>
            </form>
          </b-card-text>
        </b-card>
      </b-card-group>
      </div>
    </b-container>
  </div>
</template>

<script>
import navbar from "~/components/utils/NavBar.vue";
export default {
  middleware: "auth",
  components: {
    navbar,
  },
  data() {
    return {
      frames: [],
      humanLabelEmotions: [],
      emotionsClassified: [],
      socket: null,
      emotion: "",
    };
  },
  computed: {
    id() {
      return this.$route.params.id;
    },
  },
  created() {
    this.$axios.$get("/api/iterations/" + this.id).then((iteration) => {
      this.emotion = iteration.emotion;
      this.$axios
        .$get("/api/emotions/groups/" + this.emotion)
        .then((emotions) => {
          this.humanLabelEmotions.push({ value: null, text: 'None' })
          emotions.forEach(e => {
            this.humanLabelEmotions.push({ value: e.name, text: this.firstCapitalLetter(e.name) })
          })
        })
        .catch((error) => {
          // We have a personalized emotion, so we need to find its group first
          if(error.response.status == 404){
            this.$axios.$get("/api/emotions/" + this.emotion).then((emotion) => {
            this.$axios
              .$get("/api/emotions/groups/" + emotion.group)
              .then((emotions) => {
                this.humanLabelEmotions.push({ value: null, text: 'None' })
                emotions.forEach(e => {
                  this.humanLabelEmotions.push({ value: e.name, text: this.firstCapitalLetter(e.name) })
                })
              });
          });
          }else{
            this.$toast
            .info(error.response.data)
            .goAway(3000);
          }
        });
    });

    this.$axios
      .$get("/api/frames/iteration/" + this.id)
      .then((responseFrames) => {
        responseFrames.forEach((frame) => {
          this.emotionsClassified.push(frame.emotion.name === '' ? null : frame.emotion.name);
          this.$axios
            .$get("/api/frames/download/" + frame.id)
            .then((imageBase64) => {
              this.frames.push({
                id: frame.id,
                filename: frame.filename,
                base64: "data:image/jpg;base64," + imageBase64,
              });
            });
        });
      });
  },
  mounted() {
    this.socket = new WebSocket(
      process.env.FRAMES_WEBSOCKET_URL + this.$auth.user.id
    );
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
            .goAway(3000);
          // Connection opened
          //console.log(this.socket)
          let jsonData =
            '{ "emotion" : "' +
            this.emotionsClassified[index] +
            '", "image": "' +
            base64 +
            '"}';
          if (this.socket.readyState == 1) this.socket.send(jsonData);
        });
    },
  }
};
</script>

<style>
</style>
