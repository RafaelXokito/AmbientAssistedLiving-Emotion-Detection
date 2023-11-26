<template>
  <v-card class="mx-auto">
    <v-card-title>
      <h3>
        Definição de notificações
      </h3>
    </v-card-title>
    <v-data-table :headers="headers" :items="emotions" :loading="!(emotions.length > 0)" sort-by="display_name"
      class="elevation-1">
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="createItem(item)">
          mdi-plus
        </v-icon>
        <v-icon v-if="item.notificationsSettings.length > 0" small @click="showItem(item)">
          mdi-expand-all
        </v-icon>
      </template>
      <template v-slot:no-data> Ainda não existem definições de notificações registadas </template>
    </v-data-table>
    <v-divider></v-divider>
    <v-card v-if="showEmotionsNotificationTable && emotionsNotifications.length > 0" class="mx-auto">
      <v-card-title>
        <h3>
          Definições de notificações para a emoção: {{ currentEmotion.display_name }}
        </h3>
      </v-card-title>
      <v-data-table :headers="headersNotificationsSettings" :items="emotionsNotifications" sort-by="id"
        class="elevation-1">
        <template v-slot:item.actions="{ item }">
          <v-icon small @click="deleteItem(item)">
            mdi-delete
          </v-icon>
        </template>
      </v-data-table>
      <template v-slot:no-data> Ainda não existem definições de notificações para a emoção: {{ currentEmotion.display_name }} registadas </template>
    </v-card>

    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="text-h5">Configurar definições de notificações para a emoção: {{ currentEmotion.display_name }}</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field v-model="newItem.accuracylimit" label="Limite precisão (%)" type="number"
                  required></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="newItem.duration" label="duração (seg)" type="number" required></v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="close">
            Cancelar
          </v-btn>
          <v-btn color="blue darken-1" text @click="save">
            Guardar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Tem a certeza que quer apagar este item?</v-card-title>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeDelete">Não</v-btn>
          <v-btn color="blue darken-1" text @click="deleteItemConfirm">Sim</v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script>
export default {
  middleware: ('auth', 'client'),
  data() {
    return {
      showEmotionsNotificationTable: false,
      dialog: false,
      dialogDelete: false,
      headers: [
        { text: 'Nome', value: 'display_name' },
        { text: 'Grupo', value: 'display_group' },
        { text: 'Ações', value: 'actions', sortable: false },
      ],
      headersNotificationsSettings: [
        { text: 'Id', value: 'id' },
        { text: 'Duração', value: 'duration' },
        { text: 'Precisão', value: 'accuracylimit' },
        { text: 'Data de criação', value: 'created_at' },
        { text: 'Ações', value: 'actions', sortable: false },
      ],
      emotions: [],
      emotionsNotification: [],
      newDeleteItem: null,
      newItem: {
        id: -1,
        name: '',
        accuracylimit: 0,
        duration: 0,
      },
      emotionsNotifications: [],
      currentEmotion: {}
    }
  },
  watch: {
    dialog(val) {
      val || this.close()
    },
    dialogDelete(val) {
      val || this.closeDelete()
    },
  },
  created() {
    this.initialize()
  },
  methods: {
    async initialize() {
      await this.$axios.get("/api/emotions").then(response => {
        const emotions = response.data.data
        emotions.forEach(emotion => {
          if (emotion.name !== 'invalid')
            this.emotions.push({
              name: emotion.name,
              display_name: emotion.display_name,
              display_group: emotion.display_group,
              notificationsSettings: emotion.notificationsSettings
            })
        });
      })
    },
    showItem(item) {
      this.currentEmotion = item;
      this.showEmotionsNotificationTable = true;
      this.emotionsNotifications = item.notificationsSettings;
    },
    createItem(item) {
      this.currentEmotion = item;
      this.newItem = Object.assign({}, {
        emotion_name: item.name,
        display_name: item.display_name,
        accuracylimit: null,
        duration: null,
      })
      this.dialog = true
    },

    deleteItem(item) {
      this.newDeleteItem = item;
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      this.$axios
        .$delete("/api/emotionsNotification/" + this.newDeleteItem.id)
        .then(() => {
          this.$toast.success('Notificação '+this.newDeleteItem.id+' apagada para a emoção: ' + this.currentEmotion.display_name).goAway(3000)
          this.$axios.get("/api/emotions/"+this.newDeleteItem.emotion_name).then(response => {
            const notificationsSettings = response.data.data.notificationsSettings
            var index = this.emotions.findIndex(x => x.name === this.currentEmotion.name)
            this.emotions[index].notificationsSettings = notificationsSettings;
            this.emotionsNotifications = notificationsSettings;
            this.closeDelete()

          });
        })
        .catch(() => {
          this.$toast.error("Erro a apagar a configuração da notificação").goAway(3000)
        })
    },

    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.currentEmotion = {}
        this.newItem = {}
      })
    },

    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.newDeleteItem = {}
      })
    },

    save() {
      this.$axios
        .$post("/api/emotionsNotification", this.newItem)
        .then(() => {
          this.$toast.success('Notificações configuradas para ' + this.currentEmotion.display_name).goAway(3000)
          this.$axios.get("/api/emotions/"+this.newItem.emotion_name).then(response => {
            const notificationsSettings = response.data.data.notificationsSettings
            var index = this.emotions.findIndex(x => x.name === this.currentEmotion.name)
            this.emotions[index].notificationsSettings = notificationsSettings;
            this.close()
          });
          
        })
        .catch(() => {
          this.$toast.error("Erro a criar as configurações").goAway(3000)
        })
    }
  },
}
</script>
