<template>
    <div class="tree-view-parent">
      <div class="row">
        <div class="col-md-12">
          <ul id="tree1">
            <li style="font-size:18px">Menu Permissions</li>
            <li v-for="(menuItem,indexItem) in treeList" :key="indexItem" class="group-tree">
              <div class="custom-control checkbox custom-control-inline">
                <input type="checkbox" :id="`menuPermission${menuItem.MenuID}`" v-model="menuPermission[menuItem.MenuID]"
                       class="custom-control-input">
                <label class="custom-control-label group-tree" :for="`menuPermission${menuItem.MenuID}`">{{
                    menuItem.MenuName
                  }}</label>
              </div>
            </li>
          </ul>
          <!-- <ul id="tree2">
            <li style="font-size:18px">Operation Permissions</li>
            <li v-for="(operation,indexItem) in operations" :key="indexItem" class="group-tree">
              <div class="custom-control checkbox custom-control-inline">
                <input type="checkbox" :id="`operation${operation.ID}`" v-model="operation.value"
                       class="custom-control-input">
                <label class="custom-control-label group-tree" :for="`operation${operation.ID}`">{{
                    operation.Name
                  }}</label>
              </div>
            </li>
          </ul> -->
        </div>
        <div class="col-12">
          <button class="btn btn-primary mt-2" @click="save">Save Permissions</button>
        </div>
      </div>
    </div>
  </template>
  <script>
  import {Common} from "../../mixins/common";
  
  export default {
    mixins: [Common],
    props: ['treeList', 'userId', 'permission', 'opPermissions'],
    data() {
      return {
        menuPermission: [],
        operations: [
          {ID: 'AddDSC', Name: 'ADD DSC', value: false},
          {ID: 'EditDSC', Name: 'Edit DSC', value: false},
          {ID: 'AddPetty', Name: 'ADD Petty Bill', value: false},
          {ID: 'AddBankSwipeMachine', Name: 'Add Bank Swipe Machine', value: false},
          {ID: 'EditBankSwipeMachine', Name: 'Edit Bank Swipe Machine', value: false}
        ]
      }
    },
    methods: {
      save() {
        this.axiosPost('save-user-menu-permission', {
          permission: this.menuPermission,
        //   operations: this.operations,
          userId: this.userId
        }, (response) => {
          this.successNoti(response.message);
        }, (error) => {
          this.errorNoti(error);
        })
      }
    },
    created() {
      this.operations.forEach((operation) => {
        this.opPermissions.includes(operation.ID) ? operation.value = true : operation.value = false
      })
      this.menuPermission = this.permission;
    },
    mounted() {
      $.fn.extend({
        treed: function (o) {
          var openedClass = 'ti-arrow-circle-down';
          var closedClass = 'ti-arrow-circle-right';
          if (typeof o != 'undefined') {
            if (typeof o.openedClass != 'undefined') {
              openedClass = o.openedClass;
            }
            if (typeof o.closedClass != 'undefined') {
              closedClass = o.closedClass;
            }
          }
          var tree = $(this);
          tree.addClass("tree");
          tree.find('li').has("ul").each(function () {
            var branch = $(this); //li with children ul
            branch.prepend("<i class='indicator glyphicon " + closedClass + "'></i>");
            branch.addClass('branch');
            $(this).children().children().toggle();
            branch.on('click', function (e) {
              if (this == e.target) {
                var icon = $(this).children('i:first');
                icon.toggleClass(openedClass + " " + closedClass);
                $(this).children().children().toggle();
              }
            })
            branch.children().children().toggle();
          });
          tree.find('.branch .indicator').each(function () {
            $(this).on('click', function () {
              $(this).closest('li').click();
            });
          });
          tree.find('.branch>a').each(function () {
            $(this).on('click', function (e) {
              $(this).closest('li').click();
              e.preventDefault();
            });
          });
          tree.find('.branch>button').each(function () {
            $(this).on('click', function (e) {
              $(this).closest('li').click();
              e.preventDefault();
            });
          });
        }
      });
      $('#tree1').treed();
      $('#tree2').treed();
    }
  }
  
  </script>
  <style lang="scss">
  .tree-view-parent {
    .dept-tree {
      font-size: 16px;
      color: #0d6efd;
      font-weight: 600;
    }
  
    .sub-dept-tree {
      color: #198754;
      font-size: 15px;
      font-weight: 600;
    }
  
    .category-tree {
      color: #219fb9;
    }
  
    .sub-category-tree {
      color: #6610f2;
    }
  
    .group-tree {
      color: rgba(42, 49, 66, .7);
    }
  
    .tree, .tree ul {
      margin: 0;
      padding: 0;
      list-style: none
    }
  
    .tree ul {
      margin-left: 1em;
      position: relative
    }
  
    .tree ul ul {
      margin-left: .5em
    }
  
    .tree ul:before {
      content: "";
      display: block;
      width: 0;
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      border-left: 1px solid
    }
  
    .tree li {
      margin: 0;
      padding: 0 1em;
      line-height: 2em;
      position: relative
    }
  
    .tree ul li:before {
      content: "";
      display: block;
      width: 10px;
      height: 0;
      border-top: 1px solid;
      margin-top: -1px;
      position: absolute;
      top: 1em;
      left: 0
    }
  
    .tree ul li:last-child:before {
      background: #fff;
      height: auto;
      top: 1em;
      bottom: 0
    }
  
    .indicator {
      margin-right: 5px;
    }
  
    .tree li a {
      text-decoration: none;
      color: #369;
    }
  
    .tree li button, .tree li button:active, .tree li button:focus {
      text-decoration: none;
      color: #369;
      border: none;
      background: transparent;
      margin: 0px 0px 0px 0px;
      padding: 0px 0px 0px 0px;
      outline: 0;
    }
  }
  </style>
  