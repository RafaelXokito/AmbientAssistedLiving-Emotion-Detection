<template>
  <v-card>
    <v-card-title class="align-start">
      <span class="font-weight-semibold">Estatísticas gerais</span>
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
import { mdiBellRing, mdiCog, mdiAccountArrowUpOutline, mdiTimerSync, mdiContentSaveCog, mdiMessageText, mdiMessageTextClock, mdiBellCog, mdiCogRefreshOutline, mdiTrendingUp, mdiLabelOutline, mdiChatQuestion, mdiCalendarRange } from '@mdi/js'

export default {
  setup() {
    const resolveStatisticsIconVariation = data => {
      if (data === 'Nº de notificações da última semana') return { icon: mdiBellRing, color: 'warning' }
      if (data === 'Nº de configurações para notificações') return { icon: mdiBellCog, color: 'info' }     
      if (data === 'Nº de iterações da última semana') return { icon: mdiTimerSync, color: 'warning' }
      if (data === 'Data da última iteração') return { icon: mdiCalendarRange, color: 'info' }
      if (data === 'Nº de mecanismos de regulações de emoções') return { icon: mdiCog, color: 'primary' }
      if (data === 'Nº de conteúdos para regulação de emoções') return { icon: mdiContentSaveCog, color: 'primary' }
      if (data === 'Nº de questionários iniciados') return { icon: mdiChatQuestion, color: 'primary' }
      if (data === 'Nº de questionários completados') return { icon: mdiChatQuestion, color: 'success' }   
      if (data === 'Nº de mensagens da última semana') return { icon: mdiMessageText, color: 'warning' }
      if (data === 'Data da última mensagem') return { icon: mdiMessageTextClock, color: 'info' }

      return { icon: mdiAccountArrowUpOutline, color: 'success' }
    }

    return {
      resolveStatisticsIconVariation,

      // icons
      icons: {
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
          this.$toast.info("Não existem estatísticas").goAway(3000)
        })
    },
    timeSince(date) {
      const seconds = Math.floor((new Date() - new Date(date*1000)) / 1000)

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
