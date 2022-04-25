<template>
  <div>
    <navbar />
    <h1>Iterations</h1>
    <b-container>
    <div class="mt-5" v-if="this.tableLength != 0">
      <b-table
        small
        id="table"
        :items="this.iterations"
        :fields="fields"
        striped
        responsive="sm"
      >
        <template v-slot:cell(Frames)="row">
          <b-button variant="dark" :to="`/iterations/${row.item.id}`">
            View
          </b-button>
        </template>
      </b-table>
    </div>
    <div v-else>No iterations</div>
  </b-container>
  </div>
</template>

<script>
import navbar from "~/components/utils/NavBar.vue";
export default {
  data() {
    return {
      fields: [
        {
          key: "emotion",
          label: "Emotion",
          sortable: true,
          sortDirection: "desc",
        },
        //data
        //
        "Frames",
      ],
      iterations: [],
    };
  },
  components: {
    navbar,
  },
  methods: {
    getIterations() {
      this.$axios
        .$get("/api/iterations")
        .then((iterations) => {
          this.iterations = iterations;
        })
        .catch(() => {
          this.$toast.info("No iterations found").goAway(3000);
        });
    },
  },
  created() {
    this.getIterations();
  },
  computed: {
    currentUser() {
      return this.$auth.user;
    },
    tableLength: function () {
      return this.iterations.length;
    },
  },
};
</script>

<style>
</style>
