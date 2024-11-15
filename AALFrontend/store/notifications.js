  // store/notifications.js
  export const namespaced = true; // Add this line if not present

  export const state = () => ({
    notifications: []
  });

  export const mutations = {
    setNotifications(state, notifications) {
      state.notifications = notifications;
    },
    addNotification(state, notification) {
      const date = new Date(notification.created_at * 1000);
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      // Format the output
      notification.created_at = `${hours}:${minutes} ${day}/${month}/${year}`;
      state.notifications.unshift(notification); 
      this.$toast.info(notification.title + " às " + notification.created_at, {
        position: 'top-right'
      }).goAway(10000);
    },
    updateNotification(state, index){
      state.notifications[index].notificationseen = true;
    }
  };

  export const getters = {
    notifications(state) {
      return state.notifications;
    }
  };

  export const actions = {
    async fetchNotifications({ commit }) {
      const response = await this.$axios.$get("/api/notifications?is-short=yes");
      const notifications = response.data.map(notification => {
        const date = new Date(notification.created_at * 1000);
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        return {
          id: notification.id,
          title: notification.title,
          content: notification.content,
          notificationseen: notification.notificationseen,
          created_at: `${hours}:${minutes} ${day}/${month}/${year}`
        };
      });

      commit('setNotifications', notifications);
    },
    addNotification({ commit }, notification) {
      // Commit new notification from WebSocket
      commit('addNotification', notification);
    },
    updateNotification({ commit }, index) {
      commit('updateNotification', index);
    }
  };
  
