<template>
  <div>
    <navbar />
    <b-container class="mb-lg-5">
      <h1 class="font-mono text-center pb-lg-4 text-red-400">Iterations</h1>
      <div class="rounded-md backdrop-blur-md bg-black/5">
        <b-container class="p-lg-5">
          <highchart v-if="showIterationChart == true"
                     :options="iterationsChartOptions"
          />
          <div class="mt-5" v-if="tableLength != 0">
            <b-table
              id="iterationsTable"
              :items="iterations"
              :fields="fields"
              :current-page="currentPage"
              :per-page="perPage"
              striped
              borderless
              hover
              table-variant="light"
              responsive="sm"
            >
              <template #cell(emotion)="data">
                {{
                  firstCapitalLetter(data.item.emotion.name)
                }}
              </template>
              <template v-slot:cell(Frames)="row">
                <b-button variant="dark" :to="`/iterations/${row.item.id}`">
                  View
                </b-button>
              </template>
              <template #cell(created_at)="data">
                {{
                  data.item.created_at != null
                    ? new Date(data.item.created_at).toLocaleString("pt-PT")
                    : "Not Shown"
                }}
              </template>
            </b-table>
            <b-pagination
              v-model="currentPage"
              :total-rows="tableLength"
              :per-page="perPage"
              aria-controls="my-table"
              align="center"
            ></b-pagination>
          </div>
          <div v-else class="w-75 mx-auto alert alert-info">No iterations were created so far</div>
        </b-container>
      </div>
    </b-container>
    <b-modal ref="modal" hide-footer title="Frame Selected" header-class="font-mono" @hide="hideModal()">
      <div class="text-center font-mono">
        <h3>{{firstCapitalLetter(frameOpened.emotionIteration.name)}} - IA</h3>
        <h3 v-if="frameOpened.emotion.name !== ''">{{firstCapitalLetter(frameOpened.emotion.name)}} - HL</h3>
        <hr>
        <img v-if="frameOpened.base64 !== ''" :src="frameOpened.base64" class="w-50 mt-2 mx-auto" >
        <br>
        <b-button @click="frameOpened.showHLForm = !frameOpened.showHLForm" variant="outline-primary" class="mt-lg-2">Classify {{frameOpened.showHLForm ? "-" : "+"}} </b-button>
      </div>
      <div v-if="frameOpened.showHLForm" class="mt-lg-2">
        <form v-on:submit.prevent="classify(frameOpened.id, frameOpened.base64)">
          <div class="input-group">
            <b-select
              v-model="frameOpened.emotionClassified"
              :options="frameOpened.humanLabelEmotions"
              required
            >
            </b-select>
            <div class="input-group-append">
              <b-button type="submit" variant="outline-primary">
                Ok
              </b-button>
            </div>
          </div>
        </form>
      </div>
      <div class="text-center font-mono pt-lg-4">
        <hr class="mb-lg-3">
        <b-tabs content-class="mt-3">
          <b-tab title="All Predictions" :disabled="frameOpenedAllPredictionsChartOptions.xAxis.categories.length <= 3">
            <highchart v-if="showFrameOpenedAllPredictionsChart === true"
                       :options="frameOpenedAllPredictionsChartOptions"
            />
          </b-tab>
          <b-tab title="By group" :active="frameOpenedAllPredictionsChartOptions.xAxis.categories.length <= 3">
            <highchart v-if="showFrameOpenedByGroupChart === true"
                       :options="frameOpenedByGroupChartOptions"
            />
          </b-tab>
        </b-tabs>
      </div>

    </b-modal>
  </div>
</template>

<script>
import navbar from "~/components/utils/NavBar.vue";

export default {
  middleware: "auth",
  data() {
    return {
      fields: [
        {
          key: "emotion",
          label: "Emotion",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "created_at",
          label: "Data",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "classifiedFrames",
          label: "Classified Frames",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "totalFrames",
          label: "Total Frames",
          sortable: true,
          sortDirection: "desc",
        },
        "Frames",
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
          backgroundColor: 'rgba(0,0,0,0)',
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
          max: 100
        },
        xAxis: {
          title: {
            text: "Emotions",
          },
          categories: [],
        },
        series: [
          /*{
            name:  "Time",
            data: [],
            color: "#03045e",
            marker: {
                enabled: true,
                radius: 5
            },
          },*/
        ],
      },
      showFrameOpenedByGroupChart: false,
      frameOpenedByGroupChartOptions: {
        chart: {
          type: "column",
          backgroundColor: 'rgba(0,0,0,0)',
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
          max: 100
        },
        xAxis: {
          title: {
            text: "Emotions",
          },
          categories: [],
        },
        series: [
          /*{
            name:  "Time",
            data: [],
            color: "#03045e",
            marker: {
                enabled: true,
                radius: 5
            },
          },*/
        ],
      },
      iterationsChartOptions: {
        chart: {
          type: "line",
          backgroundColor: 'rgba(0,0,0,0)',
          zoomType: 'x'
        },
        plotOptions: {
          series: {
            cursor: 'pointer',
            point: {
              events: {
                //
              }
            }
          }
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
          /*{
            name:  "Time",
            data: [],
            color: "#03045e",
            marker: {
                enabled: true,
                radius: 5
            },
          },*/
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
        humanLabelEmotions: []
      },
      invalidEmotion: {}
    };
  },
  components: {
    navbar,
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
            .goAway(3000);
          // Connection opened
          //console.log(this.socket)
          let jsonData =
            '{ "emotion" : "' +
            this.frameOpened.emotionClassified +
            '", "image": "' +
            base64 +
            '"}';
          if (this.socket.readyState == 1) this.socket.send(jsonData);

          this.collectGraphData();
          this.hideModal()
        });
    },
    async showModal(point) {
      // console.log(point.x, point.y, point.id, point)

      await this.$axios
        .$get("/api/frames/" + point.id)
        .then((r)=> {

          this.frameOpened.createDate = r.createDate
          this.frameOpened.emotion = r.emotion
          this.frameOpened.emotionIteration = r.emotionIteration
          this.frameOpened.id = r.id

          this.frameOpened.emotionClassified = this.frameOpened.emotion.name !== "" ? this.frameOpened.emotion.name : null

          this.frameOpenedAllPredictionsChartOptions.xAxis.categories = []
          this.frameOpenedAllPredictionsChartOptions.series = [{
            showInLegend: false,
            data: []
          }]

          this.frameOpenedByGroupChartOptions.xAxis.categories = []
          this.frameOpenedByGroupChartOptions.series = [{
            showInLegend: false,
            data: []
          }]

          r.predictions.forEach((p) => {
            this.frameOpenedAllPredictionsChartOptions.xAxis.categories.push(this.firstCapitalLetter(p.emotion.name))
            this.frameOpenedAllPredictionsChartOptions.series[0].data.push(p.accuracy)

            if (!this.frameOpenedByGroupChartOptions.xAxis.categories.includes(p.emotion["group"])) {
              this.frameOpenedByGroupChartOptions.xAxis.categories.push(this.firstCapitalLetter(p.emotion["group"]))
              this.frameOpenedByGroupChartOptions.series[0].data.push(p.accuracy)
            }else {
              let index = this.frameOpenedByGroupChartOptions.xAxis.categories.indexOf(this.firstCapitalLetter(p.emotion["group"]))
              this.frameOpenedByGroupChartOptions.series[0].data[index] += p.accuracy
            }
          })

          this.showFrameOpenedAllPredictionsChart = true
          this.showFrameOpenedByGroupChart = true

          this.$axios
            .$get("/api/frames/download/" + this.frameOpened.id)
            .then((imageBase64) => {
              this.frameOpened.base64 = "data:image/jpg;base64," + imageBase64
            });
          this.$axios
            .$get("/api/emotions/groups/" + this.frameOpened.emotionIteration.name)
            .then((emotions) => {
              this.frameOpened.humanLabelEmotions.push({ value: null, text: 'Please select an emotion', disabled: true })
              emotions.forEach(e => {
                this.frameOpened.humanLabelEmotions.push({ value: e.name, text: this.firstCapitalLetter(e.name) })
              })
              this.frameOpened.humanLabelEmotions.push(this.invalidEmotion)
            })
        })

      this.$refs["modal"].show();
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

      this.$refs["modal"].hide();
    },
    firstCapitalLetter(str=""){
      return str.toString().charAt(0).toUpperCase() + str.toString().slice(1)
    },
    async collectGraphData() {

      let graphData = [];
      this.iterationsChartOptions.series = []

      for (let i  = 0; i < this.yLabels.length ; i++) {
        graphData[i] = []
        this.iterationsChartOptions.series.push({
          name : this.yLabels[i],
          data : graphData[i],
          marker: {
            enabled: true,
            radius: 5
          }
        })
      }
      let pointGraph = [];
      await this.$axios
    .$get("/api/frames/clients/"+this.currentUser.id+"/graphData")
    .then((data) => {
        data.forEach(r => {


          pointGraph.push(r.createDate)
          pointGraph.push(r.accuracy)
          pointGraph.push(r.id)

          if (r.emotion_classified === "N/A") {
            graphData[this.yLabels.indexOf(this.firstCapitalLetter(r.emotion_predicted))].push({id: r.id, x: r.createDate, y: r.accuracy})
          }else {
            if (r.emotion_classified !== 'invalid')
              graphData[this.yLabels.indexOf(this.firstCapitalLetter(r.emotion_classified))].push({id: r.id, x: r.createDate, y: r.accuracy})
          }

          pointGraph = []
        });

        const _this = this
        this.iterationsChartOptions.plotOptions.series.point.events = {
          click () {
            _this.showModal(this)
          }
        }

        this.showIterationChart = true;
      })
      .catch((error) => {
        if (typeof variable !== 'undefined')
          this.$toast.info(error.response.data).goAway(3000);
        else
          console.log(error)
          this.$toast.info(error).goAway(3000);
      });
      return graphData;
    },
    async getEmotions() {
      await this.$axios.get("/api/emotions").then((response) => {
        let emotions = response.data;
        emotions.forEach((emotion) => {
          if (emotion.group !== "invalid")
            this.yLabels.push(this.firstCapitalLetter(emotion.name));
          else
            this.invalidEmotion = {value: emotion.name, text: this.firstCapitalLetter(emotion.name)};
        });

        this.iterationsChartOptions.yAxis.categories = this.yLabels;
      });
    },
    getIterations() {
      this.$axios
        .$get("/api/iterations")
        .then((iterations) => {
          this.iterations = iterations;
          if (this.iterations !== []) {
            this.collectGraphData();
            //this.iterationsChartOptions.series[0].data =

          }
        })
        .catch(() => {
          this.$toast.info("No iterations found").goAway(3000);
        });
    },
    render() {
      this.chart = Highcharts.chart('container', {
        ...this.config
      })
    }
  },
  mounted() {
    this.socket = new WebSocket(
      process.env.FRAMES_WEBSOCKET_URL + this.$auth.user.id
    );
  },
  async created() {
    await this.getEmotions();
    this.getIterations();
  },
  computed: {
    currentUser() {
      return this.$auth.user;
    },
    tableLength() {
      return this.iterations.length;
    },
  },
  watch: {
    config: {
      handler() {
        this.render();
      },
      deep: true
    },
  },

};
</script>

<style>

</style>
