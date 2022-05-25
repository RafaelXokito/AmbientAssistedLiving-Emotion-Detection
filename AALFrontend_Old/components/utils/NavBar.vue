<template>
    <b-navbar toggleable="lg" type="light" :transparent="true" class="navbar">
      <b-container>
      <b-navbar-brand to="/dashboard">
          Home
      </b-navbar-brand>
      <b-navbar-toggle target="nav-collapse">
        <template #default="{ expanded }">
          <b-icon v-if="expanded" icon="chevron-bar-up"></b-icon>
          <b-icon v-else icon="chevron-bar-down"></b-icon>
        </template>
      </b-navbar-toggle>
      <b-collapse id="nav-collapse" is-nav v-model="isOpen">
        <b-navbar-nav :class="!isOpen ? 'ml-auto text-center' : ''">
          <b-nav-item v-if="$auth.user.scope === 'Client'" :class="this.$route.name == 'iterations' ? 'active' : ''"
                      to="/iterations">
            <div class="d-flex flex-column align-items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <p class="text-sm">
                Iterations
              </p>
            </div>
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
          <b-nav-item v-if="$auth.user.scope === 'Client'"
                      :class="this.$route.name == 'configurations' ? 'active' : ''" to="/configurations">
            <div class="d-flex flex-column align-items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <p class="text-sm">
                Configurations
              </p>
            </div>
          </b-nav-item>
        </b-navbar-nav>
        <!-- Right aligned nav items -->
        <b-navbar-nav :class="!isOpen ? 'ml-auto' : ''">
          <b-nav-item-dropdown right :class="this.$route.name == 'profile' || this.$route.name == 'changepassword' ? 'active' : ''">
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
  data() {
    return {
      isOpen: false
    }
  },
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
