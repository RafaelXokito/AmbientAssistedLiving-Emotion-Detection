<template>
  <div>
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <img class="mx-auto h-12 w-auto rounded-lg" src="../../static/logo.png"
               alt="Workflow">
          <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Sign in to your account</h2>
        </div>
        <b-form @submit="onSubmit" @reset.prevent="onReset">
          <div class="rounded-md shadow-sm -space-y-px mb-3">
            <div>
              <label for="email-address" class="sr-only">Email address</label>
              <input id="email-address" name="email" type="email" autocomplete="email" required
                     class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                     placeholder="Email address"
                     v-model="form.email">
            </div>
            <div>
              <label for="password" class="sr-only">Password</label>
              <input id="password" name="password" type="password" autocomplete="current-password" required
                     class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                     placeholder="Password"
                     v-model="form.password">
            </div>
          </div>


          <div>
            <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <!-- Heroicon name: solid/lock-closed -->
              <svg class="h-5 w-5 text-red-500 group-hover:text-red-400" xmlns="http://www.w3.org/2000/svg"
                   vie wBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                      clip-rule="evenodd"/>
              </svg>
            </span>
              Sign in
            </button>
          </div>
        </b-form>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
    }
  },
  computed: {
    currentUser(){
      return this.$auth.loggedIn
    }
  },
  created() {
    if (this.$auth.loggedIn) {

      if (this.$auth.$storage.getUniversal('redirect')) {
        this.$router.replace(this.$auth.$storage.getUniversal('redirect'))
        this.$auth.$storage.removeUniversal('redirect')
        return;
      }

      this.$router.replace('/')
      return
    }
  },
  methods: {
    async onSubmit(event) {
      event.preventDefault()
      //alert(JSON.stringify(this.form))
      const response = await this.$auth.loginWith('local', {
        data: {
          email: this.form.email,
          password: this.form.password
        }
      }).then((e) => {
        this.$router.push({name: 'dashboard'});
        this.$axios.defaults.headers.common = {Authorization: `${e.data.type} ${e.data.token}`};
      }).catch(() => {
        this.$toast.error('Sorry, you cant login. Ensure your credentials are correct').goAway(3000)
      });
    },
    onReset() {
      // Reset our form values
      this.form.email = ''
      this.form.password = ''
      this.$nextTick(() => {
        this.show = true
      })
    }
  },
}
</script>

<style scoped>
img {
  height: 250px;
}
</style>
