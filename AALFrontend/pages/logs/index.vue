<template>
<div>

    <v-card>
          <v-card-title>
            Logs
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
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
          :loading="logs.length === 0"
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

        </v-data-table>
        </v-card>
</div>
</template>
<script>
export default {
  data() {
    return {
      middleware: ('auth', 'admin'),
      logs:[],
      search: '',
      fields: [
        {
          value: "content",
          text: "Content",
          sortDirection: "desc",
        },
        {
          value: "client_id",
          text: "Client Id",
          sortDirection: "desc",
        },
        {
          value: "process",
          text: "Process Name",
          sortDirection: "desc",
        },
        {
          value: "macaddress",
          text: "MAC Address",
          sortDirection: "desc",
        },
        {
          value: "created_at",
          text: "Created At",
          sortable: "desc",
        },
      ],
      perPage: 10,
      currentPage: 1
    };
  },
  created(){
    this.$axios.$get("/api/logs")
    .then(({data})=>{
        data.forEach(element => {
            let createdAt = new Date(element["created_at"]*1000).toISOString().substr(0, 16).split("T")
            element["created_at"] = createdAt[0] + " " + createdAt[1]
        });
        this.logs = data
    })
    .catch(() => {
        this.$toast.info("No logs found").goAway(3000)
    })
  }
}
</script>

<style>

</style>


