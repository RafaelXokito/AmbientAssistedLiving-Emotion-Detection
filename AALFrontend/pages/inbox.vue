<template>
  <div>
    <v-card
      v-if="notifications.length !== 0"
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
            v-for="notification in notifications"
            :key="notification.id"
            cols="12"
            style="cursor: pointer">
            <v-hover
              v-slot="{ hover }"
            >
              <v-card
                      class="row-pointer"
                      :elevation="hover ? 16 : 2"
                      :class="{ 'on-hover': hover }"
                      @click="markNotificationAsRead(notification)">

                <v-card-title class="text-h5">
                  <v-row>
                    <v-col cols="9" class="text-h5">
                      {{ notification.title }}
                    </v-col>
                    <v-col cols="3" class="text-right">
                      <h6>{{ notification.created_at }}</h6>
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
                        v-if="notification.notificationseen"
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
    <h3 v-else class="text-center">Não existem notificações</h3>
  </div>
</template>

<script>
import VueAuthImage from 'vue-auth-image'
import { mapState } from 'vuex';

export default {
  components: {VueAuthImage},
  middleware: ('auth'),
  computed: {
    ...mapState('notifications', ['notifications']) 
  },
  methods: {
    markNotificationAsRead(notification){
      this.$axios.$patch("/api/notifications/"+notification.id).then(({data}) => {
       const index = this.notifications.findIndex(item => item.id === data.id);
       this.$store.dispatch('notifications/updateNotification', index);
      })
    }
  },
}
</script>
