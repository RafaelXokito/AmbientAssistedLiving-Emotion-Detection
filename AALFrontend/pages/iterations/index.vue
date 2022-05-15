<template>
  <div>
    <navbar />
    <b-container >
      <h1>Iterations</h1>
      <div class="backdrop-blur-md bg-white/30">
        <b-container class="p-lg-5">
          <highchart v-if="showChart == true"
                     :options="chartOptions"
          />
          <div class="mt-5" v-if="tableLength != 0">
            <b-table
              small
              id="iterationsTable"
              :items="iterations"
              :fields="fields"
              :current-page="currentPage"
              :per-page="perPage"
              striped
              responsive="sm"
            >
              <template #cell(emotion)="data">
                {{
                  data.item.emotion.charAt(0).toUpperCase() +
                  data.item.emotion.slice(1)
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
    <b-modal ref="modal" hide-footer title="Frame Selected">
      <div class="text-center">
        <h3>{{frameOpened.emotionIteration}} - IA</h3>
        <h3 v-if="frameOpened.emotion.name !== ''">{{frameOpened.emotion.name}} - HL</h3>
        <hr>
        <img v-if="frameOpened.base64 !== ''" :src="frameOpened.base64" class="w-50" >
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
              value-field="name"
              text-field="name"
            >
            </b-select>
            <b-button class="float-right" type="submit" variant="dark">
              Ok
            </b-button>
          </div>
        </form>
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
      showChart: false,
      emotionsDataGraph: [],
      iterations: [],
      perPage: 10,
      currentPage: 1,
      yLabels: [],
      chartOptions: {
        chart: {
          type: "line",
          backgroundColor: 'rgba(0,0,0,0)'
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
          tickInterval: 43200 * 1000,
          labels: {
            format: "{value:%Y-%m-%d}",
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
        emotionClassified: "",
        humanLabelEmotions: []
      }
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

          this.$axios
            .$get("/api/frames/download/" + this.frameOpened.id)
            .then((imageBase64) => {
              this.frameOpened.base64 = "data:image/jpg;base64," + imageBase64
            });
          this.$axios
            .$get("/api/emotions/groups/" + this.frameOpened.emotionIteration)
            .then((emotions) => {
              this.frameOpened.humanLabelEmotions = emotions;
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
      this.frameOpened.emotionClassified = ""
      this.frameOpened.humanLabelEmotions = []

      this.$refs["modal"].hide();
    },
    async collectGraphData() {

      let graphData = [];
      this.chartOptions.series = []

      for (let i  = 0; i < this.yLabels.length ; i++) {
        graphData[i] = []
        this.chartOptions.series.push({
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
            graphData[this.yLabels.indexOf(r.emotion_predicted)].push({id: r.id, x: r.createDate, y: r.accuracy, state: this.showModal})
          }else {
            graphData[this.yLabels.indexOf(r.emotion_classified)].push({id: r.id, x: r.createDate, y: r.accuracy, state: this.showModal})
          }

          pointGraph = []
        });

        const _this = this
        this.chartOptions.plotOptions.series.point.events = {
          click () {
            _this.showModal(this)
          }
        }

        this.showChart = true;
      })
      .catch((error) => {
        this.$toast.info(error.response.data).goAway(3000);
      });
      return graphData;
    },
    getEmotions() {
      this.$axios.get("/api/emotions").then((response) => {
        let emotions = response.data;
        emotions.forEach((emotion) => {
          this.yLabels.push(emotion.name);
        });

        // TODO - Isto teria de estar aqui por causa do gráfico
        this.yLabels.push("positive")
        this.yLabels.push("negative")
        this.chartOptions.yAxis.categories = this.yLabels;
      });
    },
    getIterations() {
      this.$axios
        .$get("/api/iterations")
        .then((iterations) => {
          this.iterations = iterations;
          if (this.iterations != []) {
            this.collectGraphData();
            //this.chartOptions.series[0].data =

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
  created() {
    this.getIterations();
    this.getEmotions();
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
