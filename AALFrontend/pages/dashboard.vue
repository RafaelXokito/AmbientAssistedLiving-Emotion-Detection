<template>
  <div>
    <navbar/>
    <header class="text-white text-center">
      <img src="/DashboardImage.svg" class="rounded-circle" width="500">
      <h1>Bem vindo(a) {{currentUser.name}}</h1>
    </header>
    <footer>
      <a href="https://storyset.com/people">People illustrations by Storyset</a>
    </footer>
  </div>
</template>

<script>
import navbar from '~/components/utils/NavBar.vue'
export default {
  middleware: ('auth'),
  components: {
    navbar,
  },
  computed: {
    currentUser(){
      return this.$auth.user
    }
  },
}
</script>

<style>
header {
  background-color: #FFDAC1;
  font-family: var(--bs-body-font-family);
}
h1 {
  padding-bottom: 150px;
}
</style>
