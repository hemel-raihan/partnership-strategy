<template>
  <div class="content">
    <div class="container-fluid">
      <breadcrumb :options="['Users']">
        <button class="btn btn-primary" @click="addDeptModal()">Add User</button>
      </breadcrumb>
      <advanced-datatable :options="tableOptions">
        <template slot="user" slot-scope="row">
          <span class="text-capitalize">{{ processText(row.item.UserType) }}</span>
        </template>
        <template slot="status" slot-scope="row">
          <span v-if="row.item.Status==='Y'">Active</span>
          <span v-else>Inactive</span>
        </template>
        <template slot="action" slot-scope="row">
          <a href="javascript:" @click="addDeptModal(row.item)"> <i class="ti-pencil-alt"></i></a>
        </template>
      </advanced-datatable>
    </div>
    <AddEditModal/>
  </div>
</template>
<script>
import {bus} from "../../app";
import {Common} from "../../mixins/common";
import AddEditModal from "../../components/users/AddEditModal";

export default {
  mixins: [Common],
  components:{AddEditModal},
  data() {
    return {
      tableOptions: {
        source: 'users',
        search: true,
        slots: [4, 5, 6],
        slotsName: ['user', 'status', 'action'],
        sortable: [2],
        pages: [20, 50, 100],
        addHeader: ['Action'],
      },
    }
  },
  methods: {
    addDeptModal(row = '') {
      bus.$emit('add-edit-user', row);
    },
    deleteDept(id) {
      this.deleteAlert(() => {
        this.axiosDelete('users', id, (response) => {
          this.successNoti(response.message);
          this.$store.commit('departmentDelete', id);
          bus.$emit('refresh-datatable');
        }, (error) => {
          this.errorNoti(error);
        })
      });
    }
  }
}
</script>
