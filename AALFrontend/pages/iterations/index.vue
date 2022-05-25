<template>
  <div>
    <v-row>
      <v-col
        cols="12"
        class="mb-6"
      >
        <highchart
          v-if="showIterationChart == true"
          :options="iterationsChartOptions"
        />
      </v-col>
      <v-col cols="12">
        <v-card>
          <v-card-title>
            Iterations
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
          id="iterationsTable"
          :headers="fields"
          :items="iterations"
          :items-per-page="perPage"
          class="elevation-1"
          :loading="iterations.length === 0"
          :search="search"
        >
          <template v-slot:item.emotion="{item}">
            {{ firstCapitalLetter(item.emotion.name) }}
          </template>
          <template v-slot:item.frames="{item}">
            <v-btn :to="`/iterations/${item.id}`" icon>
              <v-icon dense>
                mdi-expand-all
              </v-icon>
            </v-btn>
          </template>
          <template v-slot:item.created_at="{item}">
            {{
              item.created_at != null
                ? new Date(item.created_at).toLocaleString("pt-PT")
                : "Not Shown"
            }}
          </template>

        </v-data-table>
        </v-card>
      </v-col>
    </v-row>
    <v-dialog
      v-model="showFrameModal"
      max-width="500px"
    >
      <v-card>
        <v-card-title>
          Frame Selected
        </v-card-title>
        <v-card-text>
          <div class="text-center font-mono">
            <h3>
              {{ firstCapitalLetter(frameOpened.emotionIteration.name) }} - IA
            </h3>
            <h3 v-if="frameOpened.emotion.name !== ''">
              {{ firstCapitalLetter(frameOpened.emotion.name) }} - HL
            </h3>
            <hr />
            <v-img
              v-if="frameOpened.base64 !== ''"
              :src="frameOpened.base64"
              class="w-50 mt-2 mx-auto"
              width="50%"
            />
            <br />
            <v-btn
              color="primary"
              class="mt-lg-3"
              @click="frameOpened.showHLForm = !frameOpened.showHLForm"
            >Classify {{ frameOpened.showHLForm ? "-" : "+" }}
            </v-btn>
          </div>
          <div v-if="frameOpened.showHLForm" class="mt-lg-2">
            <v-form
              ref="form"
              @submit.prevent="classify(frameOpened.id, frameOpened.base64)"
            >
                <v-select
                  v-model="frameOpened.emotionClassified"
                  label="Emotions"
                  :items="frameOpened.humanLabelEmotions"
                  clearable
                  required
                >
                  <template #append-outer>
                    <v-btn
                      icon
                      color="green"
                      :disabled="frameOpened.emotionClassified === null"
                      type="submit"
                    >
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
            <v-tabs v-model="frameTab" content-class="mt-3" >
              <v-tab
                :disabled="
              frameOpenedAllPredictionsChartOptions.xAxis.categories.length <= 3
            "
              >
                All Predictions
              </v-tab>
              <v-tab
                title="By group"
                :active="
              frameOpenedAllPredictionsChartOptions.xAxis.categories.length <= 3
            "
              >
                By group
              </v-tab>
              <v-tabs-items v-model="frameTab">
                <v-tab-item>
                  <highchart
                    v-if="showFrameOpenedAllPredictionsChart === true"
                    :options="frameOpenedAllPredictionsChartOptions"
                  />
                </v-tab-item>
                <v-tab-item>
                  <highchart
                    v-if="showFrameOpenedByGroupChart === true"
                    :options="frameOpenedByGroupChartOptions"
                  />
                </v-tab-item>
              </v-tabs-items>
            </v-tabs>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-btn
            color="primary"
            text
            @click="showFrameModal = false"
          >
            Close
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
          text: "Emotion",
          sortDirection: "desc",
        },
        {
          value: "created_at",
          text: "Data",
          sortDirection: "desc",
        },
        {
          value: "classifiedFrames",
          text: "Classified Frames",
          sortDirection: "desc",
        },
        {
          value: "totalFrames",
          text: "Total Frames",
          sortDirection: "desc",
        },
        {
          value: "frames",
          text: "Frames",
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
          text: "Emotions over time",
        },
        yAxis: {
          title: {
            text: "Emotions",
          },
          categories: [],
        },
        xAxis: {
          type: "datetime",
          title: {
            text: "Datetime",
          },
          labels: {
            format: "{value:%Y-%m-%d %H:%M:%S}",
          },
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
      iterationsBarChartOptions: {
        chart: {
          type: "column",
        },
        title: {
          text: "Iterations over time",
        },
        yAxis: {
          title: {
            text: "Nº Iterations",
          },
        },
        xAxis: {
          title: {
            text: "Time",
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
    this.socket = new WebSocket(
      process.env.FRAMES_WEBSOCKET_URL + this.$auth.user.id
    )
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
            .success("Frame nº " + id + " classified successfully")
            .goAway(3000)

          // Connection opened
          // console.log(this.socket)
          const jsonData =
            '{ "emotion" : "' +
            this.frameOpened.emotionClassified +
            '", "image": "' +
            base64 +
            '"}'
          if (this.socket.readyState === 1) this.socket.send(jsonData)

          this.collectGraphData()
          this.hideModal()
        })
    },
    async showModal(point) {
      // console.log(point.x, point.y, point.id, point)

      await this.$axios.$get("/api/frames/" + point.id).then(r => {
        this.frameOpened.createDate = r.createDate
        this.frameOpened.emotion = r.emotion
        this.frameOpened.emotionIteration = r.emotionIteration
        this.frameOpened.id = r.id

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

        r.predictions.forEach(p => {
          this.frameOpenedAllPredictionsChartOptions.xAxis.categories.push(
            this.firstCapitalLetter(p.emotion.name)
          )
          this.frameOpenedAllPredictionsChartOptions.series[0].data.push(
            p.accuracy
          )

          if (
            !this.frameOpenedByGroupChartOptions.xAxis.categories.includes(
              p.emotion.group
            )
          ) {
            this.frameOpenedByGroupChartOptions.xAxis.categories.push(
              this.firstCapitalLetter(p.emotion.group)
            )
            this.frameOpenedByGroupChartOptions.series[0].data.push(p.accuracy)
          } else {
            const index =
              this.frameOpenedByGroupChartOptions.xAxis.categories.indexOf(
                this.firstCapitalLetter(p.emotion.group)
              )
            this.frameOpenedByGroupChartOptions.series[0].data[index] +=
              p.accuracy
          }
        })

        this.showFrameOpenedAllPredictionsChart = true
        this.showFrameOpenedByGroupChart = true

        this.$axios
          .$get("/api/frames/download/" + this.frameOpened.id)
          .then(imageBase64 => {
            this.frameOpened.base64 = "data:image/jpg;base64," + imageBase64
          })
        this.$axios
          .$get(
            "/api/emotions/groups/" + this.frameOpened.emotionIteration.name
          )
          .then(emotions => {
            this.frameOpened.humanLabelEmotions.push({
              value: null,
              text: "Please select an emotion",
              disabled: true,
            })
            emotions.forEach(e => {
              this.frameOpened.humanLabelEmotions.push({
                value: e.name,
                text: this.firstCapitalLetter(e.name),
              })
            })
            this.frameOpened.humanLabelEmotions.push(this.invalidEmotion)
          })
      })
      this.showFrameModal = true
    },
    hideModal() {
      this.frameOpened.showHLForm = false
      this.frameOpened.createDate = ""
      this.frameOpened.emotion = {}
      this.frameOpened.emotionIteration = {}
      this.frameOpened.id = -1
      this.frameOpened.base64 = ""
      this.frameOpened.emotionClassified = null
      this.frameOpened.humanLabelEmotions = []
    },
    firstCapitalLetter(str = "") {
      return str.toString().charAt(0).toUpperCase() + str.toString().slice(1)
    },
    async collectGraphData() {
      const graphData = []
      this.iterationsChartOptions.series = []

      for (let i = 0; i < this.yLabels.length; i++) {
        graphData[i] = []
        this.iterationsChartOptions.series.push({
          name: this.yLabels[i],
          data: graphData[i],
          marker: {
            enabled: true,
            radius: 5,
          },
        })
      }

      let pointGraph = []
      await this.$axios
        .$get("/api/frames/clients/" + this.currentUser.id + "/graphData")
        .then(data => {
          data.forEach(r => {
            pointGraph.push(r.createDate)
            pointGraph.push(r.accuracy)
            pointGraph.push(r.id)

            if (r.emotion_classified === "N/A") {
              graphData[
                this.yLabels.indexOf(
                  this.firstCapitalLetter(r.emotion_predicted)
                )
                ].push({ id: r.id, x: r.createDate, y: r.accuracy })
            } else if (r.emotion_classified !== "invalid")
                graphData[
                  this.yLabels.indexOf(
                    this.firstCapitalLetter(r.emotion_classified)
                  )
                  ].push({ id: r.id, x: r.createDate, y: r.accuracy })

            pointGraph = []
          })

          const _this = this
          this.iterationsChartOptions.plotOptions.series.point.events = {
            click() {
              _this.showModal(this)
            },
          }

          this.showIterationChart = true
        })
        .catch(error => {
          if (typeof variable !== "undefined")
            this.$toast.info(error.response.data).goAway(3000)
          else console.log(error)
          this.$toast.info(error).goAway(3000)
        })

return graphData
    },
    async getEmotions() {
      await this.$axios.get("/api/emotions").then(response => {
        const emotions = response.data
        emotions.forEach(emotion => {
          if (emotion.group !== "invalid")
            this.yLabels.push(this.firstCapitalLetter(emotion.name))
          else
            this.invalidEmotion = {
              value: emotion.name,
              text: this.firstCapitalLetter(emotion.name),
            }
        })

        this.iterationsChartOptions.yAxis.categories = this.yLabels
      })
    },
    getIterations() {
      this.$axios
        .$get("/api/iterations")
        .then(iterations => {
          this.iterations = iterations
          if (this.iterations !== []) {
            this.collectGraphData()

            // this.iterationsChartOptions.series[0].data =
          }
        })
        .catch(() => {
          this.$toast.info("No iterations found").goAway(3000)
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

<style>
</style>
