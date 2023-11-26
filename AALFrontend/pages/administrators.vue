<template>
  <div>
    <v-container>
      <v-card>
        <v-data-table
          :loading="finishedRequest == false"
          :headers="fields"
          :items="administrators"
          :search="search"
          sort-by="name"
          class="elevation-1"
          :items-per-page="perPage"
        >
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title>Administradores</v-toolbar-title>
              <v-divider class="mx-4" inset vertical></v-divider>
              <v-spacer></v-spacer>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                 label="Pesquisar"
                single-line
                hide-details
              ></v-text-field>
              <v-spacer></v-spacer>
              <v-dialog v-model="dialog" max-width="500px">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    color="primary"
                    dark
                    class="mb-2"
                    v-bind="attrs"
                    v-on="on"
                  >
                    Criar
                  </v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="text-h5">{{ formTitle }}</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12" md="12">
                          <v-text-field
                            v-model="editedItem.name"
                            label="Nome"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="12" md="6">
                          <v-text-field
                            v-model="editedItem.email"
                            label="Endereço de email"
                            type="email"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="12" md="6">
                          <v-text-field
                            v-model="editedItem.password"
                            label="Palavra-passe"
                            type="password"
                          ></v-text-field>
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
                  <v-card-title class="text-h5"
                    >Tem a certeza que quer apagar este administrador do sistema?</v-card-title
                  >
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDelete"
                      >Não</v-btn
                    >
                    <v-btn color="blue darken-1" text @click="deleteItemConfirm"
                      >Sim</v-btn
                    >
                    <v-spacer></v-spacer>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-toolbar>
          </template>
          <template v-slot:item.actions="{ item }">
            <v-icon small class="mr-2" @click="editItem(item)">
              mdi-pencil
            </v-icon>
            <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon>
          </template>
          <template v-slot:no-data> Ainda não existem administradores registados </template>
        </v-data-table>
      </v-card>
    </v-container>
  </div>
</template>

<script>
export default {
  components: {},
  middleware: ('auth', 'admin'),
  data() {
    return {
      dialog: false,
      dialogDelete: false,
      search: '',
      finishedRequest: false,
      editedItem: {
        email: null,
        password: null,
        name: null,
      },
      defaultItem: {
        email: null,
        password: null,
        name: null,
      },
      fields: [
        {
          value: 'name',
          text: 'Nome',
          sortDirection: 'desc',
        },
        {
          value: 'email',
          text: 'Endereço de email',
          sortDirection: 'desc',
        },
        {
          value: 'actions',
          text: 'Ações',
          sortable: false,
        },
      ],
      administrators: [],
      perPage: 10,
      currentPage: 1,
    }
  },
  computed: {
    currentUser() {
      return this.$auth.user
    },
    tableLength() {
      return this.administrators.length
    },
    formTitle() {
      return this.editedIndex === -1
        ? 'Criar novo administrador'
        : 'Editar administrador existente'
    },
  },
  created() {
    this.getAdministrators()
  },
  methods: {
    editItem(item) {},
    deleteItem(item) {},
    deleteItemConfirm() {
      this.administrators.splice(this.editedIndex, 1)
      this.closeDelete()
    },
    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
    save() {
      if (this.editedIndex > -1) {
        // TODO - Edit administrator request
        Object.assign(this.administrators[this.editedIndex], this.editedItem)
      } else {
        this.$axios
          .$post('/api/administrators', this.editedItem)
          .then(({ data }) => {
            this.$toast
              .success('Administrator ' + this.editedItem.name + ' created')
              .goAway(3000)
            this.administrators.push(data)

            this.close()
          })
          .catch(() => {
            this.$toast.error('Error creating administrator').goAway(3000)
          })
      }
    },
    getAdministrators() {
      this.$axios
        .$get('/api/administrators')
        .then(({ data }) => {
          this.administrators = data
          this.finishedRequest = true
        })
        .catch(() => {
          this.$toast.info('No administrators found').goAway(3000)
        })
    },
  },
}
</script>

<style>
</style>
