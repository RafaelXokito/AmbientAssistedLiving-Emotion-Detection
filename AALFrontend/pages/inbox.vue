<template>
  <div>
    <v-card
      v-if="notifications.length"
      class="mx-auto"
      max-width="1000"
    >
      <v-toolbar
        color="red"
        elevation="10"
        rounded
        dense
        dark
        class="pb-3"
      >
        <v-spacer></v-spacer>
        <v-toolbar-title class="white--text">Notifications</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-toolbar>
      <v-container>
        <v-row>
          <v-col
            cols="12"
            v-for="notification in notifications"
            :key="notification.id"
            style="cursor: pointer">
            <v-hover
              v-slot="{ hover }"
            >
              <v-card
                      class="row-pointer"
                      :elevation="hover ? 16 : 2"
                      :class="{ 'on-hover': hover }"
                      @click="openNotification(notification)">
                <v-card-title class="text-h5">
                  <v-row>
                    <v-col cols="9" class="text-h5">
                      {{ notification.title }}
                    </v-col>
                    <v-col cols="3" class="text-right">
                      <h6>{{ timeSince(notification.created_at) }}</h6>
                    </v-col>
                  </v-row>
                </v-card-title>
                <v-card-subtitle>
                  <v-row>
                    <v-col cols="11" class="text-truncate">
                      {{ notification.content }}
                    </v-col>
                    <v-col cols="1" class="text-right">
                      <v-icon
                        v-if="notification.notificationSeen"
                        color="blue lighten-1">
                        mdi-check-all
                      </v-icon>
                      <v-icon
                        v-else
                        color="blue lighten-1">
                        mdi-check
                      </v-icon>
                    </v-col>
                  </v-row>
                </v-card-subtitle>
              </v-card>
            </v-hover>
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <h2 v-else class="text-center">There's no notifications to see</h2>
    <v-dialog
      v-model="showNotification"
      max-width="600px"
    >
      <v-card>
        <v-card-title class="text-h5">
          {{ this.notification.title }}
        </v-card-title>
        <v-card-text>{{ this.notification.content }}</v-card-text>
        <v-card-actions>
          <v-btn
            color="primary"
            text
            @click="closeNotification"
          >
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  components: {},
  middleware: ('auth'),
  data(){
    return {
      notifications: [],
      showNotification: false,
      notification: {
        title: '',
        content: '',
      },
      deafultNotification: {
        title: '',
        content: '',
      }
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
    openNotification(notification){
      this.notification = notification
      this.$axios
        .$patch("/api/notifications/"+notification.id)
        .then( () => {
          notification.notificationSeen = true
        })
        .catch(() => {
          this.$toast.info("Notification not found").goAway(3000)
        })
      this.showNotification = true
    },
    closeNotification(){
      this.showNotification = false
      this.notification = this.deafultNotification
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
    },
  },
}
</script>
