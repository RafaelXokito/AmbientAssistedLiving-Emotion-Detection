<template>
  <div>
    <v-container>
      <v-card>
        <v-data-table
          :loading="finishedRequest == false"
          :headers="fields"
          :items="clients"
          :search="search"
          sort-by="name"
          class="elevation-1"
          :items-per-page="perPage"
        >
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title>Clients</v-toolbar-title>
              <v-divider class="mx-4" inset vertical></v-divider>
              <v-spacer></v-spacer>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search"
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
                    New Item
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
                            label="Name"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                          <v-text-field
                            v-model="editedItem.email"
                            label="Email"
                            type="email"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                          <vue-tel-input-vuetify
                            defaultCountry="PT"
                            v-model="editedItem.contact"
                            :validate-on-blur="true"
                            @validate="validatePhoneNumber"
                            :error-messages="
                              editedItem.contact.length > 0 &&
                              !editedItem.contactValid
                                ? 'Enter a valid phone number'
                                : ''
                            "
                          ></vue-tel-input-vuetify>
                        </v-col>
                        <v-col cols="12" md="12">
                          <v-text-field
                            v-model="editedItem.password"
                            label="Password"
                            type="password"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                          <v-menu
                            v-model="menu"
                            :close-on-content-click="false"
                            :nudge-right="40"
                            transition="scale-transition"
                            offset-y
                            min-width="auto"
                          >
                            <template v-slot:activator="{ on, attrs }">
                              <v-text-field
                                v-model="editedItem.birthdate"
                                label="Picker without buttons"
                                prepend-icon="mdi-calendar"
                                readonly
                                v-bind="attrs"
                                v-on="on"
                              ></v-text-field>
                            </template>
                            <v-date-picker
                              v-model="editedItem.birthdate"
                              color="red lighten-1"
                              :max="
                                new Date(
                                  Date.now() -
                                    new Date().getTimezoneOffset() * 60000
                                )
                                  .toISOString()
                                  .substr(0, 10)
                              "
                              min="1950-01-01"
                              @input="menu = false"
                              required
                            ></v-date-picker>
                          </v-menu>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="close">
                      Cancel
                    </v-btn>
                    <v-btn color="blue darken-1" text @click="save">
                      Save
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
              <v-dialog v-model="dialogDelete" max-width="500px">
                <v-card>
                  <v-card-title class="text-h5"
                    >Are you sure you want to delete this item?</v-card-title
                  >
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDelete"
                      >Cancel</v-btn
                    >
                    <v-btn color="blue darken-1" text @click="deleteItemConfirm"
                      >OK</v-btn
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
          <template v-slot:no-data> No clients created yet </template>
        </v-data-table>
      </v-card>
    </v-container>
  </div>
</template>

<script>
import VueTelInputVuetify from 'vue-tel-input-vuetify/lib/vue-tel-input-vuetify.vue'

export default {
  components: {
    VueTelInputVuetify,
  },
  middleware: ('auth', 'admin'),
  data() {
    return {
      activePicker: null,
      finishedRequest: false,
      date: new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
        .toISOString()
        .substr(0, 10),
      menu: false,
      dialog: false,
      dialogDelete: false,
      search: '',
      editedIndex: -1,
      editedItem: {
        email: null,
        password: null,
        name: null,
        birthdate: null,
        contact: '',
        contactValid: null,
      },
      defaultItem: {
        email: null,
        password: null,
        name: null,
        birthdate: null,
        contact: '',
        contactValid: null,
      },
      fields: [
        {
          value: 'name',
          text: 'Name',
          sortDirection: 'desc',
        },
        {
          value: 'email',
          text: 'Email',
          sortDirection: 'desc',
        },
        {
          value: 'actions',
          text: 'Actions',
          sortable: false,
        },
      ],
      clients: [],
      perPage: 10,
      currentPage: 1,
    }
  },
  computed: {
    currentUser() {
      return this.$auth.user
    },
    tableLength() {
      return this.clients.length
    },
    formTitle() {
      return this.editedIndex === -1 ? 'New Client' : 'Edit Client'
    },
  },
  created() {
    this.getClients()
  },
  methods: {
    validatePhoneNumber({ number, isValid, country }) {
      this.editedItem.contactValid = isValid
    },
    editItem(item) {},
    deleteItem(item) {},
    deleteItemConfirm() {
      this.clients.splice(this.editedIndex, 1)
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
        // TODO - Update Client request

        Object.assign(this.clients[this.editedIndex], this.editedItem)
      } else {
        this.editedItem.contact = this.editedItem.contact.replace(/\s/g, '')

        this.$axios
          .$post('/api/clients', this.editedItem)
          .then(({ data }) => {
            this.$toast.success('Client ' + data.name + ' created').goAway(3000)
            this.clients.push(data)
            this.close()
          })
          .catch(() => {
            this.$toast.error('Error creating client').goAway(3000)
          })
      }
    },
    getClients() {
      this.$axios
        .$get('/api/clients')
        .then(({ data }) => {
          this.clients = data
          this.finishedRequest = true
        })
        .catch(() => {
          this.$toast.info('No clients found').goAway(3000)
        })
    },
  },
}
</script>

<style>
</style>
