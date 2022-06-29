<template>
  <div>
    <b-container class="text-center pt-5">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-column align-items-center text-center">
              <img v-if="currentUser.age !== 'Other'" :src="currentUser.age === 'Male' ? 'https://bootdey.com/img/Content/avatar/avatar7.png' : 'https://bootdey.com/img/Content/avatar/avatar3.png'" alt="Admin" class="rounded-circle" width="150">
              <div class="mt-3">
                <div v-if="!showEdit">
                <h4>{{currentUser.name}}</h4>
                  <p class="text-secondary mb-1">Email: {{currentUser.email}}</p>
                </div>
                <b-form v-else-if="showEdit" ref="profileForm" @submit.prevent="onSubmit">
                  <b-form-group
                    id="input-group-1"
                    label="Email address:"
                    label-for="input-1"
                  >
                    <b-form-input
                      id="input-1"
                      v-model="form.email"
                      type="email"
                      placeholder="Enter email"
                      required
                    ></b-form-input>
                  </b-form-group>

                  <b-form-group id="input-group-2" label="Your Name:" label-for="input-2">
                    <b-form-input
                      id="input-2"
                      v-model="form.name"
                      placeholder="Enter name"
                      required
                    ></b-form-input>
                  </b-form-group>
                </b-form>
                <b-button v-if="showEdit" type="submit" variant="primary" @click="onSubmit">Save</b-button>
                <b-button variant="outline-primary" @click="changeView">{{showEdit ? 'Back' : 'Edit'}}</b-button>
              </div>
            </div>
          </div>
        </div>
    </b-container>
  </div>
</template>

<script>
export default {
  middleware: ('auth'),
  data() {
    return {
      form: {
        id: '',
        email: '',
        name: '',
      },
      showEdit: false,
    }
  },
  computed: {
    currentUser(){
      return this.$auth.user
    }
  },
  mounted() {
    this.form.id = this.currentUser.id
    this.form.email = this.currentUser.email
    this.form.name = this.currentUser.name
  },
  methods: {
    showErrorMessage(err) {
      if (err.response) {
        this.$toast.error('ERROR: ' + err.response.data).goAway(3000)
      }
      else {
        this.$toast.error(err).goAway(3000)
      }
    },
    changeView(){
      this.showEdit = !this.showEdit
      this.form.email = this.currentUser.email
      this.form.name = this.currentUser.name
    },
    onSubmit(){
      this.$axios
        .$put('/api/auth/update', this.form)
        .then(() => {
          this.$auth.fetchUser()
          this.$toast.success('Profile updated').goAway(3000)
          this.showEdit = false
        })
        .catch(err=>{
          this.$toast.error('Profile was not updated').goAway(3000)
        })
    },
    isNumber(event) {
      if (!/\d/.test(event.key) && event.key !== '.') return event.preventDefault()
    }
  }
}
</script>

<style>

</style>

<style scoped>
body{
    margin-top:20px;
    color: #1a202c;
    text-align: left;
    background-color: #e2e8f0;
}
.main-body {
    padding: 15px;
}
.card {
    box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
}

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 0 solid rgba(0,0,0,.125);
    border-radius: .25rem;
}

.card-body {
    flex: 1 1 auto;
    min-height: 1px;
    padding: 1rem;
}

.gutters-sm {
    margin-right: -8px;
    margin-left: -8px;
}

.gutters-sm>.col, .gutters-sm>[class*=col-] {
    padding-right: 8px;
    padding-left: 8px;
}
.mb-3, .my-3 {
    margin-bottom: 1rem!important;
}

.bg-gray-300 {
    background-color: #e2e8f0;
}
.h-100 {
    height: 100%!important;
}
.shadow-none {
    box-shadow: none!important;
}

footer {
  flex-shrink: 0;
	height: 50px;
  text-align: center;
}
</style>
