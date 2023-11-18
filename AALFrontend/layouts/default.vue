<template>

  <v-app>
    <vertical-nav-menu v-if="currentUser" :is-drawer-open.sync="isDrawerOpen"></vertical-nav-menu>

    <v-app-bar
      v-if="currentUser"
      app
      flat
      absolute
      color="transparent"
    >
      <div class="boxed-container w-full">
        <div class="d-flex align-center mx-6">
          <!-- Left Content -->
          <v-app-bar-nav-icon
            class="d-block d-lg-none me-2"
            @click="isDrawerOpen = !isDrawerOpen"
          ></v-app-bar-nav-icon>
          <v-text-field
            rounded
            dense
            outlined
            :prepend-inner-icon="icons.mdiMagnify"
            class="app-bar-search flex-grow-0"
            hide-details
          ></v-text-field>

          <v-spacer></v-spacer>

          <!-- Right Content -->
          <theme-switcher></theme-switcher>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                class="ms-3"
                v-bind="attrs"
                v-on="on"
                @click="notifiable"
              >
                <v-icon v-if="!currentUser.notifiable">
                  {{ icons.mdiBellOutline }}
                </v-icon>
                <v-icon v-else>
                  {{ icons.mdiBellRingOutline }}
                </v-icon>
              </v-btn>
            </template>
            <span v-if="!currentUser.notifiable">Ativar notificações</span>
            <span v-else>Desativar notificações</span>
          </v-tooltip>

          <app-bar-user-menu></app-bar-user-menu>
        </div>
      </div>
    </v-app-bar>

    <v-main>
      <div class="app-content-container boxed-container pa-6">
        <Nuxt />
      </div>
    </v-main>

    <v-footer
      app
      inset
      color="transparent"
      absolute
      height="56"
      class="px-0"
    >
      <div class="boxed-container w-full">
        <div class="mx-6 d-flex justify-space-between">
          <span>
            &copy; 2021 <a
              href="https://themeselection.com"
              class="text-decoration-none"
              target="_blank"
            >ThemeSelection</a></span>
          <span class="d-sm-inline d-none">
            <a
              href="https://themeselection.com/products/category/download-free-admin-templates/"
              target="_blank"
              class="me-6 text--secondary text-decoration-none"
            >Freebies</a>
            <a
              href="https://themeselection.com/blog/"
              target="_blank"
              class="me-6 text--secondary text-decoration-none"
            >Blog</a>
            <a
              href="https://github.com/themeselection/materio-vuetify-vuejs-admin-template-free/blob/main/LICENSE"
              target="_blank"
              class="text--secondary text-decoration-none"
            >MIT Licence</a>
          </span>
        </div>
      </div>
    </v-footer>
  </v-app>
</template>

<script>
import { ref } from '@vue/composition-api'
import { mdiMagnify, mdiBellOutline, mdiBellRingOutline, mdiGithub } from '@mdi/js'
import VerticalNavMenu from '@/components/vertical-nav-menu/VerticalNavMenu.vue'
import ThemeSwitcher from '@/components/ThemeSwitcher.vue'
import AppBarUserMenu from '@/components/AppBarUserMenu.vue'

export default {
  components: {
    VerticalNavMenu,
    ThemeSwitcher,
    AppBarUserMenu,
  },
  computed: {
    currentUser() {
      return this.$auth.user
    },
  },
  setup() {
    const isDrawerOpen = ref(null)

    return {
      isDrawerOpen,

      // Icons
      icons: {
        mdiMagnify,
        mdiBellOutline,
        mdiGithub,
        mdiBellRingOutline
      },
    }
  },
  methods: {
    notifiable(){
      this.$axios.$patch("/api/auth/notifiable").then(({data}) => {
        this.$auth.setUser(data)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.v-app-bar ::v-deep {
  .v-toolbar__content {
    padding: 0;

    .app-bar-search {
      .v-input__slot {
        padding-left: 18px;
      }
    }
  }
}

.boxed-container {
  max-width: 1440px;
  margin-left: auto;
  margin-right: auto;
}
</style>
