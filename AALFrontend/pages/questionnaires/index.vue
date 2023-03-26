<template>
  <div>
    <v-card>
          <v-card-title>
            Questionnaires
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
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
          <template v-slot:item.created_at="{item}">
            {{
              item.created_at != null
                ? new Date(item.created_at * 1000).toLocaleString("pt-PT")
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
          value: "points",
          text: "Points",
          sortDirection: "desc",
        },
        {
          value: "pointsLabel",
          text: "Points Label",
          sortDirection: "desc",
        },
        {
          value: "created_at",
          text: "Data",
          sortDirection: "desc",
        },
        {
          value: "responses",
          text: "Responses",
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
          var label = ""
          if(questionnaire.points >= 0 && questionnaire.points<=5){
            label = "No depression"
          }
          else if(questionnaire.points >= 6 && questionnaire.points<=10){
            label = "Slight depression"
          }
          else{
            label = "Sereve depression"
          }
          this.questionnaires.push({
            id: questionnaire.id,
            points: questionnaire.points,
            pointsLabel: label,
            created_at : questionnaire.created_at,
            responses: questionnaire.responses
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
