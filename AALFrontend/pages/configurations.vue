<template>
  <div>
    <navbar />
    <b-container>
      <b-card no-body class="mt-5">
        <b-tabs card>
          <b-tab title="Notificações" active>
            <div>

            </div>
          </b-tab>
        </b-tabs>
      </b-card>
    </b-container>
  </div>
</template>

<script>
import navbar from "~/components/utils/NavBar";

export default {
  name: "configurations",
  middleware: "auth",
  components: {
    navbar,
  },
}
</script>

<style scoped>

</style>
