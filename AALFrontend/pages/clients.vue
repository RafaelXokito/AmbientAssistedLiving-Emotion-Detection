<template>
  <div>
    <navbar/>
    <b-container>
      <div class="mt-5" v-if="tableLength != 0">
        <b-table
          small
          id="clientsTable"
          :items="clients"
          :fields="fields"
          :current-page="currentPage"
          :per-page="perPage"
          striped
          responsive="sm"
        >
        </b-table>
        <b-pagination
          v-model="currentPage"
          :total-rows="tableLength"
          :per-page="perPage"
          aria-controls="my-table"
          align="center"
        ></b-pagination>
      </div>
      <div v-else>No clients</div>
    </b-container>
  </div>
</template>

<script>
import navbar from '~/components/utils/NavBar.vue'
export default {
  components: {
    navbar,
  },
  data() {
    return {
      fields: [
        {
          key: "name",
          label: "Name",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "email",
          label: "Email",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "age",
          label: "Age",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "contact",
          label: "Contact",
          sortable: true,
          sortDirection: "desc",
        },
      ],
      clients: [],
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
    currentUser(){
      return this.$auth.user
    },
    tableLength() {
      return this.clients.length;
    },
  },
  methods: {
    getClients() {
      this.$axios
        .$get("/api/clients")
        .then((clients) => {
          this.clients = clients;
        })
        .catch(() => {
          this.$toast.info("No clients found").goAway(3000);
        });
    }
  },
  created() {
    this.getClients();
  },
}
</script>

<style>

</style>
