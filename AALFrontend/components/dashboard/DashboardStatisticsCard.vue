<template>
  <v-card>
    <v-card-title class="align-start">
      <span class="font-weight-semibold">Statistics Card</span>
    </v-card-title>

    <v-card-text>
      <v-row>
        <v-col
          v-for="data in statisticsData"
          :key="data.name"
          cols="6"
          md="3"
          class="d-flex align-center"
        >
          <v-avatar
            size="44"
            :color="resolveStatisticsIconVariation (data.name).color"
            rounded
            class="elevation-1"
          >
            <v-icon
              dark
              color="white"
              size="30"
            >
              {{ resolveStatisticsIconVariation (data.name).icon }}
            </v-icon>
          </v-avatar>
          <div class="ms-3">
            <p class="text-xs mb-0">
              {{ data.name }}
            </p>
            <h3 class="text-xl font-weight-semibold" v-if="data.name.toLowerCase().includes('time')">
              {{ timeSince(data.value) }}
            </h3>
            <h3 class="text-xl font-weight-semibold" v-else>
              {{ firstCapitalLetter(data.value) }}
            </h3>
          </div>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import { mdiAccountArrowUpOutline, mdiCogRefreshOutline, mdiTrendingUp, mdiDotsVertical, mdiLabelOutline } from '@mdi/js'

export default {
  setup() {
    const resolveStatisticsIconVariation = data => {
      if (data === 'Total of notifications') return { icon: mdiTrendingUp, color: 'primary' }
      if (data === 'Emotion with the most notifications') return { icon: mdiAccountArrowUpOutline, color: 'success' }
      if (data === 'Last Iteration Time') return { icon: mdiLabelOutline, color: 'warning' }
      if (data === 'Emotion with the least notifications configured') return { icon: mdiCogRefreshOutline, color: 'info' }

      return { icon: mdiAccountArrowUpOutline, color: 'success' }
    }

    return {
      resolveStatisticsIconVariation,

      // icons
      icons: {
        mdiDotsVertical,
        mdiTrendingUp,
        mdiAccountArrowUpOutline,
        mdiLabelOutline,
        mdiCogRefreshOutline,
      },
    }
  },
  data(){
    return {
      statisticsData: []
    }
  },
  created() {
    this.getStatistics()
  },
  methods: {
    getStatistics(){
      this.$axios
        .$get("/api/statistics")
        .then( statistics => {
          this.statisticsData = statistics
        })
        .catch(() => {
          this.$toast.info("No statistics found").goAway(3000)
        })
    },
    timeSince(date) {
      const seconds = Math.floor((new Date() - date) / 1000)

      let interval = seconds / 31536000

      if (interval > 1) {
        return Math.floor(interval) + " years"
      }
      interval = seconds / 2592000
      if (interval > 1) {
        return Math.floor(interval) + " months"
      }
      interval = seconds / 86400
      if (interval > 1) {
        return Math.floor(interval) + " days"
      }
      interval = seconds / 3600
      if (interval > 1) {
        return Math.floor(interval) + " hours"
      }
      interval = seconds / 60
      if (interval > 1) {
        return Math.floor(interval) + " minutes"
      }

      return Math.floor(seconds) + " seconds"
    },
    firstCapitalLetter(str = "") {
      return str.toString().charAt(0).toUpperCase() + str.toString().slice(1)
    },
  }
}
</script>
