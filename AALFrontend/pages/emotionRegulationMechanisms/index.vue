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
          <v-icon small @click="deleteItem(item)">
              mdi-delete
          </v-icon>
          <v-icon small class="ml-4" @click="showContents(item)">
              mdi-expand-all
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
   <div class="mt-10" v-if="dialogShowContents">
    <div class="d-flex justify-space-between align-center mx-4">
      <h4 class="text-left">
          Conteúdos do mecanismo de regulação de emoções para a emoção '{{ itemToShow.emotion }}'
       </h4>
      <v-btn @click="openContentDialog" class="m-5" variant="outline-info">Criar conteúdo</v-btn>
    </div>
    <v-data-table :headers="headersContents" :items="itemToShow.contents" :loading="!(emotionRegulationMechanisms.length > 0)" sort-by=""
        class="elevation-1 mt-4">
        <template v-slot:[`item.text`]="{ item }">
          <div v-if="item.text != null">{{ item.text }}</div>
          <div v-else>❌</div>
        </template>
        <template v-slot:[`item.file_path`]="{ item }">
          <a v-if="item.file_path != null" :href="item.file_path" target="_blank">{{ item.file_path }}</a>
          <div v-else>❌</div>
        </template>
        <template v-slot:item.actions="{ item }">
          <v-icon small @click="deleteContentItem(item)">
              mdi-delete
          </v-icon>
        </template>
        <template v-slot:no-data> Ainda não existem conteúdos para este mecanismo de regulação de emoções </template>
      </v-data-table>
      <v-dialog v-model="dialogDeleteContent" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Tem a certeza que quer apagar este item?</v-card-title>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeShowContent">Não</v-btn>
          <v-btn color="blue darken-1" text @click="deleteItemContentConfirm">Sim</v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-if="dialogCreateContent" v-model="newContentItem" max-width="900px">
      <v-card>
      <v-card-title class="text-h5">Criar um conteúdo para o mecanismo de regulação da emoção '{{ itemToShow.emotion }}'</v-card-title>
      <v-card-text>
          <v-container>
            <v-row>
            <v-col cols="12" md="6">
              <v-select label="Tipo de conteúdo" v-model="newContentItem.content_type" :items="contentTypes" item-text="display_name" item-value="name" required ></v-select>
            </v-col>
            <v-col v-if="newContentItem.content_type == 'text'" cols="12" md="6">
              <v-text-field
                label="Texto"
                outlined
                v-model="newContentItem.text"
              />
            </v-col>
            <v-col cols="12" md="6" v-if="newContentItem.content_type != null && newContentItem.content_type != 'text'">
              <v-file-input
                v-model="newContentItem.file"
                label="Adicionar ficheiro"
                prepend-icon="mdi-paperclip"
              ></v-file-input>
            </v-col>
          </v-row>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeCreateContent">Sair</v-btn>
          <v-btn color="blue darken-1" text @click="createContentConfirm">Guardar</v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </div>
    </v-card>
    <v-dialog v-model="dialog" max-width="800px">
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
                <v-text-field v-model="newItem.threshold" label="Limite precisão (%)" type="number"
                  required></v-text-field>              </v-col>
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
      dialogShowContents: false,
      dialogDeleteContent: false,
      dialogCreateContent: false,
      contentTypes: [
                    {"display_name": "Texto", "name": "text"},
                    {"display_name": "Imagem", "name": "image"},
                    {"display_name": "Áudio", "name": "audio"},
                    {"display_name": "Vídeo", "name": "video"}
                  ],
      headers: [
        { text: 'Emoção', value: 'emotion' },
        { text: 'Limite de precisão', value: 'threshold' },
        { text: 'Data de criação', value: 'created_at' },
        { text: 'Data de atualização', value: 'updated_at' },
        { text: 'Ações', value: 'actions', sortable: false }
      ],
      headersContents:[
        { text: "Tipo", value: "content_type"},
        { text: "Texto", value: "text"},
        { text: "Ficheiro", value: "file_path"},
        { text: 'Data de criação', value: 'created_at' },
        { text: 'Data de atualização', value: 'updated_at' },
        { text: 'Ações', value: 'actions', sortable: false }
      ],
      emotions: [],
      regulationMechanismsContents: [],
      emotionRegulationMechanisms: [],
      newDeleteItem: null,
      newDeleteItemContent: null,
      itemToShow: null,
      newContentItem: {},
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
    dialogShowContents(val) {
      val || this.closeShowContent()
    },
  },
  created() {
    this.initialize()
  },
  methods: {
    async getEmotionRegulationMechanisms(){
      this.emotionRegulationMechanisms = [];
      await this.$axios.get("/api/emotionRegulationMechanisms").then(response => {
        const erms = response.data.data;
        erms.forEach(erm => {
          var contents = [];
          erm.contents.forEach( ermContent => {
            var content = "";
            switch(ermContent.content_type){
              case "text":
                content = "💬 Texto";
                break;
              case "image":
                content = "🖼️ Imagem";
                break;
              case "audio":
                content = "🎶 Áudio";
                break;
              case "video":
                content = "📹 Vídeo";
                break;
            }
            contents.push({
              id: ermContent.id,
              content_type: content,
              text: ermContent.text,
              file_path: ermContent.file_path == null ? null : process.env.API_URL + ermContent.file_path,
              created_at: new Date(ermContent.created_at).toLocaleString("pt-PT"),
              updated_at: new Date(ermContent.updated_at).toLocaleString("pt-PT"),
            });
          })
            this.emotionRegulationMechanisms.push({
              id: erm.id,
              emotion: erm.emotion,
              created_at: new Date(erm.created_at).toLocaleString("pt-PT"),
              updated_at: new Date(erm.updated_at).toLocaleString("pt-PT"),
              threshold: erm.threshold + '%',
              contents: contents
            })
        });
      })
    },
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
      await this.getEmotionRegulationMechanisms();
    },
    openDialog(){
      this.dialog = true;
    },
    openContentDialog(){
      this.dialogCreateContent = true;
    },
    showContents(item){
      this.itemToShow = item;
      this.dialogShowContents = true;
    },
    deleteItem(item) {
      this.newDeleteItem = item;
      this.dialogDelete = true;
    },
    deleteContentItem(item){
      this.newDeleteItemContent = item;
      this.dialogDeleteContent = true;
    },
    removeFromList(list, idItemRemove){
      const index = list.findIndex(item => item.id === idItemRemove);
          if (index !== -1) {
            list.splice(index, 1);
          }
    },
    deleteItemContentConfirm(){
      this.$axios
        .$delete("/api/regulationMechanismContents/" + this.newDeleteItemContent.id)
        .then(() => {
          this.$toast.success('Conteúdo do mecanismo de regulação da emoção '+ this.itemToShow.emotion +' apagado').goAway(3000)
          this.removeFromList(this.itemToShow.contents, this.newDeleteItemContent.id);
          this.closeShowContent();
        })
        .catch((error) => {
          this.$toast.error("Erro a apagar o conteúdo do mecanismo de regulação da emoção "+ this.itemToShow.emotion).goAway(3000)
        })
    },
    deleteItemConfirm() {
      this.$axios
        .$delete("/api/emotionRegulationMechanisms/" + this.newDeleteItem.id)
        .then(() => {
          this.$toast.success('Mecanismo de regulação de emoções apagado').goAway(3000)
          this.removeFromList(this.emotionRegulationMechanisms, this.newDeleteItem.id);
          this.closeDelete();
        })
        .catch((error) => {
          this.$toast.error("Erro a apagar a configuração da mecanismo de regulação de emoções").goAway(3000)
        })
    },
    createContentConfirm(){
      let data = new FormData();
      data.append('emotion_regulation_mechanism', this.itemToShow.id);
      data.append('content_type',  this.newContentItem.content_type);
      if(this.newContentItem.content_type == "text"){
        data.append('text', this.newContentItem.text);
      }else{
        data.append('file', this.newContentItem.file);
      }

      this.$axios.$post("/api/regulationMechanismContents", data)
        .then(async () => {
          this.$toast.success('Conteúdo criado com sucesso').goAway(3000)
          await this.getEmotionRegulationMechanisms();
          this.closeCreateContent();
        })
        .catch(() => {
          this.$toast.error("Erro a criar a configuração da mecanismo de regulação de emoções").goAway(3000)
        })
    },
    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.newItem = {}
      })
    },
    closeCreateContent(){
      this.dialogCreateContent = false;
      this.$nextTick(() => {
        this.newContentItem = {}
      })
    },
    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.newDeleteItem = {}
      })
    },
    closeShowContent() {
      this.dialogDeleteContent = false
      this.$nextTick(() => {
        this.newDeleteItemContent = {}
      })
    },
    save() {
      this.$axios.$post("/api/emotionRegulationMechanisms", this.newItem)
        .then(async () => {
          this.$toast.success('Mecanismo de regulação de emoções criado').goAway(3000)
          await this.getEmotionRegulationMechanisms()
          this.close();
        })
        .catch(() => {
          this.$toast.error("Erro a criar a configuração da mecanismo de regulação de emoções").goAway(3000)
        })
    }
  },
}
</script>
