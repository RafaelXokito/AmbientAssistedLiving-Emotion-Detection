<template>
  <v-card>
    <v-card-title class="align-start">
      <span>{{ name }}</span>

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
            v-model="myPattern"
            active-class="border"
            color="red lighten-1"
          >
            <v-list-item
              v-for="(item, index) in statisticsMenu"
              :key="index"
              :value="index"
            >
              <v-list-item-title>{{ item.title }}</v-list-item-title>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-menu>

    </v-card-title>
    <v-card-text class="my-7">
      <div class="d-flex align-center">
        <h1 v-if="statisticsMenu[myPattern]" class="text-4xl font-weight-semibold">
          {{ "Número: " + number }}
        </h1>

        <div v-if="percentage" class="d-flex align-center mb-n3">
          <v-icon
            size="40"
            :color="state"
            :class="state === 'gray' ? 'px-2' : ''"
          >
            {{ state === 'green' ? icons.mdiMenuUp : state === 'red' ? icons.mdiMenuDown : icons.mdiMinus }}
          </v-icon>
          <span class="text-base font-weight-medium ms-n2" :class="state+'--text'">{{percentage}}%</span>
        </div>
      </div>
      <h3>{{statisticsMenu[myPattern].title + ": " + value}}</h3>
    </v-card-text>
    <v-card-text v-if="state !== ''">
      <!-- Chart -->
      <highchart
        v-if="showGraph"
        :options="graphOptions"
      />
      <v-btn
        block
        color="primary"
        class="mt-6"
        outlined
        @click="toggleGraph()"
      >
        Detalhes
      </v-btn>
    </v-card-text>
  </v-card>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import { mdiDotsVertical, mdiTrendingUp, mdiCurrencyUsd, mdiMenuUp, mdiMenuDown, mdiMinus } from '@mdi/js'
import { getCurrentInstance } from '@vue/composition-api'

export default {
  props: [
    'name',
    'number',
    'showPercentage',
    'percentage',
    'state',
    'value',
    'showGraph',
    'graphOptions',
    'pattern',
    'index'
  ],
  setup() {
    const ins = getCurrentInstance()?.proxy
    const $vuetify = ins && ins.$vuetify ? ins.$vuetify : null
    const customChartColor = $vuetify.theme.isDark ? '#3b3559' : '#f5f5f5'

    const chartOptions = {
      colors: [
        customChartColor,
        customChartColor,
        customChartColor,
        customChartColor,
        $vuetify.theme.currentTheme.primary,
        customChartColor,
        customChartColor,
      ],
      chart: {
        type: 'bar',
        toolbar: {
          show: false,
        },
        offsetX: -15,
      },
      plotOptions: {
        bar: {
          columnWidth: '40%',
          distributed: true,
          borderRadius: 8,
          startingShape: 'rounded',
          endingShape: 'rounded',
        },
      },
      dataLabels: {
        enabled: false,
      },
      legend: {
        show: false,
      },
      xaxis: {
        categories: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
        axisBorder: {
          show: false,
        },
        axisTicks: {
          show: false,
        },
        tickPlacement: 'on',
        labels: {
          show: false,
          style: {
            fontSize: '12px',
          },
        },
      },
      yaxis: {
        show: true,
        tickAmount: 4,
        labels: {
          offsetY: 3,
          formatter: value => `$${value}`,
        },
      },
      stroke: {
        width: [2, 2],
      },
      grid: {
        strokeDashArray: 12,
        padding: {
          right: 0,
        },
      },
    }

    const chartData = [
      {
        data: [40, 60, 50, 60, 75, 60, 50, 65],
      },
    ]

    return {
      chartOptions,
      chartData,

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
  data(){
    return {
      myPattern: null,
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
  mounted() {
    this.myPattern = this.statisticsMenu.findIndex( p => this.pattern === p.value)
    //console.log(this.statisticsMenu[this.myPattern].title)
  },
  watch: {
    myPattern (newVal, oldVal) {
      //console.log("Watch myPattern: ", newVal, oldVal, this.index, this.statisticsMenu[newVal].value)
      if (newVal !== oldVal && oldVal !== null)
        this.$emit('updatePattern', this.index, this.statisticsMenu[newVal].value)

    }
  },
  methods: {
    toggleGraph(){
      this.$emit('showGraph')
    }
  }
}
</script>
