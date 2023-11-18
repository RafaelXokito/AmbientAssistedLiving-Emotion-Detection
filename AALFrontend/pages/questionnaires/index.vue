<template>
  <div>
    <v-card>
          <v-card-title>
            Questionários
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
               label="Pesquisar"
              single-line
              hide-details
            ></v-text-field>
          </v-card-title>
          <v-data-table
          id="questionnairesTable"
          :headers="fields"
          :items="questionnaires"
          :items-per-page="perPage"
          class="elevation-1"
          :loading="questionnaires.length === 0"
          :search="search"
        >
          <template v-slot:item.responses="{item}">
            <v-btn :to="`/questionnaires/${item.id}`" icon>
              <v-icon dense>
                mdi-expand-all
              </v-icon>
            </v-btn>
          </template>
          <template v-slot:item.updated_at="{item}">
            {{
              item.updated_at != null
                ? new Date(item.updated_at * 1000).toLocaleString("pt-PT")
                : "Not Shown"
            }}
          </template>

        </v-data-table>
    </v-card>
  </div>
</template>

<script>

export default {
  components: {
  },
  middleware: ("auth", "client"),
  data() {
    return {
      search: '',
      fields: [
        {
          value: "updated_at",
          text: "Data de conclusão",
          sortDirection: "desc",
        },
        {
          value: "questionnaireType",
          text: "Tipo de Questionário",
          sortDirection: "desc",
        },
        {
          value: "points",
          text: "Pontuação",
          sortDirection: "desc",
        },
        {
          value: "emotionLevel",
          text: "Nível de Emoção",
          sortDirection: "desc",
        },        
        {
          value: "responses",
          text: "Respostas",
          sortable: false,
        }
      ],
      questionnaires: [],
      socket: null,
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
    currentUser() {
      return this.$auth.user
    },
    tableLength() {
      return this.questionnaires.length
    },
  },
  watch: {
    config: {
      handler() {
        this.render()
      },
      deep: true,
    },
  },
  mounted() {
    this.socket = this.$nuxtSocket({ persist: 'mySocket'})
  },
  async created() {
    await this.getQuestionnaries()
  },
  methods:{
    async getQuestionnaries() {
      await this.$axios.get("/api/geriatricQuestionnaires").then(response => {
        response.data.data.forEach(questionnaire => {
          
          this.questionnaires.push({
            id: questionnaire.id,
            questionnaireType: questionnaire.questionnaire_type,
            points: questionnaire.points,
            updated_at : questionnaire.updated_at,
            emotionLevel: questionnaire.emotionLevel,
            responses: questionnaire.responses,
          })
        });

      })
      .catch(() => {
          this.$toast.info("No questionnaires found").goAway(3000)
        })
    },
  }
}
</script>

<style>
</style>
