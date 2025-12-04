<template>
    <div>
      <div class="modal fade" id="outlet-authenticate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-title modal-title-font" id="exampleModalLabel">Digital Authentication</div>
            </div>
            <ValidationObserver v-slot="{ handleSubmit }">
              <form class="form-horizontal" id="form" @submit.prevent="handleSubmit(onSubmit)">
                <div class="modal-body">
                  <div class="row">
                    <div class="col-12 col-md-12">
                      <ValidationProvider name="User Id" mode="eager" rules="required" v-slot="{ errors }">
                        <div class="form-group">
                          <label for="user-id">User ID</label>
                          <input autocomplete="off" type="text" class="form-control" :class="{'error-border': errors[0]}" id="user-id"
                                 v-model="userId" placeholder="User ID">
                          <span class="error-message"> {{ errors[0] }}</span>
                        </div>
                      </ValidationProvider>
                    </div>
                    <div class="col-12 col-md-12">
                      <ValidationProvider autocomplete="off" name="Password" mode="eager" :rules="`${type='edit'?'':'required|min:6'}`"
                                          v-slot="{ errors }">
                        <div class="form-group">
                          <label for="password">Password</label>
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
                  <button type="button" @click="authModalClose" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        userId: '',
        password: '',
        buttonText: "Submit",
        ActionType: '',
        qtyID: ''
      }
    },
    mounted() {
      bus.$on('digital-authentication', (data, qtyID) => {
        this.ActionType = data
        this.qtyID = qtyID
        $('#floatCashModal').modal({backdrop: 'static', keyboard: false})
        $("#outlet-authenticate").modal("toggle");
        this.userId = ''
        this.password = ''
        setTimeout(() =>{
          $("#user-id").focus();
          $("#user-id").select();
        },1000)
      })

      $(document).keydown((event) => {
        if(event.keyCode == 27) {      // Esc
          this.authModalClose();
        }
      });

    },
    methods: {
      onSubmit() {
        this.$store.commit('submitButtonLoadingStatus', true);
        this.axiosPost('outlet-authentication', {
            username: this.userId,
            password: this.password,
        }, (response) => {
            if(response.status == 'success'){
              if(this.ActionType == 'cash-closing'){
                bus.$emit('cash-closing-auth');
                this.$store.commit('submitButtonLoadingStatus', false);  
              }
              else{
                bus.$emit('delete-product-cart', this.ActionType, this.qtyID);
                this.$store.commit('submitButtonLoadingStatus', false);
              }
            }
            else{
                this.errorNoti(response.msg);
                this.$store.commit('submitButtonLoadingStatus', false);
            }
        }, (error) => {
          this.errorNoti(error);
          this.$store.commit('submitButtonLoadingStatus', false);
        })
      },

      authModalClose(){
        if(this.ActionType == 'cash-closing'){
          bus.$emit('auth-cancel');
          this.$store.commit('submitButtonLoadingStatus', false);  
        }
      }
    }
  }
  </script>
  