<template>
  <div>
    <navbar />
    <h1 class="mt-5">Frames - Iteration nº {{ id }}</h1>
    <div class="d-flex flex-row flex-wrap">
      <b-card-group
        class="mt-5 col-md-4"
        v-for="frame in frames"
        :key="frame.id"
      >
        <b-card
          :img-src="frame.base64"
          img-alt="Image"
          img-top
          tag="article"
          style="max-width: 20rem"
          class="mb-2 mx-auto"
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
  components: {
    navbar,
  },
  data() {
    return {
      frames: [],
      emotionGroup: null,
      humanLabelEmotions: [],
      emotionsClassified: [],
    };
  },
  computed: {
    id() {
      return this.$route.params.id;
    },
  },
  created() {
    this.$axios.$get("/api/iterations/" + this.id).then((iteration) => {
      this.emotionGroup = iteration.emotion;
      this.$axios
        .$get("/api/emotions/groups/" + this.emotionGroup)
        .then((emotions) => {
          this.humanLabelEmotions = emotions;
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
          const socket = new WebSocket(
            "ws://localhost:8080/AALBackend/framesocket/" + this.$auth.user.id
          );
          let jsonData = '{ "emotion" : "'+this.emotionsClassified[id - 1]+'", "image": "'+base64+'"}';
          socket.addEventListener("open", function (event) {
            socket.send(jsonData);
          });
        });
    },
  },
};
</script>

<style>
</style>
