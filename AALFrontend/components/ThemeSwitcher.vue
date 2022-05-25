<template>
  <v-fade-transition mode="out-in">
    <v-icon
      :key="$vuetify.theme.dark"
      @click="theme_switch"
    >
      {{ $vuetify.theme.dark ? icons.mdiWeatherSunny : icons.mdiWeatherNight }}
    </v-icon>
  </v-fade-transition>
</template>

<script>
import { mdiWeatherNight, mdiWeatherSunny } from '@mdi/js'
import darkUnica from "highcharts/themes/dark-unica"
import Highcharts from "highcharts"


export default {
  created() {
    const theme = localStorage.getItem("darkTheme")

    // Check if the user has set the theme state before
    if (theme) {
      if (theme === "true") {
        this.$vuetify.theme.dark = true
      } else {
        this.$vuetify.theme.dark = false
      }
    } else if (
      // eslint-disable-next-line nuxt/no-globals-in-created
      window.matchMedia &&
      // eslint-disable-next-line nuxt/no-globals-in-created
      window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
      this.$vuetify.theme.dark = true
      localStorage.setItem(
        "darkTheme",
        this.$vuetify.theme.dark.toString()
      )
    }

    if (this.$vuetify.theme.dark)
      darkUnica(Highcharts)
  },
  methods: {
    theme_switch(){
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark
      localStorage.setItem("darkTheme", this.$vuetify.theme.dark.toString())
    }
  },
  setup() {
    return {
      icons: {
        mdiWeatherNight,
        mdiWeatherSunny,
      },
    }
  },
}
</script>

<style>
</style>
