<template>
<div>
  <b-container>
    <!-- Main -->
    <login />
  </b-container>
  <footer style="height: 150px">
    <div class="mt-5 text-center">
      <p class="align-middle">AAL-Emotion designed and developed for school and research propose</p>
      <p class="align-middle">
        <a href="https://www.linkedin.com/in/rafaelmendespereira/">Rafael Pereira</a>,
        <a href="https://www.linkedin.com/in/carla-mendes-5b3586233/">Carla Mendes</a>
      </p>
    </div>
  </footer>
</div>
</template>

<script>
import Login from '../components/auth/Login.vue';
export default {
  components: { Login },};
</script>

<style>
footer {
  flex-shrink: 0;
	height: 50px;
  text-align: center;
}
</style>
