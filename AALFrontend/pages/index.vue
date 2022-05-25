<template>
  <v-row>
    <v-col
      cols="12"
      md="8"
    >
      <dashboard-statistics-card></dashboard-statistics-card>
    </v-col>
    <v-col
      v-for="(statistic) in statistics"
      :key="statistic.index"
      cols="12"
      sm="6"
      md="4"
    >
      <v-card>
        <v-card-title class="align-start">
          <span>{{ statistic.name }}</span>

          <v-spacer></v-spacer>
          <v-menu offset-y>
            <template #activator="{ on, attrs }">
              <v-btn
                icon
                small
                class="mt-n2 me-n3"
                v-bind="attrs"
                v-on="on"
              >
                <v-icon size="22">
                  {{ icons.mdiDotsVertical }}
                </v-icon>
              </v-btn>
            </template>
            <v-list>
              <v-list-item-group
                v-model="statistic.pattern[0]"
                active-class="border"
                color="red lighten-1"
              >
                <v-list-item
                  v-for="(item, index) in statisticsMenu"
                  :key="index"
                  :value="index"
                >
                  <v-list-item-title @click="updatePattern(statistic, index, item.value)">{{ item.title }}</v-list-item-title>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-menu>

        </v-card-title>
        <v-card-text class="my-7">
          <div class="d-flex align-center">
            <h1 class="text-4xl font-weight-semibold">
              {{ statistic.number }}
            </h1>

            <div v-if="statistic.percentage" class="d-flex align-center mb-n3">
              <v-icon
                size="40"
                :color="statistic.state"
                :class="statistic.state === 'gray' ? 'px-2' : ''"
              >
                {{ statistic.state === 'green' ? icons.mdiMenuUp : statistic.state === 'red' ? icons.mdiMenuDown : icons.mdiMinus }}
              </v-icon>
              <span class="text-base font-weight-medium ms-n2" :class="statistic.state+'--text'">{{statistic.percentage}}%</span>
            </div>
          </div>
          <h2>{{statisticsMenu[statistic.pattern[0]].title + ": " + statistic.value}}</h2>
        </v-card-text>
        <v-card-text v-if="statistic.state !== ''">
          <!-- Chart -->
          <highchart
            v-if="statistic.showGraph"
            :options="statistic.graphOptions"
          />
          <v-btn
            block
            color="primary"
            class="mt-6"
            outlined
            @click="statistic.showGraph = !statistic.showGraph"
          >
            Details
          </v-btn>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col
      cols="12"
      md="4"
    >
      <dashboard-card-notifications></dashboard-card-notifications>
    </v-col>
  </v-row>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import {
  mdiPoll,
  mdiLabelVariantOutline,
  mdiCurrencyUsd,
  mdiHelpCircleOutline,
  mdiDotsVertical,
  mdiTrendingUp, mdiMenuUp, mdiMenuDown, mdiMinus
} from '@mdi/js'
import StatisticsCardVertical from '@/components/statistics-card/StatisticsCardVertical.vue'

// demos
import DashboardCongratulationJohn from '@/components/dashboard/DashboardCongratulationJohn.vue'
import DashboardStatisticsCard from '@/components/dashboard/DashboardStatisticsCard.vue'
import DashboardCardTotalEarning from '@/components/dashboard/DashboardCardTotalEarning.vue'
import DashboardCardDepositAndWithdraw from '@/components/dashboard/DashboardCardDepositAndWithdraw.vue'
import DashboardCardSalesByCountries from '@/components/dashboard/DashboardCardSalesByCountries.vue'
import DashboardDatatable from '@/components/dashboard/DashboardDatatable.vue'
import DashboardCardNotifications from "~/components/dashboard/DashboardCardNotifications"

export default {
  middleware: "auth",
  components: {
    DashboardCardNotifications,
    StatisticsCardVertical,
    DashboardCongratulationJohn,
    DashboardStatisticsCard,
    DashboardCardTotalEarning,
    DashboardCardDepositAndWithdraw,
    DashboardCardSalesByCountries,
    DashboardDatatable,
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
      pattern: [],
      showIterationBarChart: false,
      generalChartOptions: {
        chart: {
          type: "column",
          height: 210,
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
      statistics: [],
      statisticsMenu: [
        { title: 'Year-Month-Day', value: 'YEARMONTHDAY' },
        { title: 'Year-Month', value: 'YEARMONTH' },
        { title: 'Year', value: 'YEAR' },
        { title: 'Month', value: 'MONTH' },
        { title: 'Weekday', value: 'WEEKDAY' },
        { title: 'Hours', value: 'HOURS' }
      ],
    }
  },
  setup() {
    const totalProfit = {
      statTitle: 'Total Profit',
      icon: mdiPoll,
      color: 'success',
      subtitle: 'Weekly Project',
      statistics: '$25.6k',
      change: '+42%',
    }

    const totalSales = {
      statTitle: 'Refunds',
      icon: mdiCurrencyUsd,
      color: 'secondary',
      subtitle: 'Past Month',
      statistics: '$78',
      change: '-15%',
    }

    // vertical card options
    const newProject = {
      statTitle: 'New Project',
      icon: mdiLabelVariantOutline,
      color: 'primary',
      subtitle: 'Yearly Project',
      statistics: '862',
      change: '-18%',
    }

    const salesQueries = {
      statTitle: 'Sales Quries',
      icon: mdiHelpCircleOutline,
      color: 'warning',
      subtitle: 'Last week',
      statistics: '15',
      change: '-18%',
    }

    return {
      totalProfit,
      totalSales,
      newProject,
      salesQueries,

      icons: {
        mdiDotsVertical,
        mdiTrendingUp,
        mdiCurrencyUsd,
        mdiMenuUp,
        mdiMenuDown,
        mdiMinus
      },
    }
  },
  mounted() {
    this.loadStatistics()
    for (const k in this.statistics) { this.$watch('statistics.' + k, function (val, oldVal) { console.log(k, val, oldVal) }) }
  },
  computed: {
    currentUser() {
      return this.$auth.user
    },
  },
  methods: {
    updatePattern(statistic, index, value){

      statistic.pattern[0] = index
      statistic.pattern[1] = value

      switch (statistic.index) {
        case 0:
          this.collectBarGraphDataIterations(0,statistic)
          break
        case 1:
          this.collectBarGraphDataClassifications(1,statistic)
          break
      }

    },
    loadStatistics(){
      this.statistics = []
      this.collectBarGraphDataIterations(0)
      this.collectBarGraphDataClassifications(1)
    },
    collectBarGraphDataIterations(index, statistic=null) {

      const graphOptions = {...this.generalChartOptions}
      graphOptions.series = []
      graphOptions.xAxis.categories = []

      const pattern = statistic !== null ? statistic.pattern[1] : 'HOURS'

      let value = []
      this.$axios
        .$get("/api/iterations/graphData?pattern=" + pattern)
        .then(graphData => {
          if (graphData !== []) {
            for (let i = 0; i < graphData.length; i++) {
              value = [graphData[i][1]]
              if (pattern === "HOURS") {
                value = String(parseInt(value) + 1)
              } else if (pattern === "WEEKDAY") {
                value = this.weekdays[parseInt(value) - 1]
              }else if(pattern === "MONTH"){
                value = this.months[parseInt(value) - 1]
              }
              graphOptions.series.push({
                name: value,
                data: [graphData[i][0]],
              })
              graphOptions.xAxis.categories.push(value)
            }

          }

          const percentage = graphData.length > 1 ? ((graphData[graphData.length-1][0]/graphData[graphData.length-2][0])*100)-100 : 0
          const state = graphData.length > 1 ? percentage > 0 ? 'green' : percentage < 0 ? 'red' : 'gray' : ''

          if (graphData.length > 0) {
            if (statistic === null)
              this.statistics.push({
                name: 'Number of Iterations',
                number: graphData[graphData.length-1][0],
                showPercentage: graphData.length > 1,
                percentage: parseFloat(percentage).toFixed(2),
                state,
                value,
                pattern: [this.statisticsMenu.findIndex(e => e.value === pattern), pattern],
                showGraph: false,
                graphOptions,
                index
              })
            else {
              statistic.number = graphData[graphData.length-1][0]
              statistic.showPercentage = graphData.length > 1
              statistic.state = state
              statistic.value = value
              statistic.graphOptions = graphOptions
              statistic.percentage = parseFloat(percentage).toFixed(2)
            }
          }
        })
    },
    collectBarGraphDataClassifications(index, statistic=null) {

      const graphOptions = {...this.generalChartOptions}
      graphOptions.series = []
      graphOptions.xAxis.categories = []

      const pattern = statistic !== null ? statistic.pattern[1] : 'HOURS'

      let value = []
      this.$axios
        .$get("/api/frames/graphData?pattern=" + pattern)
        .then(graphData => {
          if (graphData !== []) {
            for (let i = 0; i < graphData.length; i++) {
              value = [graphData[i][1]]
              if (pattern === "HOURS") {
                value = String(parseInt(value) + 1)
              } else if (pattern === "WEEKDAY") {
                value = this.weekdays[parseInt(value) - 1]
              } else if (pattern === "MONTH") {
                value = this.months[parseInt(value) - 1]
              }
              graphOptions.series.push({
                name: value,
                data: [graphData[i][0]],
              })
              graphOptions.xAxis.categories.push(value)
            }

            const percentage = graphData.length > 1 ? ((graphData[graphData.length - 1][0] / graphData[graphData.length - 2][0]) * 100) - 100 : 0
            const state = graphData.length > 1 ? percentage > 0 ? 'green' : percentage < 0 ? 'red' : 'gray' : ''

            if (graphData.length > 0) {
              if (statistic === null)
                this.statistics.push({
                  name: 'Number of Classifications',
                  number: graphData[graphData.length - 1][0],
                  showPercentage: graphData.length > 1,
                  percentage: parseFloat(percentage).toFixed(2),
                  state,
                  value,
                  pattern: [this.statisticsMenu.findIndex(e => e.value === pattern), pattern],
                  showGraph: false,
                  graphOptions,
                  index
                })
              else {
                statistic.number = graphData[graphData.length-1][0]
                statistic.showPercentage = graphData.length > 1
                statistic.state = state
                statistic.value = value
                statistic.graphOptions = graphOptions
                statistic.percentage = parseFloat(percentage).toFixed(2)
              }
            }
          }
        })
    },
  },
}
</script>
