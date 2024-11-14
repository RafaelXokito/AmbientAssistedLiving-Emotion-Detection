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
              <v-list-item-action-text v-text="data.created_at"></v-list-item-action-text>
              <v-icon
              v-if="data.notificationseen"
              color="blue lighten-1">
              mdi-check-all
            </v-icon>
            <v-icon
              v-else
              color="blue lighten-1">
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
  methods: {
    getNotifications() {
      this.$axios
        .$get("/api/notifications?is-short=yes")
        .then( notifications => {
          notifications.data.forEach(notification => {
            this.notifications.push({
              id: notification.id,
              title: notification.title,
              content: notification.content,
              notificationseen: notification.notificationseen,
              created_at: this.timeSinceFromEpochTime(notification.created_at)
            })
          })
        })
        .catch(() => {
          this.$toast.info("No notifications found").goAway(3000)
        })
    },
    timeSinceFromEpochTime(timestamp) {
      const date = new Date(timestamp * 1000);
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${hours}:${minutes} ${day}/${month}/${year}`;
    },
    formatDate(dateString) {
        // Parse the date string
        const date = new Date(dateString.replace(" ", "T"));

        // Extract components
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-based
        const year = date.getFullYear();
        console.log(`${hours}:${minutes} ${day}/${month}/${year}`);
        // Format the output
        return `${hours}:${minutes} ${day}/${month}/${year}`;
    }
  },
  created(){
    this.getNotifications()
    this.socket = this.$nuxtSocket({ persist: 'mySocket'})
    this.socket.on('newNotificationMessage', data => {
      console.log(data)
      this.notifications.unshift({
        id: data.id,
        title: data.title,
        content: data.content,
        notificationseen: data.notificationseen,
        created_at: this.formatDate(data.created_at)
      })
       if (this.notifications.length > 5) {
          this.notifications.pop()
        }
      })
  }
}
</script>
