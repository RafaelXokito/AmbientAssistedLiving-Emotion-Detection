<template>
    <div class="vue-tempalte">
        <b-form @submit="onSubmit" @reset.prevent="onReset">

            <div class="mb-5 text-center">
              <b-img src="../../static/AAL_logo.jpg" fluid alt="Responsive image"></b-img>
            </div>

            <div class="form-group">
                <label>Email address</label>
                <input type="email" class="form-control form-control-lg" v-model="form.email" required/>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control form-control-lg" v-model="form.password" required/>
            </div>

            <button type="submit" class="btn btn-dark btn-lg btn-block">Sign In</button>

        </b-form>
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
        created() {
          if (this.$auth.loggedIn) {
            this.$router.push({ name: 'dashboard' });
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
              this.$router.push({ name: 'dashboard' });
              this.$axios.defaults.headers.common = { Authorization: `${e.data.type} ${e.data.token}` };
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
  img{
    height: 150px;
  }
</style>
