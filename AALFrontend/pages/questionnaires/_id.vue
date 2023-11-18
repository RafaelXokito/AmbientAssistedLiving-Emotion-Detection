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
        </v-data-table>
    </v-card>
  </div>
</template>

<script>

import BackButton from "~/components/utils/BackButton"
import Highcharts from 'highcharts'
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
          value: "is_why",
          text: "Justificação",
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
  },
  created() {
      this.$axios.$get("/api/geriatricQuestionnaires/" + this.id).then(({data}) => {
        this.responses = data.responses
      })
      this.getGraphData()
  },
  watch: {
    config: {
      handler() {
        this.render()
      },
      deep: true,
    },
  },
  methods:{
    getGraphData(){
      this.$axios.$get("/api/geriatricQuestionnaires/statistics/" + this.id).then(({data}) => {
       for (const key in data) {
        this.chartOptions.series.push({name:key,data: [data[key]]})
       }
        //console.log(this.responsesGraphData)
        //chartOptions.series[0].data=
        this.showIterationChart = true
      })
      /*.catch(() => {
          this.$toast.info("No questionnaire found").goAway(3000)
        })*/
    },
    render() {
      this.chart = Highcharts.chart("container", {
        ...this.config,
      })
    }
  }
}
</script>

<style>
</style>
