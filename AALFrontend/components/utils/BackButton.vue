<template>
  <div>
    <v-btn
      class="ma-2"
      color="red"
      dark
      @click="$router.go(-1)"
    >
      <v-icon
        dark
        left
      >
        mdi-arrow-left
      </v-icon>Voltar atrás
    </v-btn>
  </div>
</template>

<script>
export default {
  components: {}
}
</script>
