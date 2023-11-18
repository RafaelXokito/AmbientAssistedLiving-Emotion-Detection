<template>
  <v-navigation-drawer
    :value="isDrawerOpen"
    app
    floating
    width="260"
    class="app-navigation-menu"
    :right="$vuetify.rtl"
    @input="val => $emit('update:is-drawer-open', val)"
  >
    <!-- Navigation Header -->
    <div class="vertical-nav-header d-flex items-center ps-6 pe-5 pt-5 pb-2">
      <NuxtLink
        to="/"
        exact
        class="d-flex align-center text-decoration-none"
      >
        <v-img
          :src="require('@/assets/images/logos/logo_simple.png')"
          max-height="50px"
          max-width="50px"
          alt="logo"
          contain
          eager
          class="app-logo me-3"
        ></v-img>
        <v-slide-x-transition>
          <h2 class="app-title text--primary">
            SmartEmotion
          </h2>
        </v-slide-x-transition>
      </NuxtLink>
    </div>

    <!-- Navigation Items -->
    <v-list
      expand
      shaped
      class="vertical-nav-menu-items pr-5"
    >
      <nav-menu-link
        title="Página principal"
        to="/"
        :icon="icons.mdiHomeOutline"
      ></nav-menu-link>
      <nav-menu-link
        v-if="currentUser.scope === 'Client'"
        title="Definir notificações"
        to="/notification-settings"
        :icon="icons.mdiMessageCogOutline"
      ></nav-menu-link>
      <nav-menu-section-title title="Conteúdos"></nav-menu-section-title>
      <nav-menu-link
        v-if="currentUser.scope === 'Client'"
        title="Iterações"
        to="/iterations"
        :icon="icons.mdiEyeOutline"
      ></nav-menu-link>
      <nav-menu-link
        v-if="currentUser.scope === 'Client'"
        title="Questionários"
        to="/questionnaires"
        :icon="icons.mdiEyeOutline"
      ></nav-menu-link>
      <nav-menu-link
        v-if="currentUser.scope === 'Administrator'"
        title="Logs"
        to="/logs"
        :icon="icons.mdiFileOutline"
      ></nav-menu-link>
      <nav-menu-link
        v-if="currentUser.scope === 'Administrator'"
        title="Administrators"
        to="/administrators"
        :icon="icons.mdiAccountTieOutline"
      ></nav-menu-link>
      <nav-menu-link
        v-if="currentUser.scope === 'Administrator'"
        title="Clients"
        to="/clients"
        :icon="icons.mdiAccountOutline"
      ></nav-menu-link>
    </v-list>

  </v-navigation-drawer>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import {
  mdiHomeOutline,
  mdiAlphaTBoxOutline,
  mdiEyeOutline,
  mdiCreditCardOutline,
  mdiTable,
  mdiFileOutline,
  mdiFormSelect,
  mdiAccountCogOutline,
  mdiMessageCogOutline,
  mdiAccountTieOutline,
  mdiAccountOutline,
} from '@mdi/js'
import NavMenuSectionTitle from './components/NavMenuSectionTitle.vue'
import NavMenuGroup from './components/NavMenuGroup.vue'
import NavMenuLink from './components/NavMenuLink.vue'

export default {
  middleware: "auth",
  components: {
    NavMenuSectionTitle,
    NavMenuGroup,
    NavMenuLink,
  },
  props: {
    isDrawerOpen: {
      type: Boolean,
      default: null,
    },
  },
  setup() {
    return {

      icons: {
        mdiHomeOutline,
        mdiAlphaTBoxOutline,
        mdiEyeOutline,
        mdiCreditCardOutline,
        mdiTable,
        mdiFileOutline,
        mdiFormSelect,
        mdiAccountCogOutline,
        mdiMessageCogOutline,
        mdiAccountTieOutline,
        mdiAccountOutline
      },
    }
  },
  computed: {
    currentUser(){
      return this.$auth.user
    },
  }
}
</script>

<style lang="scss" scoped>
@import '~/plugins/vuetify/default-preset/preset/overrides.scss';

.app-title {
  font-size: 1.25rem;
  font-weight: 700;
  font-stretch: normal;
  font-style: normal;
  line-height: normal;
  letter-spacing: 0.3px;
}

// ? Adjust this `translateX` value to keep logo in center when vertical nav menu is collapsed (Value depends on your logo)
.app-logo {
  transition: all 0.18s ease-in-out;
  .v-navigation-drawer--mini-variant & {
    transform: translateX(-4px);
  }
}

@include theme(app-navigation-menu) using ($material) {
  background-color: map-deep-get($material, 'background');
}

.app-navigation-menu {
  .v-list-item {
    &.vertical-nav-menu-link {
      ::v-deep .v-list-item__icon {
        .v-icon {
          transition: none !important;
        }
      }
    }
  }
}

.v-list-item--active {
  background-color: red;
}

</style>
