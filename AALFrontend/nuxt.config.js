
require('dotenv').config({ path: `.env.${process.env.NODE_ENV}` })

export default {
  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    title: "AALFrontend",
    htmlAttrs: {
      lang: "en",
    },
    meta: [
      { charset: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { hid: "description", name: "description", content: "" },
      { name: "format-detection", content: "telephone=no" },
    ],
    link: [{ rel: "icon", type: "image/x-icon", href: "/favicon.ico" }],
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  css: [
    '@/assets/css/main.css',
  ],

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [],

  // Auto import components: https://go.nuxtjs.dev/config-components
  components: true,

  // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
  buildModules: [
    '@nuxtjs/dotenv',
    '@nuxt/postcss8'
  ],


  // Modules: https://go.nuxtjs.dev/config-modules
  modules: [
    // https://go.nuxtjs.dev/bootstrap
    "bootstrap-vue/nuxt",
    // https://go.nuxtjs.dev/axios
    "@nuxtjs/axios",
    "@nuxtjs/auth",
    "@nuxtjs/toast",
    'nuxt-highcharts',
  ],

  auth: {
    redirect: {
      login: "/",
      logout: "/",
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

  bootstrapVue: {
    icons: true
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

  // Axios module configuration: https://go.nuxtjs.dev/config-axios
  axios: {
    proxy: process.env.PROXY, //TODO se estiveres a dar deploy e der erro mete esta opção a false :)
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

  // Build Configuration: https://go.nuxtjs.dev/config-build
  build: {
    postcss: {
      plugins: {
        tailwindcss: {},
        autoprefixer: {},
      },
    },
  },
};
