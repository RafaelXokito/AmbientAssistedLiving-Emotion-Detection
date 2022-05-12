<template>
  <div>
    <navbar />
    <div class="mt-5 ml-5">
      <h1>Frames of Iteration nº {{ id }}</h1>
      <h4>Emotion - {{ emotion }}</h4>
    </div>
    <div class="d-flex flex-row flex-wrap">
      <b-card-group
        class="mt-5 col-md-4"
        v-for="frame in frames"
        :key="frame.id"
      >
        <b-card
          :img-src="frame.base64"
          img-alt="Image"
          tag="article"
          style="max-width: 20rem"
          class="mb-2 mx-auto"
          img-height="96px"
          img-width="96px"
          img-left
        >
          <b-card-text>
            <form v-on:submit.prevent="classify(frame.id, frame.base64)">
              <div class="input-group">
                <b-select
                  v-model="emotionsClassified[frame.id - 1]"
                  :options="humanLabelEmotions"
                  required
                  value-field="name"
                  text-field="name"
                >
                </b-select>
                <b-button class="float-right" type="submit" variant="dark">
                  Classify
                </b-button>
              </div>
            </form>
          </b-card-text>
        </b-card>
      </b-card-group>
    </div>
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
      emotionGroup: null,
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
          this.humanLabelEmotions = emotions;
        })
        .catch((error) => {
          // We have a personalized emotion, so we need to find its group first
          if(error.response.status == 404){
            this.$axios.$get("/api/emotions/" + this.emotion).then((emotion) => {
            this.$axios
              .$get("/api/emotions/groups/" + emotion.group)
              .then((emotions) => {
                this.humanLabelEmotions = emotions;
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
          this.emotionsClassified.push(frame.emotion.name);
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
    classify(id, base64) {
      this.$axios
        .$patch("/api/frames/" + id + "/classify", {
          name: this.emotionsClassified[id - 1],
        })
        .then(() => {
          this.$toast
            .success("Frame nº " + id + " classified successfully")
            .goAway(3000);
          // Connection opened
          //console.log(this.socket)
          let jsonData =
            '{ "emotion" : "' +
            this.emotionsClassified[id - 1] +
            '", "image": "' +
            base64 +
            '"}';
          if (this.socket.readyState == 1) this.socket.send(jsonData);
        });
    },
  },
};
</script>

<style>
</style>
