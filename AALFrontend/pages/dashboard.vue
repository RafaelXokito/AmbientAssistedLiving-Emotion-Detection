<template>
  <div>
    <navbar />
    <body class="text-white text-center">
      <div class="row">
        <div class="col-6">
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
        <div class="col-6">
          <div class="p-lg-5 h-100">
            <div class="rounded-md backdrop-blur-md bg-black/5 h-100">
              <h1 class="font-mono text-red-400">Statistics</h1>
              <div id="wrapper" class="max-w-xl px-4 py-4 mx-auto h-100">
                <div
                  id="jh-stats-positive"
                  class="
                    flex flex-col
                    justify-center
                    px-4
                    py-4
                    bg-white
                    border border-gray-300
                    rounded-md
                  "
                >
                  <div>
                    <div>
                      <p
                        class="
                          flex
                          items-center
                          justify-end
                          text-green-500 text-md
                        "
                      >
                        <span class="font-bold">6%</span>
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="w-5 h-5 fill-current"
                          viewBox="0 0 24 24"
                        >
                          <path
                            class="heroicon-ui"
                            d="M20 15a1 1 0 002 0V7a1 1 0 00-1-1h-8a1 1 0 000 2h5.59L13 13.59l-3.3-3.3a1 1 0 00-1.4 0l-6 6a1 1 0 001.4 1.42L9 12.4l3.3 3.3a1 1 0 001.4 0L20 9.4V15z"
                          />
                        </svg>
                      </p>
                    </div>
                    <p class="text-3xl font-semibold text-center text-gray-800">
                      43
                    </p>
                    <p class="text-lg text-center text-gray-500">New Tickets</p>
                  </div>
                </div>

                <div
                  id="jh-stats-negative"
                  class="
                    flex flex-col
                    justify-center
                    px-4
                    py-4
                    mt-4
                    bg-white
                    border border-gray-300
                    rounded-md
                    sm:mt-0
                  "
                >
                  <div>
                    <div>
                      <p
                        class="
                          flex
                          items-center
                          justify-end
                          text-red-500 text-md
                        "
                      >
                        <span class="font-bold">6%</span>
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="w-5 h-5 fill-current"
                          viewBox="0 0 24 24"
                        >
                          <path
                            class="heroicon-ui"
                            d="M20 9a1 1 0 012 0v8a1 1 0 01-1 1h-8a1 1 0 010-2h5.59L13 10.41l-3.3 3.3a1 1 0 01-1.4 0l-6-6a1 1 0 011.4-1.42L9 11.6l3.3-3.3a1 1 0 011.4 0l6.3 6.3V9z"
                          />
                        </svg>
                      </p>
                    </div>
                    <p class="text-3xl font-semibold text-center text-gray-800">
                      43
                    </p>
                    <p class="text-lg text-center text-gray-500">New Tickets</p>
                  </div>
                </div>

                <div
                  id="jh-stats-negative"
                  class="
                    flex flex-col
                    justify-center
                    px-4
                    py-4
                    mt-4
                    bg-white
                    border border-gray-300
                    rounded-md
                    sm:mt-0
                  "
                >
                  <div>
                    <div>
                      <p
                        class="
                          flex
                          items-center
                          justify-end
                          text-red-500 text-md
                        "
                      >
                        <span class="font-bold">6%</span>
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="w-5 h-5 fill-current"
                          viewBox="0 0 24 24"
                        >
                          <path
                            class="heroicon-ui"
                            d="M20 9a1 1 0 012 0v8a1 1 0 01-1 1h-8a1 1 0 010-2h5.59L13 10.41l-3.3 3.3a1 1 0 01-1.4 0l-6-6a1 1 0 011.4-1.42L9 11.6l3.3-3.3a1 1 0 011.4 0l6.3 6.3V9z"
                          />
                        </svg>
                      </p>
                    </div>
                    <p class="text-3xl font-semibold text-center text-gray-800">
                      43
                    </p>
                    <p class="text-lg text-center text-gray-500">New Tickets</p>
                  </div>
                </div>

                <div
                  id="jh-stats-neutral"
                  class="
                    flex flex-col
                    justify-center
                    px-4
                    py-4
                    mt-4
                    bg-white
                    border border-gray-300
                    rounded-md
                    sm:mt-0
                  "
                >
                  <div>
                    <div>
                      <p
                        class="
                          flex
                          items-center
                          justify-end
                          text-gray-500 text-md
                        "
                      >
                        <span class="font-bold">0%</span>
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="w-5 h-5 fill-current"
                          viewBox="0 0 24 24"
                        >
                          <path
                            class="heroicon-ui"
                            d="M17 11a1 1 0 010 2H7a1 1 0 010-2h10z"
                          />
                        </svg>
                      </p>
                    </div>
                    <p class="text-3xl font-semibold text-center text-gray-800">
                      43
                    </p>
                    <p class="text-lg text-center text-gray-500">New Tickets</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="h-100">
        <h1 class="font-mono text-red-600">
         Nº of Iterations by time
        </h1>
        <label class="font-mono text-red-600 ml-2">
          Choose a pattern for the graph to appear
        </label>
        <b-select class="w-50" v-model="pattern">
          <option value="YEARMONTHDAY">YEAR-MONTH-DAY</option>
          <option value="YEARMONTH">YEAR-MONTH</option>
          <option value="YEAR">YEAR</option>
          <option value="MONTH">MONTH</option>
          <option value="WEEKDAY">WEEKDAY</option>
          <option value="HOURS">HOURS</option>
        </b-select>
        <b-button
          variant="outline-danger"
          class="mt-2 mb-2 mr-2"
          v-on:click="showGraph()"
        >
          Show Graph
        </b-button>
        <highchart
          v-if="showIterationBarChart == true"
          :options="iterationsBarChartOptions"
        />
        <highchart
          v-if="showClassificationsBarChart == true"
          :options="classificationsBarChartOptions"
        />
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
      pattern: null,
      showIterationBarChart: false,
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
    };
  },
  methods: {
    showGraph() {
      if (this.pattern == null) {
        this.$toast.info("Choose a pattern first!").goAway(3000);
        return;
      }
      this.collectBarGraphDataIterations();
      this.collectBarGraphDataClassifications();
    },
    collectBarGraphDataIterations() {
      this.iterationsBarChartOptions.series = [];
      this.iterationsBarChartOptions.xAxis.categories = [];
      this.$axios
        .$get("/api/iterations/graphData?pattern=" + this.pattern)
        .then((graphData) => {
          if (graphData != []) {
            for (let i = 0; i < graphData.length; i++) {
              let value = [graphData[i][1]];
              if (this.pattern == "HOURS") {
                value = String(parseInt(value) + 1);
              } else if (this.pattern == "WEEKDAY") {
                value = this.weekdays[parseInt(value) - 1];
              }else if(this.pattern == "MONTH"){
                value = this.months[parseInt(value) - 1];
              }
              this.iterationsBarChartOptions.series.push({
                name: value,
                data: [graphData[i][0]],
              });
              this.iterationsBarChartOptions.xAxis.categories.push(value);
            }

            this.showIterationBarChart = true;
          }
        });
    },
    collectBarGraphDataClassifications() {
      this.classificationsBarChartOptions.series = [];
      this.classificationsBarChartOptions.xAxis.categories = [];
      this.$axios
        .$get("/api/frames/graphData?pattern=" + this.pattern)
        .then((graphData) => {
          if (graphData != []) {
            for (let i = 0; i < graphData.length; i++) {
              let value = [graphData[i][1]];
              if (this.pattern == "HOURS") {
                value = String(parseInt(value) + 1);
              } else if (this.pattern == "WEEKDAY") {
                value = this.weekdays[parseInt(value) - 1];
              }
              else if(this.pattern == "MONTH"){
                value = this.months[parseInt(value) - 1];
              }
              this.classificationsBarChartOptions.series.push({
                name: value,
                data: [graphData[i][0]],
              });
              this.classificationsBarChartOptions.xAxis.categories.push(value);
            }

            this.showClassificationsBarChart = true;
          }
        });
    },
  },
  computed: {
    currentUser() {
      return this.$auth.user;
    },
  },
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
