<template>
  <div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
      <div id="sidebar-menu">
        <ul class="metismenu" id="side-menu">
          <li class="menu-title">Main</li>
          <li>
            <router-link :to="{name:'Dashboard'}" class="waves-effect"><i class="ti-home"></i>
              <span>Home</span></router-link>
          </li>
          <li v-for="(menu,index) in menus" :key="index">
            <router-link :to="menu.Link" class="waves-effect">
              <i :class="menu.MenuIcon"></i>
              <span>{{ menu.MenuName }}
              </span>
            </router-link>
          </li>
          <li>
            <a href="javascript:" class="waves-effect text-danger" @click="logout">
              <i class="mdi mdi-power text-danger"></i>Logout
            </a>
          </li>
        </ul>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>
</template>
<script>
import {Common} from "../../mixins/common";

export default {
  mixins: [Common],
  data() {
    return {
      menus: []
    }
  },
  mounted() {
    this.getData();
    // window.addEventListener('beforeunload', function (e) {
    //   e.preventDefault();
    //   localStorage.setItem("token", "");
    // });
  },
  methods: {
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

    },
    getData() {
      this.axiosGet('app-supporting-data', (response) => {
        this.menus = response.menu;
        this.$store.commit('supportingData', response);
        setTimeout(() => {
          $("#side-menu").metisMenu();
        })
      }, (error) => {
        this.errorNoti(error)
      })
    }
  }
}
</script>
