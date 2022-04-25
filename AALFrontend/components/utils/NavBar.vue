<template>
<div>
  <b-navbar toggleable="lg" type="dark" variant="info">
    <b-navbar-brand to="dashboard">
      <img src="../../static/AAL_logo.jpg" class="img-nav-brand" alt="Kitten">
      AAL-Emotion
    </b-navbar-brand>

    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav class="ml-auto text-center">
        <b-nav-item v-if="$auth.user.scope === 'Client'" :class="this.$route.name == 'Frames' ? 'active' : ''" to="Frames">Frames</b-nav-item>
        <b-nav-item v-if="$auth.user.scope === 'Administrator'" :class="this.$route.name == 'Clients' ? 'active' : ''" to="Clients">Clients</b-nav-item>
      </b-navbar-nav>

      <!-- Right aligned nav items -->
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown right>
          <!-- Using 'button-content' slot -->
          <template #button-content>
            <em>{{currentUser.name}}</em>
          </template>
          <b-dropdown-item :class="this.$route.name == 'Profile' ? 'active' : ''" to="Profile">Profile</b-dropdown-item>
          <b-dropdown-item :class="this.$route.name == 'ChangePassword' ? 'active' : ''" to="ChangePassword">Change Password</b-dropdown-item>
          <b-dropdown-item href="#" @click="logout">Sign Out</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</div>
</template>

<script>
export default {
  computed: {
    currentUser(){
      return this.$auth.user
    }
  },
  methods: {
    async logout(){
      await this.$auth.logout()
    }
  }
}
</script>

<style>
.img-nav-brand {
  height: 40px;
  width: 40px;
}
</style>
