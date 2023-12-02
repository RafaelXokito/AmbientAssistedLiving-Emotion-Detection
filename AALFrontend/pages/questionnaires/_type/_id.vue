<template>
  <div>
    <back-button></back-button>
    <div>
        <h2 class="text-center">Questionário nº {{ id }}</h2>
        <v-row class="mt-10">
          <v-col cols="4">
            <v-card height="100%">
              <v-card-title>
                <v-row>
                  <v-col cols="10">
                    Pontuação:
                  </v-col>
                  <v-col cols="2">{{ questionnaire.points }}</v-col>
                </v-row>
              </v-card-title>
            </v-card>
          </v-col>
          <v-col cols="4">
            <v-card height="100%">
              <v-card-title>
                {{ questionnaire.short_message }}
              </v-card-title>
            </v-card>
          </v-col>
          <v-col cols="4" v-if="questionnaire.responses != undefined">
            <v-card height="100%">
              <v-card-title>
                Total de respostas: {{ this.questionnaire.responses.length }}
              </v-card-title>
            </v-card>
          </v-col>
        </v-row>  
    </div>
    <v-card class="mt-15">
      <v-card-title>
        Respostas
        <v-spacer></v-spacer>
        <v-text-field v-model="search" append-icon="mdi-magnify" label="Pesquisar" single-line
          hide-details></v-text-field>
      </v-card-title>
      <v-data-table v-if="questionnaire.responses != undefined" id="responsesTable" :headers="fields"
        :items="questionnaire.responses" :items-per-page="perPage" class="elevation-1"
        :loading="questionnaire.responses.length === 0" :search="search">
        <template v-slot:item.created_at="{ item }">
          {{
            item.created_at != null
            ? new Date(item.created_at * 1000).toLocaleString("pt-PT")
            : "Data não apresentada"
          }}
        </template>
        <template v-slot:item.is_why="{ item }">
          <div v-if="item.is_why">✅</div>
          <div v-else>❌</div>
        </template>
        <template v-slot:no-data> Ainda não existem respostas registadas </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>

import BackButton from "~/components/utils/BackButton"
export default {
  components: { BackButton },
  middleware: ("auth", "client"),
  data() {
    return {
      questionnaire: {},
      perPage: 30,
      currentPage: 1,
      search: '',
      showIterationChart: false,
      fields: [
        {
          value: "question",
          text: "Pergunta",
          sortDirection: "desc",
        },
        {
          value: "response",
          text: "Resposta",
          sortDirection: "desc",
        },
        {
          value: "is_why",
          text: "Justificação da pergunta",
          sortDirection: "desc",
        },
        {
          value: "emotion",
          text: "Emoção detetada",
          sortDirection: "desc",
        },
        {
          value: "created_at",
          text: "Data",
          sortDirection: "desc",
        }
      ],
      responsesGraphData: [],
      chartOptions: {
        chart: {
          type: 'column'
        },
        title: {
          text: 'Comparação entre os resultados do questionário e a avaliação do modelo de Inteligência Artificial'
        },
        xAxis: {
          categories: ['Resultado do questionário', 'Valor máximo do intervalo do resultado do questionário', 'Resultado do modelo de Inteligência Artificial']
        },
        yAxis: {
          min: 0,
          max: 15,
          title: {
            text: 'Value'
          }
        },
        series: []
      }
    }
  },
  computed: {
    id() {
      return this.$route.params.id
    },
    type() {
      return this.$route.params.type
    },
  },
  created() {
    this.$axios.$get("/api/" + this.type + "/" + this.id + "?details=true").then(response => {
      this.questionnaire = response.data
      console.log(response.data)
    })
  }
}
</script>

<style></style>
