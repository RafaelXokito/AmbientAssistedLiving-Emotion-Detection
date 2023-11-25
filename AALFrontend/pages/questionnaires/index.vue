<template>
  <div>
    <v-card>
      <v-card-title>
        <h3 v-if="!questionnaireType">
          Questionários
        </h3>
        <h3 v-else>
          {{ questionnaireTypeData.display_name }}
        </h3>
        <v-spacer></v-spacer>
        <v-select single-line v-model="questionnaireType" :items="questionnairesTypesSelect" />
      </v-card-title>
      <div v-if="questionnaireType">
        <v-card-text>
          <v-row>
            <v-col cols="3">
              <v-card height="100%">
                <v-card-title>
                  <v-row>
                    <v-col cols="10">
                      Pontuação mínima:
                    </v-col>
                    <v-col cols="2">{{ questionnaireTypeData.points_min }}</v-col>
                  </v-row>
                </v-card-title>
              </v-card>
            </v-col>
            <v-col cols="3">
              <v-card height="100%">
                <v-card-title>
                  <v-row>
                    <v-col cols="10">
                      Pontuação máxima:
                    </v-col>
                    <v-col cols="2">{{ questionnaireTypeData.points_max }}</v-col>
                  </v-row>
                </v-card-title>
              </v-card>
            </v-col>
            <v-col cols="3">
              <v-card height="100%">
                <v-card-title>
                  <v-row>
                    <v-col cols="10">
                      Total de perguntas: {{ questionnaireTypeData.questions.length }}
                    </v-col>
                    <v-col cols="2">
                      <v-btn @click="UpdateShowQuestions" icon>
                        <v-icon v-if="!ShowQuestions" dense>
                          mdi-expand-all
                        </v-icon>
                        <v-icon v-else dense>
                          mdi-close
                        </v-icon>
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-card-title>
              </v-card>
            </v-col>
            <v-col cols="3">
              <v-card height="100%">
                <v-card-title>
                  <v-row>
                    <v-col cols="10">
                      Total de resultados: {{ questionnaireTypeData.results_mappings.length }}
                    </v-col>
                    <v-col cols="2">
                      <v-btn @click="UpdateShowResultsMappings" icon>
                        <v-icon v-if="!ShowResultsMappings" dense>
                          mdi-expand-all
                        </v-icon>
                        <v-icon v-else dense>
                          mdi-close
                        </v-icon>
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-card-title>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
        <div v-if="ShowQuestions">
          <v-card-title>Perguntas</v-card-title>
          <v-data-table :headers="fieldsQuestions" :items="questionnaireTypeData.questions" />
        </div>
        <div v-if="ShowResultsMappings">
          <v-card-title>Resultados</v-card-title>
          <v-data-table :headers="fieldsResultsMappings" :items="questionnaireTypeData.results_mappings">
            <template v-slot:item.points_max="{ item }">
              {{
                item.points_max_inclusive ? item.points_max + " (Inclusive)" :
                item.points_max + " (Exclusive)"
              }}
            </template>
          </v-data-table>
        </div>
      </div>
    </v-card>
    <div v-if="tableLength != 0">
      <v-divider></v-divider>
      <v-card>
        <v-card-title>Questionários efetuados</v-card-title>
        <v-card-text>
          <v-text-field v-model="search" append-icon="mdi-magnify" label="Pesquisar" single-line
            hide-details></v-text-field>
        </v-card-text>
        <v-data-table id="questionnairesTable" :headers="fields" :items="questionnaires" :items-per-page="perPage"
          class="elevation-1" :loading="questionnaires.length === 0" :search="search">
          <template v-slot:item.details="{ item }">
            <v-btn :to="`/questionnaires/${item.details}`" icon>
              <v-icon dense>
                mdi-expand-all
              </v-icon>
            </v-btn>
          </template>
          <template v-slot:item.updated_at="{ item }">
            {{
              item.updated_at != null
              ? new Date(item.updated_at * 1000).toLocaleString("pt-PT")
              : "Data não apresentada"
            }}
          </template>
        </v-data-table>
      </v-card>
    </div>
  </div>
</template>

<script>

export default {
  components: {
  },
  middleware: ("auth", "client"),
  data() {
    return {
      questionnairesTypesData: [],
      questionnairesTypesSelect: [],
      questionnaireType: null,
      questionnaireTypeData: null,
      search: '',
      ShowQuestions: false,
      ShowResultsMappings: false,
      fieldsQuestions: [
        {
          value: "number",
          text: "Número",
          sortDirection: "asc",
        },
        {
          value: "question",
          text: "Pergunta",
          sortDirection: "asc",
        },
      ],
      fieldsResultsMappings: [
        {
          value: "points_min",
          text: "Limite mínimo",
          sortDirection: "asc",
        },
        {
          value: "points_max",
          text: "Limite máximo",
          sortDirection: "asc",
        },
        {
          value: "short_message",
          text: "Resposta",
          sortDirection: "asc",
        },
      ],
      fields: [
        {
          value: "updated_at",
          text: "Data de conclusão",
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
          value: "details",
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
    questionnaireType(newVal) {
      this.questionnaireTypeData = this.getQuestionnaireTypeDataByDisplayName(newVal)[0];
      this.getQuestionnariesByType(this.questionnaireTypeData.name, this.questionnaireTypeData.display_name);
    }
  },
  mounted() {
    this.socket = this.$nuxtSocket({ persist: 'mySocket' })
  },
  async created() {
    await this.getQuestionnariesTypes()
  },
  methods: {
    UpdateShowQuestions() {
      if (this.ShowResultsMappings) this.ShowResultsMappings = false;
      this.ShowQuestions = !this.ShowQuestions;
    },
    UpdateShowResultsMappings() {
      if (this.ShowQuestions) this.ShowQuestions = false;
      this.ShowResultsMappings = !this.ShowResultsMappings;
    },
    getQuestionnaireTypeDataByDisplayName(display_name) {
      return this.questionnairesTypesData.filter(questionnaire => {
        return questionnaire.display_name === display_name;
      });
    },
    async getQuestionnariesTypes() {
      await this.$axios.get("/api/questionnairesTypes").then(response => {
        response.data.data.forEach(questionnaire => {
          this.questionnairesTypesData.push({
            display_name: questionnaire.display_name,
            name: questionnaire.name,
            points_min: questionnaire.points_min,
            points_max: questionnaire.points_max,
            questions: questionnaire.questions,
            results_mappings: questionnaire.results_mappings
          })
          this.questionnairesTypesSelect.push(questionnaire.display_name);
        });
      })
        .catch(() => {
          this.$toast.info("Não existem questionários no sistema").goAway(3000)
        })
    },
    async getQuestionnariesByType(name, display_name) {
      name = name + 's';
      await this.$axios.get("/api/" + name).then(response => {
        this.questionnaires = [];
        response.data.data.forEach(questionnaire => {
          this.questionnaires.push({
            details: name + "/" + questionnaire.id,
            updated_at: questionnaire.updated_at,
            points: questionnaire.points
          })
        });
      })
        .catch(() => {
          this.$toast.info("Não existem questionários efetuados do tipo " + display_name).goAway(3000)
        })
    }
  }
}
</script>

<style></style>
