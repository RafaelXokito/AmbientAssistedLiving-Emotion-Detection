<template>
  <div class="auth-wrapper auth-v1">
    <div class="auth-inner">
      <v-card class="auth-card">
        <!-- logo -->
        <v-card-title class="d-flex align-center justify-center py-7">
          <nuxt-link
            to="/login"
            class="d-flex align-center"
          >
            <v-img
              :src="require('@/assets/images/logos/logo.png')"
              max-height="300px"
              max-width="300px"
              alt="logo"
              contain
              class="me-3 "
            ></v-img>

          </nuxt-link>
        </v-card-title>

        <!-- title -->
        <v-card-text>
          <p class="text-2xl font-weight-semibold text--primary mb-2">
            Seja bem-vindo à plataforma SmartEmotion! 👋🏻
          </p>
          <p class="mb-2">
            Por favor inicie sessão com a sua conta
          </p>
        </v-card-text>

        <!-- login form -->
        <v-card-text>
          <v-form @submit="onSubmit" @reset.prevent="onReset">
            <v-text-field
              v-model="form.email"
              outlined
              label="Email"
              placeholder="Insira o seu email"
              hide-details
              class="mb-3"
              required
            ></v-text-field>

            <v-text-field
              v-model="form.password"
              outlined
              :type="isPasswordVisible ? 'text' : 'password'"
              label="Palavra-passe"
              placeholder="********"
              :append-icon="isPasswordVisible ? icons.mdiEyeOffOutline : icons.mdiEyeOutline"
              hide-details
              @click:append="isPasswordVisible = !isPasswordVisible"
              required
            ></v-text-field>

            <div class="d-flex align-center justify-space-between flex-wrap">
              <v-checkbox
                label="Guardar credenciais"
                hide-details
                class="me-3 mt-1"
              >
              </v-checkbox>

              <!-- forgot link -->
              <a
                href="javascript:void(0)"
                class="mt-1"
              >
                Esqueceu-se da palavra-passe?
              </a>
            </div>

            <v-btn
              type="submit"
              block
              color="primary"
              class="mt-6"
            >
              Iniciar sessão
            </v-btn>
          </v-form>
        </v-card-text>

        <!-- divider -->
        <v-card-text class="d-flex align-center mt-2">
          <v-divider></v-divider>
          <span class="mx-5">Ou</span>
          <v-divider></v-divider>
        </v-card-text>

        <!-- social links -->
        <v-card-actions class="d-flex justify-center">
          <v-btn
            v-for="link in socialLink"
            :key="link.icon"
            icon
            class="ms-1"
          >
            <v-icon :color="$vuetify.theme.dark ? link.colorInDark : link.color">
              {{ link.icon }}
            </v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </div>

    <!-- background triangle shape  -->
    <img
      class="auth-mask-bg"
      height="173"
      :src="require(`@/assets/images/misc/mask-${$vuetify.theme.dark ? 'dark':'light'}.png`)"
    >

    <!-- tree -->
    <v-img
      class="auth-tree"
      width="247"
      height="185"
      src="@/assets/images/misc/tree.png"
    ></v-img>

    <!-- tree  -->
    <v-img
      class="auth-tree-3"
      width="377"
      height="289"
      src="@/assets/images/misc/tree-3.png"
    ></v-img>

    <v-dialog
      v-model="dialog"
      persistent
      max-width="500"
    >
      <v-card>
        <v-card-title class="text-h5">
          Ative a sua conta
        </v-card-title>
        <v-card-text>Concorda que os seus dados sejam utilizados para fins de investigação com o objetivo de melhorar a experiência do utilizador?</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="red darken-1"
            text
            @click="activateAccount(false)"
          >
            Discordo
          </v-btn>
          <v-btn
            color="red darken-1"
            text
            @click="activateAccount(true)"
          >
            Concordo
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
// eslint-disable-next-line object-curly-newline
import { mdiFacebook, mdiTwitter, mdiGithub, mdiGoogle, mdiEyeOutline, mdiEyeOffOutline } from '@mdi/js'
import { ref } from '@vue/composition-api'

export default {
  setup() {
    const isPasswordVisible = ref(false)
    const socialLink = [
      {
        icon: mdiFacebook,
        color: '#4267b2',
        colorInDark: '#4267b2',
      },
      {
        icon: mdiTwitter,
        color: '#1da1f2',
        colorInDark: '#1da1f2',
      },
      {
        icon: mdiGithub,
        color: '#272727',
        colorInDark: '#fff',
      },
      {
        icon: mdiGoogle,
        color: '#db4437',
        colorInDark: '#db4437',
      },
    ]

    return {
      isPasswordVisible,
      socialLink,

      icons: {
        mdiEyeOutline,
        mdiEyeOffOutline,
      },
      form: {
        email: '',
        password: ''
      },
      dialog: false
    }
  },
  methods: {
    async onSubmit(event) {
      event.preventDefault()
      await this.$auth.loginWith('local', {
        data: {
          email: this.form.email,
          password: this.form.password
        }
      }).then(e => {
        this.$axios.defaults.headers.common = {Authorization: `${e.data.token_type} ${e.data.access_token}`}
        this.socket = this.$nuxtSocket({ persist: 'mySocket'})
        if(this.$auth.user.scope === "Client"){
          this.socket.emit("logged_in", {"username": this.$auth.user.id.toString(), "userType": "C"})
        }else{
          this.socket.emit("logged_in", {"username": this.$auth.user.id.toString(), "userType": "A"})
        }
        this.$router.go(-1)
      }).catch(e => {
        if (e.response && e.response.data)
          this.$toast.error('Desculpe, não foi possível iniciar sessão.').goAway(3000)
        else if (e.response && e.response.data && e.response.data.error.includes("not activated")) {
          this.$toast.error('Desculpe, não foi possível iniciar sessão. Ative a sua conta primeiro.').goAway(3000)
          this.dialog = true
        } else {
          this.$toast.error('Sorry, you cant login. Ensure your credentials are correct').goAway(3000)
        }
      })
    },
    onReset() {
      // Reset our form values
      this.form.email = ''
      this.form.password = ''
      this.$nextTick(() => {
        this.show = true
      })
    },
    activateAccount(agree){
      if (agree) {
        this.$axios.$patch("/api/auth/activateClient", {
            email: this.form.email,
            password: this.form.password
          }).then(() => {
          this.$auth.loginWith('local', {
            data: {
              email: this.form.email,
              password: this.form.password
            }
          }).then(e => {
            this.$axios.defaults.headers.common = {Authorization: `${e.data.type} ${e.data.token}`}
            this.socket = this.$nuxtSocket({ persist: 'mySocket'})
            if(this.$auth.user.scope === "Client"){
              this.socket.emit("logged_in", {"username": this.$auth.user.id.toString(), "userType": "C"})
            }else{
              this.socket.emit("logged_in", {"username": this.$auth.user.id.toString(), "userType": "A"})
            }
            this.$router.go(-1)
          })
        })
      }
      this.dialog = false
    }
  },
}
</script>

<style lang="scss">
@import '~@/plugins/vuetify/default-preset/preset/pages/auth.scss';
</style>
