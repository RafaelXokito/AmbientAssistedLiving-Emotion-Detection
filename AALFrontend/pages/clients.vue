<template>
  <div>
    <navbar/>
    <div align="right">
      <b-button class="m-5" variant="outline-info" v-b-modal.modalCreate>+ Client</b-button>
    </div>
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
    <b-modal id="modalCreate" size="lg" title="Create Client" hide-footer>
      <b-form @submit.prevent="onSubmit" @reset.prevent="resetCreate">
        <b-form-group
          id="input-group-name"
          label="Name:"
          label-for="input-name"
          label-class="font-weight-bold"
        >
          <b-form-input
            id="input-name"
            aria-describedby="input-name-feedback"
            v-model="form.name"
            trim
          ></b-form-input>
        </b-form-group>
        <b-form-group
          id="input-group-email"
          label="E-mail:"
          label-for="input-email"
          label-class="font-weight-bold"
        >
          <b-form-input
            id="input-email"
            aria-describedby="input-email-feedback"
            v-model="form.email"
            trim
          ></b-form-input>
        </b-form-group>
        <b-form-group
          id="input-group-password"
          label="Password:"
          label-for="input-password"
          label-class="font-weight-bold"
        >
          <b-form-input
            id="input-password"
            aria-describedby="input-password-feedback"
            v-model="form.password"
            type="password"
            name="password"
            autocomplete="on"
            trim
          ></b-form-input>
        </b-form-group>
        <b-form-group
          id="input-group-birthdate"
          label="Date of Birth:"
          label-for="input-birthdate"
          label-class="font-weight-bold"
        >
          <b-form-datepicker
            id="input-birthdate"
            v-model="form.birthDate"
            show-decade-nav
            hide-header
            aria-describedby="input-birthdate-feedback"
          />
        </b-form-group>
        <b-form-group
          id="input-group-phonenumber"
          label="Phone Number:"
          label-for="input-phonenumber"
          label-class="font-weight-bold"
        >
          <b-form-input
            id="input-phonenumber"
            aria-describedby="input-phonenumber-feedback"
            v-model="form.contact"
            trim
          ></b-form-input>
        </b-form-group>
        <div align="center">
          <b-button type="submit" variant="primary">Create</b-button>
          <b-button type="reset" variant="danger">Reset</b-button>
        </div>
      </b-form>
    </b-modal>

  </div>
</template>

<script>
import navbar from '~/components/utils/NavBar.vue'
export default {
  middleware: ('auth'),
  components: {
    navbar,
  },
  data() {
    return {
      form: {
        email: null,
        password: null,
        name: null,
        birthDate: null,
        contact: null
      },
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
    onSubmit() {
      this.form.birthDate = new Date(this.form.birthDate)
      this.$axios
        .$post("/api/clients", this.form)
        .then((client) => {
          this.$toast.success('Client '+this.form.name+' created').goAway(3000);
          this.clients.push(client);
        })
        .catch(() => {
          this.$toast.error("Error creating client").goAway(3000);
        });
    },
    resetCreate() {
      this.clients = null;
    },
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
