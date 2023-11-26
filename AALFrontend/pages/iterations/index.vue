<template>
  <div>
    <v-row>
      <v-col cols="12" class="mb-6">
        <highchart v-if="showIterationChart == true" :options="iterationsChartOptions" />
      </v-col>
      <v-col cols="12">
        <v-card>
          <v-card-title>
            Iterações
            <v-spacer></v-spacer>
            <v-text-field v-model="search" append-icon="mdi-magnify" label="Pesquisar" single-line
              hide-details></v-text-field>
          </v-card-title>
          <v-data-table id="iterationsTable" :headers="fields" :items="iterations" :items-per-page="perPage"
            class="elevation-1" :loading="iterations.length === 0" :search="search">
            <template v-slot:item.emotion="{ item }">
              {{ firstCapitalLetter(item.emotion.display_name) }}
            </template>
            <template v-slot:item.contents="{ item }">
              <v-btn :to="`/iterations/${item.id}`" icon>
                <v-icon dense>
                  mdi-expand-all
                </v-icon>
              </v-btn>
            </template>
            <template v-slot:item.created_at="{ item }">
              {{
                item.created_at != null
                ? new Date(item.created_at * 1000).toLocaleString("pt-PT")
                : "---"
              }}
            </template>
            <template v-slot:no-data> Ainda não existem iterações registadas </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
    <v-dialog v-model="showFrameModal" max-width="500px">
      <v-card>
        <v-card-title>
          Imagem selecionada
        </v-card-title>
        <v-card-text>
          <div class="text-center font-mono">
            <h3 v-if="frameOpened.emotion.name !== ''">
              {{ firstCapitalLetter(frameOpened.emotionIteration.name) }} - IA
            </h3>
            <h3 v-else>
              {{ firstCapitalLetter(frameOpened.emotion.name) }} - HL
            </h3>
            <hr />
            <v-img v-if="frameOpened.base64 !== ''" :src="frameOpened.base64" class="w-50 mt-2 mx-auto" width="50%" />
            <br />
            <v-btn color="primary" class="mt-lg-3" @click="frameOpened.showHLForm = !frameOpened.showHLForm">Classify {{
              frameOpened.showHLForm ? "-" : "+" }}
            </v-btn>
          </div>
          <div v-if="frameOpened.showHLForm" class="mt-lg-2">
            <v-form ref="form" @submit.prevent="classify(frameOpened.id, frameOpened.base64)">
              <v-select v-model="frameOpened.emotionClassified" label="Emoções" :items="frameOpened.humanLabelEmotions"
                clearable required>
                <template #append-outer>
                  <v-btn icon color="green" :disabled="frameOpened.emotionClassified === null" type="submit">
                    <v-icon>
                      mdi-send
                    </v-icon>
                  </v-btn>
                </template>
              </v-select>
            </v-form>
          </div>
          <div class="text-center font-mono pt-lg-4">
            <hr class="mb-lg-3" />
            <v-tabs v-model="frameTab" content-class="mt-3">
              <v-tab :disabled="
                frameOpenedAllPredictionsChartOptions.xAxis.categories.length <= 3
              ">
                Todas as previsões
              </v-tab>
              <v-tab title="Por grupo" :active="
                frameOpenedAllPredictionsChartOptions.xAxis.categories.length <= 3
              ">Por grupo</v-tab>
              <v-tabs-items v-model="frameTab">
                <v-tab-item>
                  <highchart v-if="showFrameOpenedAllPredictionsChart === true"
                    :options="frameOpenedAllPredictionsChartOptions" />
                </v-tab-item>
                <v-tab-item>
                  <highchart v-if="showFrameOpenedByGroupChart === true" :options="frameOpenedByGroupChartOptions" />
                </v-tab-item>
              </v-tabs-items>
            </v-tabs>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" text @click="showFrameModal = false">
            Fechar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>

import Highcharts from "highcharts"

export default {
  components: {
  },
  middleware: ("auth", "client"),
  data() {
    return {
      search: '',
      fields: [
        {
          value: "emotion",
          text: "Emoção",
          sortDirection: "desc",
        },
        {
          value: "created_at",
          text: "Data",
          sortDirection: "desc",
        },
        {
          value: "contents",
          text: "Conteúdo",
          sortable: false,
        },
      ],
      socket: null,
      showIterationChart: false,
      emotionsDataGraph: [],
      iterations: [],
      perPage: 10,
      currentPage: 1,
      yLabels: [],
      showFrameOpenedAllPredictionsChart: false,
      frameOpenedAllPredictionsChartOptions: {
        chart: {
          type: "column",
          height: 200,
          backgroundColor: "rgba(0,0,0,0)",
        },
        title: {
          text: "Predictions",
        },
        yAxis: {
          title: {
            text: "Accuracy",
          },
          categories: [],
          min: 0,
          max: 100,
        },
        xAxis: {
          title: {
            text: "Emotions",
          },
          categories: [],
        },
        series: [
          /* {
            name:  "Time",
            data: [],
            color: "#03045e",
            marker: {
                enabled: true,
                radius: 5
            },
          }, */
        ],
      },
      showFrameOpenedByGroupChart: false,
      frameOpenedByGroupChartOptions: {
        chart: {
          type: "column",
          height: 250,
          backgroundColor: "rgba(0,0,0,0)",

        },
        title: {
          text: "Predictions",
        },
        yAxis: {
          title: {
            text: "Accuracy",
          },
          categories: [],
          min: 0,
          max: 100,
        },
        xAxis: {
          title: {
            text: "Emotions",
          },
          categories: [],
        },
        series: [
          /* {
            name:  "Time",
            data: [],
            color: "#03045e",
            marker: {
                enabled: true,
                radius: 5
            },
          }, */
        ],
      },
      iterationsChartOptions: {
        chart: {
          type: "line",
          backgroundColor: "rgba(0,0,0,0)",
          zoomType: "x",
        },
        plotOptions: {
          series: {
            cursor: "pointer",
            point: {
              events: {
                //
              },
            },
          },
        },
        title: {
          text: "Emoções ao longo do tempo",
        },
        yAxis: {
          type: "category",
          title: {
            text: "Emoções",
          },
          categories: [],
        },
        xAxis: {
          type: "timestamps",
          title: {
            text: "Tempo",
          },
          labels: {
            format: "{value:%Y-%m-%d %H:%M}",
          }
        },
        series: [
          /* {
            name:  "Time",
            data: [],
            color: "#03045e",
            marker: {
                enabled: true,
                radius: 5
            },
          }, */
        ],
      },
      frameOpened: {
        showHLForm: false,
        createDate: "",
        emotion: {},
        emotionIteration: {},
        id: -1,
        base64: "",
        emotionClassified: null,
        humanLabelEmotions: [],
      },
      invalidEmotion: {},
      showFrameModal: false,
      frameTab: null
    }
  },
  computed: {
    currentUser() {
      return this.$auth.user
    },
    tableLength() {
      return this.iterations.length
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
    this.socket = this.$nuxtSocket({ persist: 'mySocket' })
  },
  async created() {
    await this.getEmotions()
    this.getIterations()
  },
  methods: {
    classify(id, base64) {
      this.$axios
        .$patch("/api/frames/" + id + "/classify", {
          name: this.frameOpened.emotionClassified,
        })
        .then(() => {
          this.$toast
            .success("Imagem com o nº " + id + " classificada com sucesso")
            .goAway(3000)

          // Connection opened
          // console.log(this.socket)
          const jsonData =
            '{ "userId": ' + this.$auth.user.id +
            ',"emotion" : "' +
            this.frameOpened.emotionClassified +
            '", "image": "' +
            base64 +
            '"}'
          this.socket.emit('newFrameMessage', jsonData)
          this.hideModal()
          this.collectGraphData()
        })
    },
    async showModal(point) {
      while (this.frameOpened.humanLabelEmotions.length) { this.frameOpened.humanLabelEmotions.pop() }
      await this.$axios.$get("/api/frames/" + point.id).then(async ({ data }) => {
        data.createDate = data.createDate * 1000
        this.frameOpened.createDate = data.createDate
        this.frameOpened.emotion = data.emotion
        this.frameOpened.emotionIteration = data.emotionIteration
        this.frameOpened.id = data.id

        this.frameOpened.emotionClassified =
          this.frameOpened.emotion.name !== ""
            ? this.frameOpened.emotion.name
            : null

        this.frameOpenedAllPredictionsChartOptions.xAxis.categories = []
        this.frameOpenedAllPredictionsChartOptions.series = [
          {
            showInLegend: false,
            data: [],
          },
        ]

        this.frameOpenedByGroupChartOptions.xAxis.categories = []
        this.frameOpenedByGroupChartOptions.series = [
          {
            showInLegend: false,
            data: [],
          },
        ]

        data.predictions.forEach(p => {
          this.frameOpenedAllPredictionsChartOptions.xAxis.categories.push(
            this.firstCapitalLetter(p.emotion.name)
          )
          this.frameOpenedAllPredictionsChartOptions.series[0].data.push(
            p.accuracy
          )

          if (
            !this.frameOpenedByGroupChartOptions.xAxis.categories.includes(
              p.emotion.category
            )
          ) {
            this.frameOpenedByGroupChartOptions.xAxis.categories.push(
              this.firstCapitalLetter(p.emotion.category)
            )
            this.frameOpenedByGroupChartOptions.series[0].data.push(p.accuracy)
          } else {
            const index =
              this.frameOpenedByGroupChartOptions.xAxis.categories.indexOf(
                this.firstCapitalLetter(p.emotion.category)
              )
            this.frameOpenedByGroupChartOptions.series[0].data[index] +=
              p.accuracy
          }
        })

        this.showFrameOpenedAllPredictionsChart = true
        this.showFrameOpenedByGroupChart = true

        await this.$axios
          .$get("/api/frames/download/" + this.frameOpened.id)
          .then(imageBase64 => {
            this.frameOpened.base64 = imageBase64
          })
        this.$axios
          .$get(
            "/api/emotions/groups/" + this.frameOpened.emotionIteration.name
          )
          .then(({ data }) => {
            this.frameOpened.humanLabelEmotions.push({
              value: null,
              text: "Por favor selecione uma emoção",
              disabled: true,
            })
            data.forEach(e => {
              if (e.name !== this.frameOpened.emotionIteration.name)
                this.frameOpened.humanLabelEmotions.push({
                  value: e.name,
                  text: this.firstCapitalLetter(e.name),
                })
            })
            if (this.invalidEmotion.value !== undefined)
              this.frameOpened.humanLabelEmotions.push(this.invalidEmotion)
          })
      })
      this.showFrameModal = true
    },
    hideModal() {
      this.showFrameModal = false
      this.frameOpened.showHLForm = false
      this.frameOpened.createDate = ""
      this.frameOpened.emotion = {}
      this.frameOpened.emotionIteration = {}
      this.frameOpened.id = -1
      this.frameOpened.base64 = ""
      this.frameOpened.emotionClassified = null
      while (this.frameOpened.humanLabelEmotions.length) { this.frameOpened.humanLabelEmotions.pop() }
      console.log(this.frameOpened.humanLabelEmotions.length)
    },
    firstCapitalLetter(str = "") {
      return str.toString().charAt(0).toUpperCase() + str.toString().slice(1)
    },
    async collectGraphData() {
      const graphData = []
      this.iterationsChartOptions.series = []
      var dates = []
      for (let i = 0; i < this.yLabels.length; i++) {
        var iterations = this.iterations.filter(iteration => {
          return this.firstCapitalLetter(iteration.emotion.display_name) === this.yLabels[i];
        });
        var data = [];
        iterations.forEach(iteration => {
          var idx = this.yLabels.indexOf(this.firstCapitalLetter(iteration.emotion.display_name));
          var date = iteration.created_at * 1000;
          data.push([date, idx])
          dates.push(date)
        });
        graphData[i] = data;
        this.iterationsChartOptions.series.push({
          name: this.yLabels[i],
          data: graphData[i]
        })
      }
      this.showIterationChart = true
      return graphData
    },
    async getEmotions() {
      await this.$axios.get("/api/emotions").then(response => {
        const emotions = response.data.data
        emotions.forEach(emotion => {
          if (emotion.category !== "invalid")
            this.yLabels.push(this.firstCapitalLetter(emotion.display_name))
          else
            this.invalidEmotion = {
              value: emotion.display_name,
              text: this.firstCapitalLetter(emotion.display_name),
            }
        })
        this.iterationsChartOptions.yAxis.categories = this.yLabels
      })
        .catch(() => {
          this.$toast.info("Não existem emoções registadas no sistema").goAway(3000)
        })
    },
    getIterations() {
      this.$axios
        .$get("/api/iterations")
        .then(response => {
          this.iterations = response.data
          if (this.iterations !== []) {
            this.collectGraphData()
          }
        })
        .catch(() => {
          this.$toast.info("Não existem iterações registadas no sistema").goAway(3000)
        })
    },
    render() {
      this.chart = Highcharts.chart("container", {
        ...this.config,
      })
    },
  },
}
</script>

<style></style>
