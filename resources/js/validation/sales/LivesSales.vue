<template>
  <div class="content">
    <div class="container-fluid">
      <breadcrumb :options="['Live Sales']">
      </breadcrumb>
    </div>
  </div>
</template>
<script>
import {Common} from "../../mixins/common";
import {bus} from "../../app";

export default {
  mixins: [Common],
  data: () => ({
    isLoading: true,
    showLayout: false,
    isShowAddEditModal: false,
  }),
  created(){
    this.getData();
  },
  mounted() {
  },
  methods: {
    getData() {
      this.axiosGet('live-sales',(response)=>{
        console.log(response)
      },(error)=>{
        this.errorNoti(error)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.no-data {
  text-align: center;
  padding: 10px;
  font-size: 16px;
  color: #cb2d2d;
  font-weight: 600;
  text-transform: uppercase;
}

.card-header {
  background-color: #f7f7f7 !important;
}

.date-layout {
  border: 1px solid #ced4da;
  padding: 4px 10px;
  border-radius: 5px;
}

.date-title-layout {
  border-bottom: 1px solid #ced4da;
  padding-bottom: 5px;
  margin-bottom: 5px;
  text-align: center;
}


</style>
