<template>
  <div class="container">
    <div class="row">
      <div class="col-lg-4 col-md-6 col-sm-8 mx-auto">
        <h1>Sign in</h1>
        <form @submit.prevent="onSubmit">
          <b-form-group class="text-center">
            <label>Email:</label>
            <b-input
              name="email"
              placeholder="Your email"
              v-model="email"
              type="email"
              required
            />
          </b-form-group>
          <b-form-group class="text-center">
            <label>Password:</label>
            <b-input
              type="password"
              placeholder="Your password"
              v-model="password"
              required
            />
          </b-form-group>
          <b-button type="submit" class="btn-dark">Submit</b-button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
    auth: false,
  data() {
    return {
      email: null,
      password: null,
    };
  },
   methods: {
    onSubmit() {
      let promise = this.$auth.loginWith("local", {
        data: {
          email: this.email,
          password: this.password,
        },
      });
      promise.then(() => {
       this.$toast.success("You are logged in!").goAway(3000);
        // check if the user $auth.user object is set
        // TODO redirect based on the user role
        // eg:
        /*if (this.$auth.user.groups.includes("Patient")) {
          this.$router.push("/dashboard");
        } else if (this.$auth.user.groups.includes("HealthcareProfessional")) {
          this.$router.push("/dashboardHealthcareProfessionals");
        } else if (this.$auth.user.groups.includes("Administrator")){
          this.$router.push("/dashboardAdministrators");
        }*/
      });
      promise.catch(() => {
        this.$toast
          .error("Sorry, you cant login. Ensure your credentials are correct")
          .goAway(3000);
      });
    }
   }

};
</script>

<style>
b-input {
  margin-bottom: 20px;
}
</style>
