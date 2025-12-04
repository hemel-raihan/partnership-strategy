<template>
  <div class="content">
    <div class="container-fluid">
      <div class="page-title-box">
        <div class="row align-items-center">
          <div class="col-sm-12">
            <h4 class="page-title">Dashboard</h4>
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                Welcome to Stock Out Tracker
              </li>
            </ol>
          </div>
        </div>
      </div>
      <div>
        <div class="row">
          <div class="col-12 mt-4">
            <div class="card">
              <div class="dashboard_container">
                <img :src="`${mainOrigin}assets/images/retails.png`" style="width: 100%; height: auto" alt="banner">
                <div class="centered">WELCOME TO Stock Out Tracker</div>
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
import moment from "moment";
export default {
  mixins: [Common],
  data: () => ({
    startDate: moment().format('YYYY-MM-DD'),
    isLoading: true,
    showLayout: false,
    isExpandIcon: true,
  }),
  mounted() {
  },
  methods: {
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

.dashboard_container {
  position: relative;
  text-align: center;
  color: white;
}
.centered {
  position: absolute;
  top: 15%;
  left: 50%;
  color: red;
  font-size: 50px;
  font-weight: bold;
  transform: translate(-50%, -50%);
}

</style>
