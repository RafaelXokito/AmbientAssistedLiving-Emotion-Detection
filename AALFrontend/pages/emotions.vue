<template>
  <div>
    <div align="right">
      <b-button v-b-modal.modalCreate class="m-5" variant="outline-info">+ Emotion</b-button>
    </div>
    <b-container>
      <div v-if="tableLength != 0" class="mt-5">
        <b-table
          id="emotionsTable"
          small
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
          label="Nome:"
          label-for="input-name"
          label-class="font-weight-bold"
        >
          <b-form-input
            id="input-name"
            v-model="form.name"
            aria-describedby="input-name-feedback"
            trim
          ></b-form-input>
        </b-form-group>
        <b-form-group
          id="input-group-display-name"
          label="Descrição do nome:"
          label-for="input-display-name"
          label-class="font-weight-bold"
        >
          <b-form-input
            id="input-display-name"
            v-model="form.display_name"
            aria-describedby="input-name-feedback"
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
          <b-button type="submit" variant="primary">Criar</b-button>
          <b-button type="reset" variant="danger">Reiniciar</b-button>
        </div>
      </b-form>
    </b-modal>

  </div>
</template>

<script>
export default {
  middleware: ('auth'),
  data() {
    return {
      form: {
        group: null,
        name: null,
        display_name: null
      },
      fields: [
        {
          key: "name",
          label: "Nome",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "group",
          label: "Grupo",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "display_name",
          label: "Descrição do nome",
          sortable: true,
          sortDirection: "desc",
        }
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
      return this.emotions.length
    },
  },
  created() {
    this.getEmotions()
    this.getGroups()
  },
  methods: {
    onSubmit() {
      this.$axios
        .$post("/api/emotions", this.form)
        .then(emotion => {
          this.$toast.success('A Emoção '+this.form.name+' foi criada com sucesso').goAway(3000)
          this.emotions.push(emotion)
        })
        .catch(() => {
          this.$toast.error("Erro ao registar a emoção no sistema").goAway(3000)
        })
    },
    resetCreate() {
      this.emotions = null
    },
    getEmotions() {
      this.$axios
        .$get("/api/emotions")
        .then(emotions => {
          this.emotions = emotions
        })
        .catch(() => {
          this.$toast.info("Não existem emoções").goAway(3000)
        })
    },
    getGroups(){
      this.groups = [{ value: null, text: 'Por favor selecione uma opção' },
                    { value: 'positive', text: 'Positiva' },
                    { value: 'neutral', text: 'Neutra' },
                    { value: 'negative', text: 'Negativa' }]
    }
  },
}
</script>

<style>

</style>
