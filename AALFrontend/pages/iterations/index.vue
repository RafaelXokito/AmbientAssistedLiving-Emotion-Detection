<template>
  <div>
    <navbar />
    <h1>Iterations</h1>
    <b-container>
    <div class="mt-5" v-if="tableLength != 0">
      <b-table
        small
        id="iterationsTable"
        :items="iterations"
        :fields="fields"
        :current-page="currentPage"
        :per-page="perPage"
        striped
        responsive="sm"
      >
        <template #cell(emotion)="data">
          {{
            data.item.emotion.charAt(0).toUpperCase() + data.item.emotion.slice(1)
          }}
        </template>
        <template v-slot:cell(Frames)="row">
          <b-button variant="dark" :to="`/iterations/${row.item.id}`">
            View
          </b-button>
        </template>
        <template #cell(created_at)="data">
          {{
            data.item.created_at != null
              ? new Date(data.item.created_at).toLocaleString('pt-PT')
              : "Not Shown"
          }}
        </template>
      </b-table>
      <b-pagination
        v-model="currentPage"
        :total-rows="tableLength"
        :per-page="perPage"
        aria-controls="my-table"
        align="center"
      ></b-pagination>
    </div>
    <div v-else>No iterations</div>
  </b-container>
  </div>
</template>

<script>
import navbar from "~/components/utils/NavBar.vue";
export default {
  middleware: ('auth'),
  data() {
    return {
      fields: [
        {
          key: "emotion",
          label: "Emotion",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "created_at",
          label: "Data",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "classifiedFrames",
          label: "Classified Frames",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "totalFrames",
          label: "Total Frames",
          sortable: true,
          sortDirection: "desc",
        }
        ,
        "Frames",
      ],
      iterations: [],
      perPage: 10,
      currentPage: 1
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
          console.log(iterations)
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
    tableLength() {
      return this.iterations.length;
    },
  },
};
</script>

<style>
</style>
