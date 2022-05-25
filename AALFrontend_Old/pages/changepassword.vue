<template>
  <div>
    <navbar/>
    <b-container class="text-center pt-5">
      <b-form @submit.prevent="onSubmit">
      <b-form-group
        id="input-group-1"
        label="Current Password"
        label-for="input-1"
      >
        <b-form-input
          id="input-1"
          v-model="form.oldPassword"
          type="password"
          name="password"
          autocomplete="current-password"
          placeholder="****"
          :state="oldPasswordState"
          aria-describedby="input-old-feedback"
          required
        ></b-form-input>
        <b-form-invalid-feedback id="input-old-feedback">
          {{oldPasswordErr}}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group id="input-group-2" label="New Password" label-for="input-2">
        <b-form-input
          id="input-2"
          v-model="form.newPassword"
          type="password"
          name="password"
          autocomplete="new-password"
          placeholder="****"
          :state="newPasswordState"
          aria-describedby="input-new-feedback"
          required
        ></b-form-input>
        <b-form-invalid-feedback id="input-new-feedback">
          {{newPasswordErr}}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-button type="submit" variant="primary">Submit</b-button>
    </b-form>
    </b-container>
  </div>
</template>

<script>
import navbar from '~/components/utils/NavBar.vue'
export default {
  middleware: ('auth'),
  components: {
    navbar
  },
  data() {
    return {
      errors: [],
      form: {
        id: '',
        oldPassword: '',
        newPassword: '',
      },
      oldPasswordErr: '',
      newPasswordErr: '',
    }
  },
  computed: {
    currentUser(){
      return this.$auth.user
    },
    oldPasswordState(){
      if (this.form.oldPassword == null || this.form.oldPassword === '') {
        return null
      }
      if (this.form.oldPassword.length < 6) {
        this.oldPasswordErr = "Password need to contains at least 4 characters!"
        return false
      }
      return true
    },
    newPasswordState(){
      if (this.form.newPassword == null || this.form.newPassword === '') {
        return null
      }
      if (this.form.newPassword.length < 4) {
        this.newPasswordErr = "Password need to contains at least 4 characters!"
        return false
      }
      return true
    },
    isFormValid() {
      return this.oldPasswordState && this.newPasswordState
    }
  },
  methods: {
    showErrorMessage(err) {
      if (err.response) {
        this.$toast.error('ERROR: ' + err.response.data).goAway(3000);
      }
      else {
        this.$toast.error(err).goAway(3000);
      }
    },
    onSubmit(){
      if (!this.isFormValid) {
        this.showErrorMessage("Fix the errors before submitting")
        return;
      }

      this.$axios
        .$patch('/api/auth/updatepassword', this.form)
        .then(() => {
          this.$toast.success('Password updated').goAway(3000);
          this.form.oldPassword = ""
          this.form.newPassword = ""
          this.$router.back()
        })
        .catch((err)=>{
          this.showErrorMessage(err)
          this.form.oldPassword = ""
          this.form.newPassword = ""
        });
    },
  },
}
</script>

<style>
footer {
  flex-shrink: 0;
	height: 50px;
  text-align: center;
}
</style>
