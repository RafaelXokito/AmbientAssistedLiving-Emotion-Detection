<template>
  <v-card
    flat
    class="mt-5"
  >
    <v-form>
      <div class="px-3">
        <v-card-text class="pt-5">
          <v-row>
            <v-col
              cols="12"
              sm="8"
              md="6"
            >
              <!-- current password -->
              <v-text-field
                v-model="currentPassword"
                :type="isCurrentPasswordVisible ? 'text' : 'password'"
                :append-icon="isCurrentPasswordVisible ? icons.mdiEyeOffOutline:icons.mdiEyeOutline"
                label="Palavra-passe atual"
                outlined
                dense
                @click:append="isCurrentPasswordVisible = !isCurrentPasswordVisible"
              ></v-text-field>

              <!-- new password -->
              <v-text-field
                v-model="newPassword"
                :type="isNewPasswordVisible ? 'text' : 'password'"
                :append-icon="isNewPasswordVisible ? icons.mdiEyeOffOutline:icons.mdiEyeOutline"
                label="Nova palavra-passe"
                outlined
                dense
                hint="A nova palavra-passe tem de ter pelo menos 8 caracteres"
                persistent-hint
                @click:append="isNewPasswordVisible = !isNewPasswordVisible"
              ></v-text-field>

              <!-- confirm password -->
              <v-text-field
                v-model="cPassword"
                :type="isCPasswordVisible ? 'text' : 'password'"
                :append-icon="isCPasswordVisible ? icons.mdiEyeOffOutline:icons.mdiEyeOutline"
                label="Confirme a nova palavra-passe"
                outlined
                dense
                class="mt-3"
                @click:append="isCPasswordVisible = !isCPasswordVisible"
              ></v-text-field>
            </v-col>

            <v-col
              cols="12"
              sm="4"
              md="6"
              class="d-none d-sm-flex justify-center position-relative"
            >
              <v-img
                contain
                max-width="170"
                src="@/assets/images/3d-characters/pose-m-1.png"
                class="security-character"
              ></v-img>
            </v-col>
          </v-row>
        </v-card-text>
      </div>

      <!-- divider -->
      <v-divider></v-divider>

      <div class="pa-3">
        <v-card-title class="flex-nowrap">
          <v-icon class="text--primary me-3">
            {{ icons.mdiKeyOutline }}
          </v-icon>
          <span class="text-break">Autenticação por dois fatores</span>
        </v-card-title>

        <v-card-text class="two-factor-auth text-center mx-auto">
          <v-avatar
            color="primary"
            class="primary mb-4"
            rounded
          >
            <v-icon
              size="25"
              color="white"
            >
              {{ icons.mdiLockOpenOutline }}
            </v-icon>
          </v-avatar>
          <p class="text-base text--primary font-weight-semibold">
            Autenticação por dois fatores ainda não está ativada.
          </p>
          <p class="text-sm text--primary">
            A autenticação por dois factores acrescenta uma camada adicional de
            segurança à sua conta, exigindo mais do que apenas uma
            palavra-passe para iniciar sessão. Saiba mais.
          </p>
        </v-card-text>

        <!-- action buttons -->
        <v-card-text>
          <v-btn
            color="primary"
            class="me-3 mt-3"
          >
            Guardar
          </v-btn>
          <v-btn
            color="secondary"
            outlined
            class="mt-3"
          >
            Cancelar
          </v-btn>
        </v-card-text>
      </div>
    </v-form>
  </v-card>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import { mdiKeyOutline, mdiLockOpenOutline, mdiEyeOffOutline, mdiEyeOutline } from '@mdi/js'
import { ref } from '@vue/composition-api'

export default {
  setup() {
    const isCurrentPasswordVisible = ref(false)
    const isNewPasswordVisible = ref(false)
    const isCPasswordVisible = ref(false)
    const currentPassword = ref('12345678')
    const newPassword = ref('87654321')
    const cPassword = ref('87654321')

    return {
      isCurrentPasswordVisible,
      isNewPasswordVisible,
      currentPassword,
      isCPasswordVisible,
      newPassword,
      cPassword,
      icons: {
        mdiKeyOutline,
        mdiLockOpenOutline,
        mdiEyeOffOutline,
        mdiEyeOutline,
      },
    }
  },
}
</script>

<style lang="scss" scoped>
.two-factor-auth {
  max-width: 25rem;
}
.security-character {
  position: absolute;
  bottom: -0.5rem;
}
</style>
