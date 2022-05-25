require('dotenv').config({ path: `.env.${process.env.NODE_ENV}` })

export default {
  // Target: https://go.nuxtjs.dev/config-target
  target: 'static',

  ssr: false,

  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    titleTemplate: '%s - Politécnico de Leiria',
    title: 'AALFrontend',
    htmlAttrs: {
      lang: 'en',
    },
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: '' },
      { name: 'format-detection', content: 'telephone=no' },
    ],
    link: [{ rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }],
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  css: [
    '~/plugins/vuetify/default-preset/preset/overrides.scss',
    '~/assets/scss/styles.scss',
    '~/assets/scss/variables.scss',
  ],

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [
    { src: '~/plugins/vuetify.js', mode: 'client' },
    { src: '~/plugins/vue-composition-api.js', mode: 'client' },
    { src: '~/plugins/vue-apexchart.js', ssr: false },
  ],

  // Auto import components: https://go.nuxtjs.dev/config-components
  components: true,

  // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
  buildModules: [
    //'@nuxtjs/eslint-module',
    '@nuxtjs/dotenv',
    '@nuxtjs/vuetify',
  ],

  // Modules: https://go.nuxtjs.dev/config-modules
  modules: [
    // https://go.nuxtjs.dev/axios
    '@nuxtjs/axios',
    "@nuxtjs/toast",
    "@nuxtjs/auth",
    'nuxt-highcharts',
  ],

  auth: {
    redirect: {
      login: "/login",
      logout: "/login",
      home: "/",
    },
    watchLoggedIn: true,
    strategies: {
      local: {
        endpoints: {
          login: {
            url: "/api/auth/login",
            method: "post",
            propertyName: "token",
          },
          logout: false,
          user: {
            url: "/api/auth/user",
            method: "get",
            propertyName: "",
          },
        },
      },
    },
  },

  router: {
    middleware: ["auth"],
  },

  toast: {
    position: "top-center",
    register: [
      // Register custom toasts
      {
        name: "my-error",
        message: "Oops...Something went wrong",
        options: {
          type: "error",
        },
      },
    ],
  },

  highcharts: {
    /* module options */
  },

  // Axios module configuration: https://go.nuxtjs.dev/config-axios
  axios: {
    proxy: false, //TODO se estiveres a dar deploy e der erro mete esta opção a false :)
    credentials: true,
  },

  proxy: {
    "/api/": {
      target: process.env.API_URL,
      pathRewrite: {
        "^/api/": "",
      },
    },
  },

  // Vuetify module configuration: https://go.nuxtjs.dev/config-vuetify
  vuetify: {},

  // Build Configuration: https://go.nuxtjs.dev/config-build
  build: {},
}
