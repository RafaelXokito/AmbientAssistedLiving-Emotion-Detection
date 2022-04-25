<template>
<div>
  <b-container>
    <!-- Main -->
      <b-row class="vertical-center mt-5" style="margin-bottom: 50px">
        <b-col>
          <div class="inner-block">
            <login />
          </div>
        </b-col>
      </b-row>
  </b-container>
  <footer style="height: 150px">
    <div class="mt-5 text-center">
      <p class="align-middle">AAL-Emotion designed and developed for school and research propose</p>
      <p class="align-middle">
        <a href="https://www.linkedin.com/in/rafaelmendespereira/">Rafael Pereira</a>,
        <a href="https://www.linkedin.com/in/carla-mendes-5b3586233/">Carla Mendes</a>,
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

.inner-block {
  width: 450px;
  margin: auto;
  background: #ffffff;
  box-shadow: 0px 14px 80px rgba(34, 35, 58, 0.2);
  padding: 40px 55px 45px 55px;
  border-radius: 15px;
  transition: all 0.3s;
}

.vertical-center .form-control:focus {
  border-color: #2554ff;
  box-shadow: none;
}

.vertical-center h3 {
  text-align: center;
  margin: 0;
  line-height: 1;
  padding-bottom: 20px;
}

label {
  font-weight: 500;
}

.forgot-password,
.forgot-password a {
  text-align: right;
  font-size: 13px;
  padding-top: 10px;
  color: #7a7a7a;
  margin: 0;
}

.forgot-password a {
  color: #2554ff;
}

.social-icons {
  text-align: center;
  font-family: "Open Sans";
  font-weight: 300;
  font-size: 1.5em;
  color: #222222;
}

.social-icons ul {
  list-style: none;
  margin: 0;
  padding: 0;
}
.social-icons ul li {
  display: inline-block;
  zoom: 1;
  width: 65px;
  vertical-align: middle;
  border: 1px solid #e3e8f9;
  font-size: 15px;
  height: 40px;
  line-height: 40px;
  margin-right: 5px;
  background: #f4f6ff;
}

.social-icons ul li a {
  display: block;
  font-size: 1.4em;
  margin: 0 5px;
  text-decoration: none;
}
.social-icons ul li a i {
  -webkit-transition: all 0.2s ease-in;
  -moz-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  -ms-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
}

.social-icons ul li a:focus i,
.social-icons ul li a:active i {
  transition: none;
  color: #222222;
}

.container {
  padding-bottom: 50px !important;
}

footer {
  flex-shrink: 0;
	height: 50px;
  text-align: center;
}
</style>
