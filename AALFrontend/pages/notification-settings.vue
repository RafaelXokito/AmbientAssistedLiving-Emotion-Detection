<template>
  <v-card
  class="mx-auto"
  >
    <v-data-table
      :headers="headers"
      :items="emotions"
      :loading="!(emotions.length > 0)"
      sort-by="calories"
      class="elevation-1"
    >
      <template v-slot:item.actions="{ item }">
        <v-icon
          v-if="Object.keys(item.notificationsSettings).length"
          small
          class="mr-2"
          @click="editItem(item)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          v-else
          small
          class="mr-2"
          @click="editItem(item)"
        >
          mdi-pencil-plus
        </v-icon>
        <v-icon
          v-if="Object.keys(item.notificationsSettings).length"
          small
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
      </template>
      <template v-slot:no-data>
        <v-btn
          color="primary"
          @click="initialize"
        >
          Reiniciar
        </v-btn>
      </template>
    </v-data-table>
    <v-dialog
      v-model="dialog"
      max-width="500px"
    >
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ formTitle }}</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="editedItem.accuracyLimit"
                  label="Limite precisão (%)"
                  type="number"
                  required
                ></v-text-field>
              </v-col>
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="editedItem.durationSeconds"
                  label="duração (seg)"
                  type="number"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="blue darken-1"
            text
            @click="close"
          >
            Cancelar
          </v-btn>
          <v-btn
            color="blue darken-1"
            text
            @click="save"
          >
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
  data(){
    return {
      dialog: false,
      dialogDelete: false,
      headers: [
        { text: 'Nome', value: 'name' },
        { text: 'Grupo', value: 'group' },
        { text: 'Ações', value: 'actions', sortable: false },
      ],
      emotions: [],
      emotionsNotification: [],
      editedIndex: -1,
      editedItem: {
        id: -1,
        emotion_name: '',
        accuracyLimit: 0,
        durationSeconds: 0,
      },
      defaultItem: {
        id: -1,
        emotion_name: '',
        accuracyLimit: 0,
        durationSeconds: 0,
      },
    }
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'Configurar notificações para a emoção de ' + this.editedItem.emotion_name : 'Editar as configurações para notificações da emoção de ' + this.editedItem.emotion_name
    },
  },
  watch: {
    dialog (val) {
      val || this.close()
    },
    dialogDelete (val) {
      val || this.closeDelete()
    },
  },
  created () {
    this.initialize()
  },
  methods: {
    async initialize () {

      await this.$axios.get("/api/emotionsNotification").then(({data}) => {
        data = data.data
        data.forEach(emotionNotification => {
          if (emotionNotification.name !== 'invalid')
            this.emotionsNotification.push({
              id: emotionNotification.id,
              emotion_name: this.firstCapitalLetter(emotionNotification.emotion_name),
              accuracyLimit: emotionNotification.accuracyLimit,
              durationSeconds: emotionNotification.durationSeconds,
            })
        })

        this.$axios.get("/api/emotions").then(response => {
          const emotions = response.data.data
          emotions.forEach(emotion => {
            if (emotion.name !== 'invalid')
              this.emotions.push({
                name: this.firstCapitalLetter(emotion.name),
                group: this.firstCapitalLetter(emotion.group),
                notificationsSettings: this.emotionsNotification.find(e => e.emotion_name === this.firstCapitalLetter(emotion.name)) ?? {}
              })
          })
        })
      })
    },

    firstCapitalLetter(str = "") {
      return str.toString().charAt(0).toUpperCase() + str.toString().slice(1)
    },

    editItem (item) {

      this.editedIndex = this.emotions.indexOf(item)
      if (Object.keys(item.notificationsSettings).length)
        this.editedItem = Object.assign({}, item.notificationsSettings)
      else
        this.editedItem = Object.assign({}, {
          emotion_name: item.name,
          accuracyLimit: null,
          durationSeconds: null,
        })
      this.dialog = true
    },

    deleteItem (item) {
      this.editedIndex = this.emotions.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },

    deleteItemConfirm () {
      const notificationsSettings = this.emotions.at(this.editedIndex).notificationsSettings
      this.$axios
        .$delete("/api/emotionsNotification/"+notificationsSettings.id)
        .then(() => {
          this.$toast.success('Notification '+notificationsSettings.name+' for '+ notificationsSettings.emotion_name +' configured').goAway(3000)

          Object.assign(this.emotions[this.editedIndex], {
            name: this.emotions[this.editedIndex].name,
            group: this.emotions[this.editedIndex].group,
            notificationsSettings: {}
          })
          this.emotionsNotification.splice(this.emotionsNotification.findIndex(e => e.emotion_name === notificationsSettings.emotion_name), 1)
          this.closeDelete()
        })
        .catch(() => {
          this.$toast.error("Erro a apagar a configuração da notificação").goAway(3000)
        })
    },

    close () {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    closeDelete () {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    save () {
      // this.editedIndex > -1
      if (this.emotions[this.editedIndex].notificationsSettings.id) {
        // TODO - Edit notification setting request
        Object.assign(this.emotionsNotification[this.editedIndex], this.editedItem)
      } else {
        this.$axios
          .$post("/api/emotionsNotification", this.editedItem)
          .then(emotionNotification => {
            this.$toast.success('Notificações configuradas para ' + this.editedItem.emotion_name).goAway(3000)
            Object.assign(this.emotions[this.editedIndex], {
              name: this.emotions[this.editedIndex].name,
              group: this.emotions[this.editedIndex].group,
              notificationsSettings: {
                id: emotionNotification.id,
                emotion_name: this.firstCapitalLetter(emotionNotification.emotion_name),
                accuracyLimit: emotionNotification.accuracyLimit,
                durationSeconds: emotionNotification.durationSeconds,
              }
            })
            this.emotionsNotification.push(emotionNotification)
            this.close()
          })
          .catch(() => {
            this.$toast.error("Error a criar as configurações").goAway(3000)
          })
      }
    },
  },
}
</script>
