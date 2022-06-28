<template>
  <div>
    <navbar/>
    <div align="right">
      <b-button class="m-5" variant="outline-info" v-b-modal.modalCreate>+ Emotion</b-button>
    </div>
    <b-container>
      <div class="mt-5" v-if="tableLength != 0">
        <b-table
          small
          id="emotionsTable"
          :items="emotions"
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
      <div v-else>No emotions</div>
    </b-container>
    <b-modal id="modalCreate" size="lg" title="Create Emotion" hide-footer>
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
          id="input-group-group"
          label="Group:"
          label-for="input-group"
          label-class="font-weight-bold"
        >
          <b-select
            v-model="form.group"
            :options="groups"
            required
          >
          </b-select>
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
        group: null,
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
          key: "group",
          label: "Group",
          sortable: true,
          sortDirection: "desc",
        },
      ],
      emotions: [],
      groups: [],
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
    currentUser(){
      return this.$auth.user
    },
    tableLength() {
      return this.emotions.length;
    },
  },
  methods: {
    onSubmit() {
      this.$axios
        .$post("/api/emotions", this.form)
        .then((emotion) => {
          this.$toast.success('Emotion '+this.form.name+' created').goAway(3000);
          this.emotions.push(emotion);
        })
        .catch(() => {
          this.$toast.error("Error creating emotion").goAway(3000);
        });
    },
    resetCreate() {
      this.emotions = null;
    },
    getEmotions() {
      this.$axios
        .$get("/api/emotions")
        .then((emotions) => {
          this.emotions = emotions;
        })
        .catch(() => {
          this.$toast.info("No emotions found").goAway(3000);
        });
    },
    getGroups(){
      this.groups = [{ value: null, text: 'Please select an option' },
                    { value: 'positive', text: 'Positive' },
                    { value: 'neutral', text: 'Neutral' },
                    { value: 'negative', text: 'Negative' }]
    }
  },
  created() {
    this.getEmotions();
    this.getGroups();
  },
}
</script>

<style>

</style>
