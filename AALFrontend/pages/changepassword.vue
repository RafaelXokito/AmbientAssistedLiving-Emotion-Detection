<template>
  <div>
    <navbar/>
    <h1>Change Password</h1>
  </div>
</template>

<script>
import navbar from '~/components/utils/NavBar.vue'
export default {
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

</style>
