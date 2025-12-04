<template>
  <div>
    <div class="modal fade" id="add-edit-dept" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-title modal-title-font" id="exampleModalLabel">{{ title }}</div>
          </div>
          <ValidationObserver v-slot="{ handleSubmit }">
            <form class="form-horizontal" id="form" @submit.prevent="handleSubmit(onSubmit)">
              <div class="modal-body">
                <div class="row">
                  <div class="col-12 col-md-6">
                    <ValidationProvider name="Staff ID" mode="eager" rules="required" v-slot="{ errors }">
                      <div class="form-group">
                        <label for="name">Staff ID</label>
                        <input type="text" class="form-control" :class="{'error-border': errors[0]}" id="staff-id"
                               v-model="staffId" name="staff-id" placeholder="Staff ID" :disabled="actionType==='edit'">
                        <span class="error-message"> {{ errors[0] }}</span>
                      </div>
                    </ValidationProvider>
                  </div>
                  <div class="col-12 col-md-6">
                    <ValidationProvider name="Name" mode="eager" rules="required" v-slot="{ errors }">
                      <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" :class="{'error-border': errors[0]}" id="name"
                               v-model="name" name="name" placeholder="Name">
                        <span class="error-message"> {{ errors[0] }}</span>
                      </div>
                    </ValidationProvider>
                  </div>
                  <div class="col-12 col-md-6">
                    <ValidationProvider name="Designation" mode="eager" rules="required" v-slot="{ errors }">
                      <div class="form-group">
                        <label for="name">Designation</label>
                        <input type="text" class="form-control" :class="{'error-border': errors[0]}" id="designation"
                               v-model="designation" name="designation" placeholder="Designation">
                        <span class="error-message"> {{ errors[0] }}</span>
                      </div>
                    </ValidationProvider>
                  </div>
                  <div class="col-12 col-md-6">
                    <ValidationProvider name="Mobile" mode="eager" rules="required" v-slot="{ errors }">
                      <div class="form-group">
                        <label for="name">Mobile</label>
                        <input type="text" class="form-control" :class="{'error-border': errors[0]}" id="mobile"
                               v-model="mobile" name="mobile" placeholder="Mobile">
                        <span class="error-message"> {{ errors[0] }}</span>
                      </div>
                    </ValidationProvider>
                  </div>
                  <div class="col-12 col-md-6">
                    <ValidationProvider name="Password" mode="eager" :rules="`${type='edit'?'':'required|min:6'}`"
                                        v-slot="{ errors }">
                      <div class="form-group">
                        <label for="name">Password</label>
                        <input type="password" class="form-control" :class="{'error-border': errors[0]}" id="password"
                               v-model="password" name="password" placeholder="Password">
                        <span class="error-message"> {{ errors[0] }}</span>
                      </div>
                    </ValidationProvider>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <submit-form :name="buttonText"/>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </form>
          </ValidationObserver>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import {bus} from "../../app";
import {Common} from "../../mixins/common";
import {mapGetters} from "vuex";

export default {
  mixins: [Common],
  data() {
    return {
      title: '',
      name: '',
      designation: '',
      staffId: '',
      buttonText: "",
      mobile: '',
      email: '',
      password: '',
      outlet: '',
      userType: '',
      type: '',
      actionType: ''
    }
  },
  computed: {
    ...mapGetters(['outlets']),
  },
  mounted() {
    bus.$on('add-edit-user', (row) => {
      if (row) {
        console.log(row)
        this.title = 'Update User';
        this.buttonText = "Save";
        this.name = row.Name;
        this.designation = row.Designation;
        this.staffId = row.UserID;
        this.mobile = row.Mobile;
        this.email = row.Email;
        this.outlet = row.OutletCode;
        this.userType = row.UserType;
        this.password = '';
        this.actionType = 'edit'
      } else {
        this.title = 'Add User';
        this.buttonText = "Add";
        this.name = '';
        this.designation = '';
        this.staffId = '';
        this.mobile = '';
        this.email = '';
        this.password = '';
        this.outlet = '';
        this.userType = '';
        this.actionType = 'add'
      }
      $("#add-edit-dept").modal("toggle");
    })
  },
  methods: {
    onSubmit() {
      this.$store.commit('submitButtonLoadingStatus', true);
      let url = '';
      if (this.actionType === 'add') url = 'add-user';
      else url = 'update-user'
      this.axiosPost(url, {
        name: this.name,
        designation: this.designation,
        UserID: this.staffId,
        mobile: this.mobile,
        email: this.email,
        password: this.password,
        outlet: this.outlet,
        userType: this.userType
      }, (response) => {
        this.successNoti(response.message);
        $("#add-edit-dept").modal("toggle");
        bus.$emit('refresh-datatable');
        this.$store.commit('submitButtonLoadingStatus', false);
      }, (error) => {
        this.errorNoti(error);
        this.$store.commit('submitButtonLoadingStatus', false);
      })
    }
  }
}
</script>
