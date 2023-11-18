<template>
  <v-card id="account-setting-card">
    <!-- tabs -->
    <v-tabs
      v-model="tab"
      show-arrows
    >
      <v-tab
        v-for="tab in tabs"
        :key="tab.icon"
      >
        <v-icon
          size="20"
          class="me-3"
        >
          {{ tab.icon }}
        </v-icon>
        <span>{{ tab.title }}</span>
      </v-tab>
    </v-tabs>

    <!-- tabs item -->
    <v-tabs-items v-model="tab">
      <v-tab-item>
        <account-settings-account :account-data="accountSettingData.account"></account-settings-account>
      </v-tab-item>

      <v-tab-item>
        <account-settings-security></account-settings-security>
      </v-tab-item>

      <v-tab-item>
        <account-settings-info :information-data="accountSettingData.information"></account-settings-info>
      </v-tab-item>
    </v-tabs-items>
  </v-card>
</template>

<script>
import { mdiAccountOutline, mdiLockOpenOutline, mdiInformationOutline } from '@mdi/js'
import { ref } from '@vue/composition-api'

// demos
import AccountSettingsAccount from '@/components/account-settings/AccountSettingsAccount.vue'
import AccountSettingsSecurity from '@/components/account-settings/AccountSettingsSecurity.vue'
import AccountSettingsInfo from '@/components/account-settings/AccountSettingsInfo.vue'

export default {
  components: {
    AccountSettingsAccount,
    AccountSettingsSecurity,
    AccountSettingsInfo,
  },
  setup() {
    const tab = ref('')

    // tabs
    const tabs = [
      { title: 'Conta', icon: mdiAccountOutline },
      { title: 'Segurança', icon: mdiLockOpenOutline },
      { title: 'Iformações', icon: mdiInformationOutline },
    ]

    // account settings data
    const accountSettingData = {
      account: {
        avatarImg: require('@/assets/images/avatars/1.png'),
        name: 'john Doe',
        email: 'johnDoe@example.com',
        role: 'Client',
        contact: '9XXXXXXXX',
        birthday: Date.now().toString(),
      },
      information: {
        birthday: 'Fevereiro 22, 1995',
        phone: '',
        gender: 'male',
      },
    }

    return {
      tab,
      tabs,
      accountSettingData,
      icons: {
        mdiAccountOutline,
        mdiLockOpenOutline,
        mdiInformationOutline,
      },
    }
  },
  created() {
    this.accountSettingData.account.role = this.currentUser.scope
    this.accountSettingData.account.name = this.currentUser.name
    this.accountSettingData.account.email = this.currentUser.email
    if (this.currentUser.scope === 'Client'){

    }
  },
  computed: {
    currentUser(){
      return this.$auth.user
    }
  },
}
</script>
