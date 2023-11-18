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

import {
  mdiCurrencyUsd,
  mdiDotsVertical,
  mdiMenuUp, 
  mdiMenuDown, 
  mdiMinus
} from '@mdi/js'
import DashboardStatisticsCard from '@/components/dashboard/DashboardStatisticsCard.vue'
import DashboardCardNotifications from "~/components/dashboard/DashboardCardNotifications"

export default {
  components: {
    DashboardCardNotifications,
    DashboardStatisticsCard
  },
  middleware: "auth",
  data() {
    return {
      icons: {
        mdiDotsVertical,
        mdiCurrencyUsd,
        mdiMenuUp,
        mdiMenuDown,
        mdiMinus
      },
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
      showIterationBarChart: false,
      iterationsBarChartOptions: {
        chart: {
          type: "line",
        },
        title: {
          text: "Iterações ao longo do tempo",
        },
        yAxis: {
          title: {
            text: "Número de iterações",
          },
        },
        xAxis: {
          title: {
            text: "Tempo",
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
          text: "Classificação de imagens ao longo do tempo",
        },
        yAxis: {
          title: {
            text: "Número de imagens classificadas",
          },
        },
        xAxis: {
          title: {
            text: "Tempo",
          },
          categories: [],
        },
        series: [],
      },
      statistics: [],
      statisticsMenu: [
        { title: 'Ano-Mês-Dia', value: 'YEARMONTHDAY' },
        { title: 'Ano-Mês', value: 'YEARMONTH' },
        { title: 'Ano', value: 'YEAR' },
        { title: 'Mês', value: 'MONTH' },
        { title: 'Dia-de-semana', value: 'WEEKDAY' },
        { title: 'Horas', value: 'HOURS' }
      ],
    }
  },
  computed: {
    currentUser() {
      return this.$auth.user
    },
  },
  mounted() {
    this.loadStatistics()
    for (const k in this.statistics) { this.$watch('statistics.' + k, function (val, oldVal) { console.log(k, val, oldVal) }) }
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
    async loadStatistics(){
      this.statistics = []
      await this.collectBarGraphDataIterations(0)
      await this.collectBarGraphDataClassifications(1)
    },
    collectBarGraphDataIterations(index, statistic=null) {

      const graphOptionsIterations = JSON.parse(JSON.stringify({
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
          labels: {
            enabled: false // disable labels
          },
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
      }))

      const pattern = statistic !== null ? statistic.pattern[1] : 'HOURS'

      let value = []
      let number = []
      this.$axios
        .$get("/api/iterations/graphData?pattern=" + pattern)
        .then(graphData => {
          if (graphData !== []) {
            for (let i = 0; i < graphData.length; i++) {
              value = graphData[i].d
              number = graphData[i].c
              graphOptionsIterations.series.push({
                name: value,
                data: [number],
              })
            }

          }

          const percentage = graphData.length > 1 ? ((graphData[graphData.length-1].c/graphData[graphData.length-2].c)*100)-100 : 0
          const state = graphData.length > 1 ? percentage > 0 ? 'green' : percentage < 0 ? 'red' : 'gray' : ''

          if (graphData.length > 0) {
            if (statistic === null)
              this.statistics.push({
                name: 'Número total de iterações',
                number,
                showPercentage: graphData.length > 1,
                percentage: parseFloat(percentage).toFixed(2),
                state,
                value,
                pattern: [this.statisticsMenu.findIndex(e => e.value === pattern), pattern],
                showGraph: false,
                graphOptions: graphOptionsIterations,
                index
              })
            else {
              statistic.number = number
              statistic.showPercentage = graphData.length > 1
              statistic.state = state
              statistic.value = value
              statistic.graphOptions = graphOptionsIterations
              statistic.percentage = parseFloat(percentage).toFixed(2)
            }
          }
        })
    },
    collectBarGraphDataClassifications(index, statistic=null) {

      const graphOptionsClassifications = JSON.parse(JSON.stringify({
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
          labels: {
            enabled: false // disable labels
          },
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
      }))

      const pattern = statistic !== null ? statistic.pattern[1] : 'HOURS'

      let value = []
      let number = []
      this.$axios
        .$get("/api/frames/graphData?pattern=" + pattern)
        .then(graphData => {
          if (graphData !== []) {

            for (let i = 0; i < graphData.length; i++) {
              value = graphData[i].d
              number = graphData[i].c
              graphOptionsClassifications.series.push({
                name: value,
                data: [number],
              })
            }

            const percentage = graphData.length > 1 ? ((graphData[graphData.length - 1].c / graphData[graphData.length - 2].c) * 100) - 100 : 0
            const state = graphData.length > 1 ? percentage > 0 ? 'green' : percentage < 0 ? 'red' : 'gray' : ''

            if (graphData.length > 0) {
              if (statistic === null)
                this.statistics.push({
                  name: 'Número de classificações',
                  number,
                  showPercentage: graphData.length > 1,
                  percentage: parseFloat(percentage).toFixed(2),
                  state,
                  value,
                  pattern: [this.statisticsMenu.findIndex(e => e.value === pattern), pattern],
                  showGraph: false,
                  graphOptions: graphOptionsClassifications,
                  index
                })
              else {
                statistic.number = number
                statistic.showPercentage = graphData.length > 1
                statistic.state = state
                statistic.value = value
                statistic.graphOptions = graphOptionsClassifications
                statistic.percentage = parseFloat(percentage).toFixed(2)
              }
            }
          }
        })
    },
  },
}
</script>
