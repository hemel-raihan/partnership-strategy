<template>
    <div class="content">
      <div class="container-fluid">
        <breadcrumb :options="['Outlet Permissions']"/>
        <div class="row">
          <div class="col-xl-12">
            <div class="card">
              <div class="card-body">
                <div class="row">
                  <div class="col-6">
                    <ValidationObserver v-slot="{ handleSubmit }">
                      <form class="form-horizontal" @submit.prevent="handleSubmit(onSubmit)">
                        <div class="form-group">
                          <!--                        <label for="user">User</label>-->
                          <model-select :options="users" v-model="selectedUser" placeholder="Select User" id="user"
                                        name="user"/>
                        </div>
                        <div class="form-group">
                          <submit-form-2 name="Submit"/>
                        </div>
                        <!--                        <div class="form-group">-->
                        <!--                          <label for="user">User</label>-->
                        <!--                          <div class="input-group">-->
                        <!--                            <select class="form-control" :class="{'error-border': errors[0]}" id="user"-->
                        <!--                                    v-model="userId" name="user">-->
                        <!--                              <option v-for="(user,index) in users" :key="index" :value="user.UserID">-->
                        <!--                                {{ `${user.UserName} - (${user.UserID}-${user.Designation})` }}-->
                        <!--                              </option>-->
                        <!--                            </select>-->
                        <!--                            <submit-form class="ml-1" name="Submit"/>-->
                        <!--                          </div>-->
                        <!--                          <span class="error-message"> {{ errors[0] }}</span>-->
                        <!--                        </div>-->
                      </form>
                    </ValidationObserver>
                  </div>
                  <div class="col-12">
                    <outlet-permissions v-if="isLoading" :treeList="treeList" :userId="userId" :permission="permission"/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  <script>
  import {Common} from "../../mixins/common";
  import {ModelSelect} from 'vue-search-select'
  
  export default {
    mixins: [Common],
    components: {
      ModelSelect
    },
    data() {
      return {
        users: [],
        userId: '',
        treeList: [],
        permission: [],
        isLoading: false,
        selectedUser: {
          value: '',
          text: ''
        },
      }
    },
    created() {
      this.getData();
    },
    methods: {
      onSubmit() {
        this.isLoading = false;
        this.permission = [];
        let UserID = this.selectedUser.value
        if (UserID) {
          this.userId = UserID;
          this.axiosGet('get-user-outlet-details/' + this.userId, (response) => {
            this.treeList = response.outlets;
            this.permission = response.userOutlet;
            this.isLoading = true;
          }, (error) => {
            this.errorNoti(error);
          })
        } else {
          this.infoAlert('No User','You are not selected any user!')
        }
      },
      getData() {
        this.axiosGet('users', (response) => {
          this.users = response.map((user) => {
            return {value: user.UserID, text: user.UserName + '(' + user.UserID + '-' + user.Designation + ')'}
          })
          //this.users = response;
        }, (error) => {
          this.errorNoti(error)
        });
      }
    }
  }
  </script>
  