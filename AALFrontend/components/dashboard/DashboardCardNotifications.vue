<template>
  <v-card v-if="notifications.length > 0">
    <v-card-title class="align-start">
      <span>Notificações</span>

      <v-spacer></v-spacer>
      <span class="text-xs text--disabled cursor-pointer" @click="$router.push('/inbox')">Ver todas</span>
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

export default {
  data(){
    return {
      notifications: []
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
        return Math.floor(interval) + " anos"
      }
      interval = seconds / 2592000
      if (interval > 1) {
        return Math.floor(interval) + " meses"
      }
      interval = seconds / 86400
      if (interval > 1) {
        return Math.floor(interval) + " dias"
      }
      interval = seconds / 3600
      if (interval > 1) {
        return Math.floor(interval) + " horas"
      }
      interval = seconds / 60
      if (interval > 1) {
        return Math.floor(interval) + " minutos"
      }

      return Math.floor(seconds) + " segundos"
    }
  }
}
</script>
