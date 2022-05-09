<template>
  <div>
    <navbar />
    <h1>Iterations</h1>
    <highchart v-if="showChart == true"
      :options="chartOptions"
    />
    <div v-else class="w-75 mx-auto alert alert-info">No frames from the iterations were classified yet</div>
    <b-container>
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
      showChart: false,
      emotionsDataGraph: [],
      iterations: [],
      perPage: 10,
      currentPage: 1,
      yLabels: [],
      chartOptions: {
        chart: {
          type: "line",
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
          {
            name:  "Time",
            data: [],
            color: "#03045e",
            marker: {
                enabled: true,
                radius: 5
            },
          },
        ],
      },
    };
  },
  components: {
    navbar,
  },
  methods: {
    collectGraphData() {

      let graphData = [];
      let pointGraph = [];
      this.$axios
    .$get("/api/frames/graphData")
    .then((data) => {
      data.forEach(r => {
        pointGraph.push(r.fst)
        pointGraph.push(r.snd)
        graphData.push(pointGraph)
        pointGraph = []
      });
        let len = graphData.length
        let date = new Date(graphData[0][0]);
        let firstDate = date.getDate()+"/"+(date.getMonth()+1)+"/"+date.getFullYear()+" "+(date.getHours()-1)+":"+date.getMinutes();
        date = new Date(graphData[len-1][0]);
        let lastDate = date.getDate()+"/"+(date.getMonth()+1)+"/"+date.getFullYear()+" "+(date.getHours()-1)+":"+date.getMinutes();
        this.chartOptions.series[0].name = "From "+firstDate+" to "+lastDate+"";
        this.showChart = true;
        console.log(graphData)
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

        this.chartOptions.yAxis.categories = this.yLabels;
      });
    },
    getIterations() {
      this.$axios
        .$get("/api/iterations")
        .then((iterations) => {
          this.iterations = iterations;
          if (this.iterations != []) {
            this.chartOptions.series[0].data = this.collectGraphData();

          }
        })
        .catch(() => {
          this.$toast.info("No iterations found").goAway(3000);
        });
    },
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

};
</script>

<style>
</style>
