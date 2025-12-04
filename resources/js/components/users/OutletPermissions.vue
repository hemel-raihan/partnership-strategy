<template>
    <div class="tree-view-parent">
      <div class="row mb-2">
        <div class="col-md-6">
          <div class="input-group">
            <input type="text" class="form-control" @keyup="search" placeholder="Search outlet here">
          </div>
        </div>
        <div class="col-12 mt-2 ml-4">
          <input type="checkbox" id="all-selected" class="custom-control-input" @click="allSelected">
          <label class="custom-control-label group-tree" for="all-selected"> Select All Outlets</label>
        </div>
      </div>
      <hr/>
      <div class="row">
        <div class="col-md-6 col-lg-3" v-for="(outlet,indexItem) in allOutlets" :key="indexItem">
          <ul id="tree1" class="tree">
            <li class="group-tree" style="list-style: none">
              <div class="custom-control checkbox custom-control-inline">
                <input type="checkbox" :id="`outletPermission${outlet.DepotCode}`"
                       class="custom-control-input" @click="userPermission(outlet.DepotCode)">
                <label class="custom-control-label group-tree" :for="`outletPermission${outlet.DepotCode}`">{{
                    outlet.DepotName
                  }}</label>
              </div>
            </li>
          </ul>
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
    props: ['treeList', 'userId', 'permission'],
    data() {
      return {
        outlets: [],
        allOutlets: []
      }
    },
    methods: {
      allSelected(event) {
        let check = $('#all-selected').is(':checked')
        if (check) {
          this.outlets = this.allOutlets.map((outlet) => outlet.DepotCode)
        } else {
          this.outlets = [];
        }
        setTimeout(() => {
          $('#tree1 input[type = "checkbox"]').prop("checked", false);
          this.outlets.forEach((outlet) => {
            $(`#outletPermission${outlet}`).prop("checked", true);
          })
        })
      },
      search(event) {
        let value = event.target.value;
        this.allOutlets = this.treeList.filter((outlet) => {
          return outlet.DepotName.search(value) !== -1 || this.outlets.includes(outlet.DepotCode);
        })
        setTimeout(() => {
          $('#tree1 input[type = "checkbox"]').prop("checked", false);
          this.outlets.forEach((outlet) => {
            $(`#outletPermission${outlet}`).prop("checked", true);
          })
          if(this.allOutlets.length === this.outlets.length){
            $("#all-selected").prop("checked", true);
          }else{
            $("#all-selected").prop("checked", false);
          }
        })
      },
      userPermission(depotCode) {
        let found = this.outlets.find((outlet) => outlet === depotCode);
        if (found) this.outlets = this.outlets.filter((outlet) => outlet !== depotCode)
        else this.outlets.push(depotCode)
  
        if(this.allOutlets.length === this.outlets.length){
          $("#all-selected").prop("checked", true);
        }else{
          $("#all-selected").prop("checked",false);
        }
      },
      save() {
        let instance = this;
        instance.axiosPost('save-user-outlet-permission', {
          userId: this.userId,
          permission: instance.outlets
        }, (response) => {
          this.successNoti(response.message);
        }, (error) => {
          this.errorNoti(error);
        })
      }
    },
    created() {
      this.outlets = this.permission;
      this.allOutlets = this.treeList
      console.log(this.allOutlets.length === this.permission.length,this.allOutlets.length)
    },
    mounted() {
      if(this.allOutlets.length === this.permission.length){
        $("#all-selected").prop("checked", true);
      }else{
        $("#all-selected").prop("checked", false);
      }
      $.fn.extend({
        treed: function (o) {
          let openedClass = 'ti-arrow-circle-down';
          let closedClass = 'ti-arrow-circle-right';
          if (typeof o != 'undefined') {
            if (typeof o.openedClass != 'undefined') {
              openedClass = o.openedClass;
            }
            if (typeof o.closedClass != 'undefined') {
              closedClass = o.closedClass;
            }
          }
          let tree = $(this);
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
      setTimeout(() => {
        this.outlets.forEach((outlet) => {
          $(`#outletPermission${outlet}`).prop("checked", true);
        })
      })
    }
  }
  </script>
  <style lang="scss">
  ul {
    margin-bottom: 0 !important;
  }
  
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
  