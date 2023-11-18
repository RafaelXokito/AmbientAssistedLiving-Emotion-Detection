<template>
  <div>
    <v-card>
      <v-card-title>
        Registo de logs
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Pesquisar"
          single-line
          hide-details
        ></v-text-field>
      </v-card-title>
      <v-data-table
        id="logsTable"
        :headers="fields"
        :items="logs"
        :items-per-page="perPage"
        class="elevation-1"
        :loading="finishedRequest == false"
        :search="search"
      >
        <!-- <template v-slot:item.="{item}">
            {{ firstCapitalLetter(item.emotion.name) }}
          </template>
          <template v-slot:item.created_at="{item}">
            {{
              item.created_at != null
                ? new Date(item.created_at).toLocaleString("pt-PT")
                : "Not Shown"
            }}
          </template>-->
        <template v-slot:no-data> Ainda não existem registos de logs </template>
      </v-data-table>
    </v-card>
  </div>
</template>
<script>
export default {
  data() {
    return {
      finishedRequest: false,
      middleware: ('auth', 'admin'),
      logs: [],
      search: '',
      fields: [
        {
          value: 'content',
          text: 'Conteúdo',
          sortDirection: 'desc',
        },
        {
          value: 'client_id',
          text: 'Id do Cliente',
          sortDirection: 'desc',
        },
        {
          value: 'process',
          text: 'Nome do processo',
          sortDirection: 'desc',
        },
        {
          value: 'macaddress',
          text: 'Endereço MAC',
          sortDirection: 'desc',
        },
        {
          value: 'created_at',
          text: 'Data de criação',
          sortable: 'desc',
        },
      ],
      perPage: 10,
      currentPage: 1,
    }
  },
  computed: {
    tableLength() {
      return this.logs.length
    },
  },
  methods: {
    getLogs() {
      this.$axios
        .$get('/api/logs')
        .then(({ data }) => {
          data.forEach((element) => {
            let createdAt = new Date(element['created_at'] * 1000)
              .toISOString()
              .substr(0, 16)
              .split('T')
            element['created_at'] = createdAt[0] + ' ' + createdAt[1]
          })
          this.logs = data
          this.finishedRequest = true
        })
        .catch(() => {
          this.$toast.info('Ainda não existem registos de logs').goAway(3000)
        })
    },
  },
  created() {
    this.getLogs()
  },
}
</script>

<style>
</style>


