<template>
  <v-card v-if="notifications.length > 0">
    <v-card-title class="align-start">
      <span>Notifications</span>

      <v-spacer></v-spacer>
      <span class="text-xs text--disabled cursor-pointer" @click="$router.push('/inbox')">View All</span>
    </v-card-title>

    <v-card-text>
      <v-list class="pb-0">
        <v-list-item
          v-for="(data,index) in notifications"
          :key="data.id"
          :class="`align-center px-0 ${index > 0 ? 'mt-4':''}`"
        >
            <v-list-item-content>
              <v-list-item-title v-text="data.title"></v-list-item-title>


              <v-list-item-subtitle v-text="data.content"></v-list-item-subtitle>
            </v-list-item-content>

            <v-list-item-action>
              <v-list-item-action-text v-text="timeSince(data.created_at)"></v-list-item-action-text>

              <v-icon
                v-if="data.notificationSeen"
                color="blue lighten-1"
              >
                mdi-check-all
              </v-icon>

              <v-icon
                v-else
                color="blue lighten-1"
              >
                mdi-check
              </v-icon>
            </v-list-item-action>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script>
import { mdiDotsVertical, mdiChevronUp, mdiChevronDown } from '@mdi/js'

export default {
  data(){
    return {
      notifications: []
    }
  },
  setup() {
    const salesByCountries = [
      {
        abbr: 'US',
        amount: '$8,656k',
        country: 'United states of america',
        change: '+25.8%',
        sales: '894k',
        color: 'success',
      },
      {
        abbr: 'UK',
        amount: '$2,415k',
        country: 'United kingdom',
        change: '-6.2%',
        sales: '645k',
        color: 'error',
      },
      {
        abbr: 'IN',
        amount: '$865k',
        country: 'India',
        change: '+12.4%',
        sales: '148k',
        color: 'warning',
      },
      {
        abbr: 'JA',
        amount: '$745k',
        country: 'Japan',
        change: '-11.9%',
        sales: '86k',
        color: 'secondary',
      },
      {
        abbr: 'KO',
        amount: '$45k',
        country: 'Korea',
        change: '+16.2%',
        sales: '42k',
        color: 'error',
      },
    ]

    return {
      salesByCountries,
      icons: {
        mdiDotsVertical,
        mdiChevronUp,
        mdiChevronDown,
      },
    }
  },
  created(){
    this.getNotifications()
    this.socket = this.$nuxtSocket({ persist: 'mySocket'})
    this.socket.on('newNotificationMessage', data=> {
      console.log(data)
      const aux = data.data.split(';')
      this.notifications.unshift({
        id: aux[aux.length-5],
        title: aux[aux.length-4],
        content: aux[aux.length-3],
        notificationSeen: aux[aux.length-2] === 'true',
        created_at: aux[aux.length-1]
      })
       if (this.notifications.length > 5) {
          this.notifications.pop()
        }
      })
  },
  methods: {
    getNotifications() {
      this.$axios
        .$get("/api/notifications?is-short=yes")
        .then( notifications => {
          this.notifications = notifications.data
        })
        .catch(() => {
          this.$toast.info("No notifications found").goAway(3000)
        })
    },
    timeSince(date) {
      const seconds = Math.floor((new Date().getTime() - new Date(date*1000).getTime()) / 1000)
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
    }
  }
}
</script>
