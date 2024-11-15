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
import { mapState } from 'vuex';
export default {
  computed: {
    ...mapState('notifications', ['notifications']) 
  }
}
</script>
