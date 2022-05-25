<template>
  <div>
    <navbar/>
    <div align="right">
      <b-button class="m-5" variant="outline-info" v-b-modal.modalCreate>+ Administrator</b-button>
    </div>
    <b-container>
      <div class="mt-5" v-if="tableLength != 0">
        <b-table
          small
          id="administratorsTable"
          :items="administrators"
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
      <div v-else class="w-75 mx-auto alert alert-info">No more administrators registered in the system yet</div>
    </b-container>
    <b-modal id="modalCreate" size="lg" title="Create Administrator" hide-footer>
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
      ],
      administrators: [],
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
    currentUser(){
      return this.$auth.user
    },
    tableLength() {
      return this.administrators.length;
    },
  },
  methods: {
    onSubmit() {
      this.form.birthDate = new Date(this.form.birthDate)

      this.$axios
        .$post("/api/administrators", this.form)
        .then((administrator) => {
          this.$toast.success('Administrator '+this.form.name+' created').goAway(3000);
          this.administrators.push(administrator);
        })
        .catch(() => {
          this.$toast.error("Error creating administrator").goAway(3000);
        });
    },
    resetCreate() {
      this.administrators = null;
    },
    getAdministrators() {
      this.$axios
        .$get("/api/administrators")
        .then((administrators) => {
          this.administrators = administrators;
        })
        .catch(() => {
          this.$toast.info("No administrators found").goAway(3000);
        });
    }
  },
  created() {
    this.getAdministrators();
  },
}
</script>

<style>

</style>
