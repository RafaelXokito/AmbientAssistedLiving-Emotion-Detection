<template>
    <div>
        <v-card class="mx-auto">
      <v-card-title class="d-flex justify-space-between align-center">
        <h3 class="text-left">
          Definição de mecanismos de regulação de emoções
        </h3>
        <v-btn @click="openDialog" class="m-5" variant="outline-info">Criar</v-btn>
      </v-card-title>
      <v-data-table :headers="headers" :items="emotionRegulationMechanisms" :loading="!(emotionRegulationMechanisms.length > 0)" sort-by=""
        class="elevation-1">
        <template v-slot:item.actions="{ item }">
          <v-icon v-if='item.is_default==="Não"' small @click="deleteItem(item)">
              mdi-delete
          </v-icon>
        </template>
        <template v-slot:no-data> Ainda não existem mecanismos de regulação de emoções registadas </template>
      </v-data-table>
      <v-divider></v-divider>  
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
    <v-dialog v-model="dialog">
      <v-card>
        <v-card-title>
          <span class="text-h5">Criar um mecanismo de regulação para uma emoção</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12" md="6">
                <v-select label="Emoção" v-model="newItem.emotion" :items="emotions" item-text="display_name" item-value="name" required ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select label="Mecanismo de regulação" v-model="newItem.regulation_mechanism" :items="regulationMechanisms" item-text="display_name" item-value="name" required ></v-select>
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
    </div>
</template>
<script>
export default {
  middleware: ('auth', 'client'),
  data() {
    return {
      dialog: false,
      dialogDelete: false,
      headers: [
        { text: 'Mecanismo de regulação', value: 'regulation_mechanism' },
        { text: 'Emoção', value: 'emotion' },
        { text: 'Pré-definida', value: 'is_default' },
        { text: 'Data de criação', value: 'created_at' },
        { text: 'Ações', value: 'actions', sortable: false }
      ],
      emotions: [],
      regulationMechanisms: [],
      emotionRegulationMechanisms: [],
      newDeleteItem: null,
      newItem: {},
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
        response.data.data.forEach(emotion => {
          if (emotion.name !== 'invalid')
            this.emotions.push({
              name: emotion.name,
              display_name: emotion.display_name
            })
        });
      })
      await this.$axios.get("/api/regulationMechanisms").then(response => {
        response.data.data.forEach(regulationMechanism => {
            this.regulationMechanisms.push({
              name: regulationMechanism.name,
              display_name: regulationMechanism.display_name
            })
        });
      })
      await this.$axios.get("/api/emotionRegulationMechanisms").then(response => {
        const erms = response.data.data;
        erms.forEach(erm => {
            this.emotionRegulationMechanisms.push({
              id: erm.id,
              regulation_mechanism: erm.regulation_mechanism,
              emotion: erm.emotion,
              is_default: erm.is_default == true ? "Sim" : "Não",
              created_at: new Date(erm.created_at).toLocaleString("pt-PT")
            })
        });
      })
    },
    openDialog(){
      this.dialog = true;
    },
    deleteItem(item) {
      this.newDeleteItem = item;
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      this.$axios
        .$delete("/api/emotionRegulationMechanisms/" + this.newDeleteItem.id)
        .then(() => {
          this.$toast.success('Mecanismo de regulação de emoções apagado').goAway(3000)
        })
        .catch((error) => {
          this.$toast.error("Erro a apagar a configuração da mecanismo de regulação de emoções").goAway(3000)
        })
    },

    close() {
      this.dialog = false
      this.$nextTick(() => {
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
      this.$axios.$post("/api/emotionRegulationMechanisms", this.newItem)
        .then(() => {
          this.$toast.success('Mecanismo de regulação de emoções criado').goAway(3000)
          this.close();
        })
        .catch(() => {
          this.$toast.error("Erro a criar a configuração da mecanismo de regulação de emoções").goAway(3000)
        })
    }
  },
}
</script>
