<template>
    <div class="content">
      <div class="container-fluid">
        <breadcrumb :options="['Users']">
          <button class="btn btn-primary" @click="addDeptModal()">Add User</button>
        </breadcrumb>
        <h4>Config Lists</h4>
        <draggable v-model="configList" @end="onDragEnd" class="drag-container">
            <div class="config-item"  v-for="item in configList" :key="item.ConfigSL">
                {{ item.ParticularName }}
            </div>
        </draggable>
      </div>
      <AddEditModal/>
    </div>
  </template>
  <script>
  import {bus} from "../../app";
  import {Common} from "../../mixins/common";
  import AddEditModal from "../../components/users/AddEditModal";
  import draggable from 'vuedraggable';
  
  export default {
    mixins: [Common],
    components:{AddEditModal, draggable},
    data() {
      return {
        configList: [],
        draggingItem: ''
      }
    },
    mounted() {
        this.getAllConfigs()
    },
    methods: {
        getAllConfigs() {
            this.axiosGet('get-all-configs',
            (response) => {
                this.configList = response.data;
            }, (error) => {
                this.errorNoti(error)
            })
        },
        onDragEnd(event) {
            const updatedOrder = this.configList.map(item => item.ConfigSL);
            
            this.axiosPost('update-config-order', {
                order: updatedOrder,
            }, (response) => {
                this.getAllConfigs()
            }, (error) => {
                this.errorNoti(error);
            })
        },
    }
  }
  </script>

<style scoped>
.drag-container {
  list-style-type: none;
  padding: 10px;
  border: 2px solid #ccc;
  border-radius: 5px;
  cursor: grab;
}

.config-item {
  background-color: #f8f8f8;
  padding: 10px;
  margin: 5px 0;
  border: 1px solid #000;
  border-radius: 4px;
  cursor: grab;
  transition: background-color 0.3s;
}

.dragging {
  opacity: 0.5;
}

.dragged {
  background-color: #aaffaa;
}
</style>
  