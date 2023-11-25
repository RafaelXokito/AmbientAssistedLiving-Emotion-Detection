<template>
  <div>
    <back-button></back-button>
    <div class="mt-5 ml-5 text-center">
      <h1 class="text-red-400">Questionário nº {{ id }}</h1>
    </div>
    <div>
      <!--<highchart
          v-if="showIterationChart == true"
          :options="chartOptions"
        />-->
    </div>
    <v-card>
          <v-card-title>
            Respostas
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
          id="responsesTable"
          :headers="fields"
          :items="responses"
          :items-per-page="perPage"
          class="elevation-1"
          :loading="responses.length === 0"
          :search="search"
        >
        <template v-slot:item.created_at="{ item }">
              {{
                item.created_at != null
                ? new Date(item.created_at * 1000).toLocaleString("pt-PT")
                : "Data não apresentada"
              }}
            </template>
        </v-data-table>
    </v-card>
  </div>
</template>

<script>

import BackButton from "~/components/utils/BackButton"
export default {
  components: {BackButton},
  middleware: ("auth", "client"),
  data() {
    return {
      responses: [],
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
          value: "created_at",
          text: "Data",
          sortDirection: "desc",
        }
        ],
        responsesGraphData:  [],
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
    console.log("/api/"+this.type+"/"+this.id)
      this.$axios.$get("/api/"+this.type+"/"+this.id+"?details=true").then(response => {
        this.responses = response.data.responses
      })
  }
}
</script>

<style>
</style>
