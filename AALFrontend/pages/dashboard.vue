<template>
  <div>
    <navbar />
    <body class="text-white text-center p-3">
      <div class="row">
        <div :class="statistics.length > 0 ? 'col-md-6 col-sm-12' : 'col'" >
          <div class="p-lg-5">
            <h1 class="font-mono text-red-400">
              Bem vindo(a) {{ currentUser.name }}
            </h1>
            <img
              src="/DashboardImage.svg"
              class="rounded-circle mx-auto"
              width="500"
            />
          </div>
        </div>
        <div class="col-md-6 col-sm-12" v-if="statistics.length > 0">
          <div class="p-lg-5 h-100">
            <div class="rounded-md backdrop-blur-md bg-black/5 h-100">
              <h1 class="font-mono text-red-400">Statistics</h1>
              <div id="wrapper" class="max-w-xl px-4 py-4 mx-auto h-100">
                <div
                  class="
                    flex flex-col
                    justify-center
                    px-4
                    py-4
                    bg-white
                    border border-gray-300
                    rounded-md
                  "
                  v-for="(statistic, index) in statistics"
                  :class="index > 0 ? 'mt-2' : ''"
                >
                  <div>
                    <div class="row" >
                      <p
                        class="
                          flex
                          items-center
                          justify-start
                          text-md
                          col-9
                          text-gray-800
                        "
                      >
                        <span class="font-bold">{{ pattern + ": " + statistic.value }}</span>
                      </p>
                      <p
                        class="
                          flex
                          items-center
                          justify-end
                          text-md
                          col-3
                        "
                        :class="`text-${statistic.state}-500`"
                        v-if="statistic.showPercentage"
                      >
                        <span class="font-bold">{{Math.abs(statistic.percentage)}}%</span>

                        <svg v-if="statistic.state === 'green'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24"> <path class="heroicon-ui" d="M17 11a1 1 0 010 2H7a1 1 0 010-2h10z"/> </svg>
                        <svg v-else-if="statistic.state === 'red'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path class="heroicon-ui"  d="M20 9a1 1 0 012 0v8a1 1 0 01-1 1h-8a1 1 0 010-2h5.59L13 10.41l-3.3 3.3a1 1 0 01-1.4 0l-6-6a1 1 0 011.4-1.42L9 11.6l3.3-3.3a1 1 0 011.4 0l6.3 6.3V9z" /></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24"> <path class="heroicon-ui" d="M17 11a1 1 0 010 2H7a1 1 0 010-2h10z"/> </svg>

                      </p>
                    </div>
                    <p class="text-3xl font-semibold text-center text-gray-800">
                      {{ statistic.number }}
                    </p>
                    <p class="text-lg text-center text-gray-500">{{ statistic.name }}</p>
                    <p class="flex items-center justify-end text-md">
                      <button v-if="!statistic.showGraph" @click="statistic.showGraph = true" type="button" class="text-white bg-black/5 hover:bg-black/20 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full text-sm p-2.5 text-center inline-flex items-center mr-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                        <svg class="h-4 w-4 text-red-400"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                      </button>
                      <button v-else @click="statistic.showGraph = false" type="button" class="text-white bg-black/5 hover:bg-black/20 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full text-sm p-2.5 text-center inline-flex items-center mr-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                        <svg class="h-4 w-4 text-red-400"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <line x1="5" y1="12" x2="19" y2="12" /></svg>
                      </button>
                    </p>
                    <div class="mt-2">
                      <highchart
                        v-if="statistic.showGraph"
                        :options="statistic.graphOptions"
                      />
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <label class="font-mono text-red-600 ml-2">
                    Choose a date pattern for the graphs by:
                  </label>
                  <b-select class="w-50" v-model="pattern">
                    <option value="YEARMONTHDAY">Year-Month-Day</option>
                    <option value="YEARMONTH">Year-Month</option>
                    <option value="YEAR">Year</option>
                    <option value="MONTH">Month</option>
                    <option value="WEEKDAY">Weekday</option>
                    <option value="HOURS">Hour</option>
                  </b-select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </body>
    <footer>
      <a href="https://storyset.com/people">People illustrations by Storyset</a>
    </footer>
  </div>
</template>

<script>
import navbar from "~/components/utils/NavBar.vue";
export default {
  middleware: "auth",
  components: {
    navbar,
  },
  data() {
    return {
      months:[
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
      ],
      weekdays: [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
      ],
      pattern: 'HOURS',
      showIterationBarChart: false,
      generalChartOptions: {
        chart: {
          type: "column",
          height: 200,
          weight: null,
          marginTop: 15
        },
        title: {
          text: "",
        },
        yAxis: {
          title: {
            text: "",
          },
        },
        xAxis: {
          title: {
            text: "",
          },
          categories: [],
        },
        series: [],
        plotOptions: {
          column: {
            borderRadius: 5
          }
        },
        responsive: {
          rules: [{
            condition: {
              maxWidth: 500
            },
            chartOptions: {
              legend: {
                align: 'center',
                verticalAlign: 'bottom',
                layout: 'horizontal'
              },
              yAxis: {
                labels: {
                  align: 'left',
                  x: 0,
                  y: -5
                },
                title: {
                  text: null
                }
              },
              subtitle: {
                text: null
              },
              credits: {
                enabled: false
              }
            }
          }]
        }
      },
      iterationsBarChartOptions: {
        chart: {
          type: "line",
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
        series: [],
      },
      showClassificationsBarChart: false,
      classificationsBarChartOptions: {
        chart: {
          type: "column",
        },
        title: {
          text: "Classifications of Frames over time",
        },
        yAxis: {
          title: {
            text: "Nº Classified Frames",
          },
        },
        xAxis: {
          title: {
            text: "Time",
          },
          categories: [],
        },
        series: [],
      },
      statistics: []
    };
  },
  mounted() {
    this.loadStatistics()
  },
  methods: {
    loadStatistics(){
      this.statistics = []
      this.collectBarGraphDataIterations()
      this.collectBarGraphDataClassifications()
    },
    collectBarGraphDataIterations() {

      let graphOptions = {...this.generalChartOptions}
      graphOptions.series = [];
      graphOptions.xAxis.categories = [];

      let value = []
      this.$axios
        .$get("/api/iterations/graphData?pattern=" + this.pattern)
        .then((graphData) => {
          if (graphData != []) {
            for (let i = 0; i < graphData.length; i++) {
              value = [graphData[i][1]];
              if (this.pattern == "HOURS") {
                value = String(parseInt(value) + 1);
              } else if (this.pattern == "WEEKDAY") {
                value = this.weekdays[parseInt(value) - 1];
              }else if(this.pattern == "MONTH"){
                value = this.months[parseInt(value) - 1];
              }
              graphOptions.series.push({
                name: value,
                data: [graphData[i][0]],
              });
              graphOptions.xAxis.categories.push(value);
            }

          }

          let percentage = graphData.length > 1 ? ((graphData[graphData.length-1][0]/graphData[graphData.length-2][0])*100)-100 : 0
          let state = graphData.length > 1 ? percentage > 0 ? 'green' : percentage < 0 ? 'red' : 'gray' : ''

          this.statistics.push({
            name: 'Number of Iterations',
            number: graphData[graphData.length-1][0],
            showPercentage: graphData.length > 1,
            percentage: parseFloat(percentage).toFixed(2),
            state: state,
            value: value,
            showGraph: false,
            graphOptions: graphOptions
          })
        });
    },
    collectBarGraphDataClassifications() {

      let graphOptions = {...this.generalChartOptions}
      graphOptions.series = [];
      graphOptions.xAxis.categories = [];

      let value = []
      this.$axios
        .$get("/api/frames/graphData?pattern=" + this.pattern)
        .then((graphData) => {
          if (graphData != []) {
            for (let i = 0; i < graphData.length; i++) {
              value = [graphData[i][1]];
              if (this.pattern == "HOURS") {
                value = String(parseInt(value) + 1);
              } else if (this.pattern == "WEEKDAY") {
                value = this.weekdays[parseInt(value) - 1];
              }
              else if(this.pattern == "MONTH"){
                value = this.months[parseInt(value) - 1];
              }
              graphOptions.series.push({
                name: value,
                data: [graphData[i][0]],
              });
              graphOptions.xAxis.categories.push(value);
            }

            let percentage = graphData.length > 1 ? ((graphData[graphData.length-1][0]/graphData[graphData.length-2][0])*100)-100 : 0
            let state = graphData.length > 1 ? percentage > 0 ? 'green' : percentage < 0 ? 'red' : 'gray' : ''

            this.statistics.push({
              name: 'Number of Classifications',
              number: graphData[graphData.length-1][0],
              showPercentage: graphData.length > 1,
              percentage: parseFloat(percentage).toFixed(2),
              state: state,
              value: value,
              showGraph: false,
              graphOptions: graphOptions
            })
          }
        });
    },
  },
  computed: {
    currentUser() {
      return this.$auth.user;
    },
  },
  watch: {
    pattern(){
      this.loadStatistics()
    }
  }
};
</script>

<style>
header {
  font-family: var(--bs-body-font-family);
}
h1 {
  padding-top: 50px;
  font-size: xxx-large;
}
</style>
