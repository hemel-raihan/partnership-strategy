<template>
  <div class="topbar">
    <div class="topbar-left">
      <router-link :to="{name:'Dashboard'}" class="logo">
        <span><img style="width: 100%;height: 75px" :src="`${mainOrigin}assets/images/stockouttracker.jpg`"
                   alt="logo"/> </span><i><img
          :src="`${mainOrigin}assets/images/mini_logo.png`" style="height: 50px ;width: 100%" alt=""/></i>
      </router-link>
    </div>
    <nav class="navbar-custom">
      <ul class="navbar-right list-inline float-right mb-0">
        <li class="dropdown notification-list list-inline-item">
          <div class="dropdown notification-list nav-pro-img">
            <a class="dropdown-toggle nav-link arrow-none waves-effect nav-user" data-toggle="dropdown"
               href="#" role="button" aria-haspopup="false" aria-expanded="false">
              <img :src="image" alt="user"
                   class="rounded-circle"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-dropdown">
              <!-- item-->
              <router-link class="dropdown-item" :to="`${mainOrigin}profile`"><i
                  class="mdi mdi-account-circle m-r-5"></i>
                Profile
              </router-link>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" @click="logout" href="#"><i
                  class="mdi mdi-power text-danger"></i>
                Logout</a>
            </div>
          </div>
        </li>
      </ul>
      <ul class="list-inline menu-left mb-0">
        <li class="float-left">
          <button @click="toggleSidebar" class="button-menu-mobile open-left waves-effect"><i
              class="mdi mdi-menu"></i></button>
        </li>
      </ul>
    </nav>
  </div>
</template>
<script>
import {Common} from '../../mixins/common'

export default {
  mixins: [Common],
  data() {
    return {
      image: ''
    }
  },
  created() {
    this.getData();
  },
  methods: {
    toggleSidebar(e) {
      $("body").toggleClass("enlarged")
    },
    getData() {
      this.axiosPost('me', {}, (response) => {
        this.image = `${this.mainOrigin}uploads/${response.Avatar}`;
        this.$store.commit('me', response);
      }, (error) => {
        this.errorNoti(error);
      });
    },
    logout() {
      this.axiosPost("logout", {}, (response) => {
            localStorage.setItem("token", "");
            this.$router.push(this.mainOrigin + "login");
            this.successNoti(response.message)
          },
          (error) => {
            this.errorNoti(error);
          }
      );

    }
  }
}
</script>
