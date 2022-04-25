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
            File: {{ frame.filename }}
            <b-button
              class="float-right"
              variant="dark"
              v-b-modal.modal-classify
            >
              Classify
            </b-button>
          </b-card-text>
        </b-card>
      </b-card-group>
      <b-modal id="modal-classify" title="Classify Image" @ok="classify()">

      </b-modal>
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
    };
  },
  computed: {
    id() {
      return this.$route.params.id;
    },
  },
  created() {
    this.$axios
      .$get("/api/frames/iteration/" + this.id)
      .then((responseFrames) => {
        responseFrames.forEach((frame) => {
          this.$axios
            .$get("/api/frames/download/" + frame.id)
            .then((imageBase64) => {
              this.frames.push({
                filename: frame.filename,
                base64: "data:image/jpg;base64," + imageBase64
              });
            });
        });
      });
  },
};
</script>

<style>
</style>
