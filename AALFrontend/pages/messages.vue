<template>
    <div>
        <v-card>
            <v-card-title>
                <h3>
                    Mensagens
                </h3>
            </v-card-title>
            <div>
                <v-card-text>
                    <v-text-field v-model="search" append-icon="mdi-magnify" label="Pesquisar" single-line
                        hide-details></v-text-field>
                </v-card-text>
                <v-data-table id="messagesTable" :headers="fields" :items="messages" :items-per-page="perPage"
                    class="elevation-1" :loading="messages.length === 0" :search="search">
                    <template v-slot:item.created_at="{ item }">
                        {{
                            item.created_at != null
                            ? new Date(item.created_at * 1000).toLocaleString("pt-PT")
                            : "Data não apresentada"
                        }}
                    </template>
                    <template v-slot:item.isChatbot="{ item }">
                        {{
                            item.isChatbot
                            ? "Agente conversacional"
                            : "Utilizador"
                        }}
                    </template>
                <template v-slot:no-data> Ainda não existem mensagens registadas </template>
                </v-data-table>
            </div>
        </v-card>
    </div>
</template>


<script>
export default {
    components: {
    },
    middleware: ("auth", "client"),
    data() {
        return {
            perPage: 10,
            currentPage: 1,
            search: '',
            messages: [],
            fields: [
                {
                    value: "created_at",
                    text: "Data de envio",
                    sortDirection: "desc",
                },
                {
                    value: "body",
                    text: "Mensagem",
                    sortDirection: "desc",
                },
                {
                    value: "isChatbot",
                    text: "Utilizador",
                    sortDirection: "desc",
                }
            ]
        }
    },
    async created() {
        await this.getMessages()
    },
    methods: {
        async getMessages() {
            await this.$axios.get("/api/messages?order=desc").then(response => {
                this.messages = [];
                response.data.data.forEach(msg => {
                    this.messages.push({
                        created_at: msg.created_at,
                        body: msg.body,
                        isChatbot: msg.isChatbot
                    })
                });
            })
                .catch(() => {
                    this.$toast.info("Não existem mensagens").goAway(3000)
                })
        }
    }
}

</script>