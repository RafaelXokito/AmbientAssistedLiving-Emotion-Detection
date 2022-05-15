<template>
    <b-navbar toggleable="lg" type="light" :transparent="true" class="navbar">
      <b-container>
      <b-navbar-brand to="/dashboard">
          Home
      </b-navbar-brand>

      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

      <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav class="ml-auto text-center">
          <b-nav-item v-if="$auth.user.scope === 'Client'" :class="this.$route.name == 'iterations' ? 'active' : ''"
                      to="/iterations">Iterations
          </b-nav-item>
          <b-nav-item v-if="$auth.user.scope === 'Administrator'" :class="this.$route.name == 'clients' ? 'active' : ''"
                      to="/clients">Clients
          </b-nav-item>
          <b-nav-item v-if="$auth.user.scope === 'Administrator'"
                      :class="this.$route.name == 'administrators' ? 'active' : ''" to="/administrators">Administrators
          </b-nav-item>
          <b-nav-item v-if="$auth.user.scope === 'Administrator'"
                      :class="this.$route.name == 'emotions' ? 'active' : ''" to="/emotions">Emotions
          </b-nav-item>
        </b-navbar-nav>
        <!-- Right aligned nav items -->
        <b-navbar-nav class="ml-auto">
          <b-nav-item-dropdown right
                               :class="this.$route.name == 'profile' || this.$route.name == 'changepassword' ? 'active' : ''">
            <!-- Using 'button-content' slot -->
            <template #button-content>
              <em>{{ currentUser.name }}</em>
            </template>
            <b-dropdown-item :active="this.$route.name == 'profile'" to="/profile">Profile</b-dropdown-item>
            <b-dropdown-item :active="this.$route.name == 'changepassword'" to="/changepassword">Change Password
            </b-dropdown-item>
            <b-dropdown-item href="#" @click="logout">Sign Out</b-dropdown-item>
          </b-nav-item-dropdown>
        </b-navbar-nav>
      </b-collapse>
      </b-container>
    </b-navbar>
</template>


<script setup>
import {Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems} from '@headlessui/vue'
import {BellIcon, MenuIcon, XIcon} from '@heroicons/vue/outline'

const navigation = [
  {name: 'Dashboard', href: '#', current: true},
  {name: 'Team', href: '#', current: false},
  {name: 'Projects', href: '#', current: false},
  {name: 'Calendar', href: '#', current: false},
]
</script>

<script>
export default {
  computed: {
    currentUser() {
      return this.$auth.user
    }
  },
  methods: {
    async logout() {
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
.navbar {
  -webkit-box-shadow: 0 8px 6px -9px #999;
  -moz-box-shadow: 0 8px 6px -9px #999;
  box-shadow: 0 8px 6px -9px #999;

  /* the rest of your styling */
}
</style>
