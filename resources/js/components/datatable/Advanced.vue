<template>
  <div class="datatable" v-if="!isLoading">
    <div class="row">
      <div class="col-xl-12">
        <div class="card" v-if="requestParams.filters">
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-3"
                   v-for="(filter,index) in requestParams.filters" :key="index">
                <div class="form-group" style="margin-bottom: unset">
                  <select class="form-control" id="filter" v-if="filter.type==='dropdown'" v-model="filter.value"
                          name="rider">
                    <option value="" readonly>{{ filter.title }}</option>
                    <option :value="option.value" v-for="(option,opIndex) in filter.options" :key="opIndex">
                      {{ option.text }}
                    </option>
                  </select>
                  <date-picker v-else-if="filter.type==='datepicker'" v-model="filter.value" :placeholder="filter.title"
                               format="DD-MM-YYYY" valueType="format"></date-picker>
                  <date-picker v-else-if="filter.type==='rangepicker'" v-model="filter.value"
                               :placeholder="filter.title" format="DD-MM-YYYY" range valueType="format"></date-picker>
                </div>
              </div>
              <div class="col-12 col-md-1 ml-1">
                <div class="form-group" style="margin-bottom: unset">
                  <button class="btn btn-primary" @click="readData">Filter</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xl-12">
        <div class="card">
          <div class="card-body">
            <div class="search mb-2">
              <div class="row">
                <div class="col-lg-3">
                  Show <select class="form-control" style="width:unset;display:unset" v-model="showDataAmount"
                               @change="selectedDataAmount" id="rows">
                  <option v-for="(page,index) in options.pages" :value="page" :key="index">{{ page }}</option>
                </select> rows
                </div>
                <div class="col-lg-3"></div>
                <div class="col-lg-3"></div>
                <div class="col-lg-3">
                  <input type="text" style="float:right" class="form-control" placeholder="Search here" id="search"
                         @keyup="search()" v-model="requestParams.search"/>
                </div>
              </div>
            </div>
            <div class="datatable" style="overflow-x:auto">
              <table id="datatable"
                     class="table table-bordered table-striped dt-responsive nowrap dataTable no-footer dtr-inline table-sm"
                     style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                <thead>
                <th v-for="(column,columnIndex) in headers" class="sorting"
                    :class="{'text-center':options.textCenter && options.textCenter.includes(columnIndex),'text-right':options.textRight && options.textRight.includes(columnIndex)}"
                    :key="columnIndex">
                  <span>{{ column }}</span>
                  <span style="float: right"
                        v-if="requestParams.sortedKey === column.key && requestParams.sortedType === 'asc'">
                            <a href="javascript:" @click="sortedKeyValue(column.key,'desc')">
                                <img :src="`${mainOrigin}assets/images/datatable/up.png`" alt="up.png">
                            </a>
                        </span>
                  <span style="float: right"
                        v-else-if="requestParams.sortedKey === column.key && requestParams.sortedType === 'desc'">
                            <a href="javascript:" @click="sortedKeyValue(column.key,'asc')">
                                <img :src="`${mainOrigin}assets/images/datatable/down.png`" alt="down.png">
                            </a>
                        </span>
                  <span style="float: right" v-else-if="column.sortable">
                           <a href="javascript:" @click="sortedKeyValue(column.key,'desc')">
                              <img :src="`${mainOrigin}assets/images/datatable/sort.png`" alt="sort.png">
                           </a>
                        </span>
                </th>
                </thead>
                <tbody>
                <template v-if="dataSets.length > 0">
                  <tr v-for="(data,dataIndex) in dataSets" :key="dataIndex">
                    <td v-for="(column,columnIndex) in columns" :key="columnIndex"
                        :class="{'text-center':options.textCenter && options.textCenter.includes(columnIndex),'text-right':options.textRight && options.textRight.includes(columnIndex)}">
            <span v-if="options.slots && options.slots.includes(columnIndex)">
              <slot :name="options.slotsName[options.slots.indexOf(columnIndex)]" :item="data"></slot>
            </span>
                      <span v-else-if="options.numberFormat && options.numberFormat.includes(columnIndex)">{{
                          numberFormat(data[column])
                        }}</span>
                      <span v-else-if="options.periodFormat && options.periodFormat.includes(columnIndex)">{{
                          periodFormat(data[column])
                        }}</span>
                      <span v-else-if="options.dateFormat && options.dateFormat.includes(columnIndex)">{{
                          dateFormat(data[column])
                        }}</span>
                      <span v-else>{{ data[column] }}</span>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr style="text-align: center;">
                    <td :colspan="columns.length" class="no-data-row"> No data found.</td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
            <div class="row">
              <div class="col-4">
                <div class="data-count">
                  Show {{ showFrom }} to {{ showTo }} of {{ totalCount }} rows
                </div>
              </div>
              <div class="col-8">
                <div class="pagination" v-if="totalCount > requestParams.take">
                  <a href="javascript:" :class="{'isDisabled':currentPage===1}" @click="newPage(1)">First Page</a>
                  <a href="javascript:" :class="{'isDisabled':currentPage===1}"
                     @click="newPage(currentPage-1)">&laquo;</a>
                  <a href="javascript:" @click="newPage(page)"
                     :class="{'active isDisabled':currentPage===page,'isDisabled':page === '...'}"
                     v-for="(page,pageIndex) in pages" :key="pageIndex">{{ page }}</a>
                  <a @click="newPage(currentPage+1)" :class="{'isDisabled':currentPage===totalPage}"
                     href="javascript:">&raquo;</a>
                  <a @click="newPage(totalPage)" :class="{'isDisabled':currentPage===totalPage}" href="javascript:">Last
                    Page</a>
                </div>
                <div class="pagination" v-else>
                  <a href="javascript:" class="isDisabled">&laquo;</a>
                  <a href="javascript:" class="active isDisabled">1</a>
                  <a class="isDisabled" href="javascript:">&raquo;</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <data-export/>
  </div>
  <div v-else>
    <skeleton-loader :row="9"/>
  </div>
</template>

<script>
import {Common} from "../../mixins/common";
import {bus} from "../../app";
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';

export default {
  components: {DatePicker},
  props: ['options'],
  mixins: [Common],
  data() {
    return {
      time1: null,
      dataSets: [],
      filename: '',
      requestParams: {
        take: '',
        search: '',
        sortedType: 'asc',
        sortedKey: '',
        filters: [],
      },
      currentPage: 1,
      pages: [],
      showDataAmount: '',
      totalPage: 1,
      totalCount: 0,
      showTo: 0,
      showFrom: 0,
      isLoading: true,
      headers: [],
      columns: []
    }
  },
  mounted() {
    // this.requestParams.sortedKey = this.options.columns[0].key;
    // this.requestParams.sortedType = this.options.sortedType;
    this.requestParams.filters = this.options.filters;
    this.requestParams.take = this.showDataAmount = this.options.pages[0];
    this.readData();
    bus.$on('refresh-datatable', () => {
      this.readData();
    })
    bus.$on('export-data', (name) => {
      this.filename = name;
      this.readData('export');
    })
  },
  methods: {
    sortedKeyValue(key, type) {
      this.requestParams.sortedKey = key;
      this.requestParams.sortedType = type;
      this.readData();
    },
    readData(type = '') {
      this.requestParams.type = type
      this.axiosPost(this.options.source + '?page=' + this.currentPage, this.requestParams,
          (response) => {
            if (type !== 'export') {
              this.dataSets = response.data;
              if (this.dataSets.length > 0) {
                let columns = Object.keys(response.data[0]);
                columns = columns.filter((item) => item !== 'row_num');
                this.columns = [];
                if (this.options.hideColumn) {
                  columns.forEach((item) => {
                    let find = this.options.hideColumn.find((hide) => hide == item);
                    if (!find) {
                      this.columns.push(item);
                    }
                  });
                } else {
                  this.columns = columns;
                }
                if (this.options.addHeader) this.columns = [...this.columns, ...this.options.addHeader];
                let rex = /([A-Z])([A-Z])([a-z])|([a-z])([A-Z])/g;
                this.headers = this.columns.map((item) => item.replace(rex, '$1$4 $2$3$5'));
                this.totalCount = response.total;
                this.showTo = response.to;
                this.showFrom = response.from;
                this.totalPage = Math.ceil(this.totalCount / this.requestParams.take);
                this.pages = this.pagination(this.currentPage, this.totalPage);
              }
              this.isLoading = false;
            } else {
              let dataSets = response;
              if (dataSets.length > 0) {
                let columns = Object.keys(response[0]);
                columns = columns.filter((item) => item !== 'row_num');
                let rex = /([A-Z])([A-Z])([a-z])|([a-z])([A-Z])/g;
                columns = columns.map((item) => {
                  let title = item.replace(rex, '$1$4 $2$3$5')
                  return {title, key: item}
                });
                bus.$emit('data-table-import', dataSets, columns, this.filename)
              }
            }
          },
          (error) => {
            this.errorNoti(error);
          }
      )
    },
    search() {
      this.currentPage = 1;
      this.readData();
    },
    selectedDataAmount() {
      this.requestParams.take = this.showDataAmount;
      this.readData();
    },
    newPage(page) {
      if (page !== 0 && page <= this.totalPage) {
        this.currentPage = page;
        this.readData();
      }
    },
    pagination(c, m) {
      var delta = 2,
          range = [],
          rangeWithDots = [],
          l;

      range.push(1);
      for (let i = c - delta; i <= c + delta; i++) {
        if (i < m && i > 1) {
          range.push(i);
        }
      }
      range.push(m);

      for (let i of range) {
        if (l) {
          if (i - l === 2) {
            rangeWithDots.push(l + 1);
          } else if (i - l !== 1) {
            rangeWithDots.push('...');
          }
        }
        rangeWithDots.push(i);
        l = i;
      }
      return rangeWithDots;
    }
  }
}
</script>
<style lang="scss">
:root {
  --code-color: darkred;
  --code-bg-color: #aaaaaa;
  --code-font-size: 14px;
  --code-line-height: 1.4;
  --scroll-bar-color: #c5c5c5;
  --scroll-bar-bg-color: #f6f6f6;
}

pre {
  color: var(--code-color);
  font-size: var(--code-font-size);
  line-height: var(--code-line-height);
  background-color: var(--code-bg-color);
}

.code-block {
  max-height: 100px;
  overflow: auto;
  padding: 8px 7px 5px 15px;
  margin: 0px 0px 0px 0px;
  border-radius: 7px;
}

::-webkit-scrollbar-corner {
  background: rgba(0, 0, 0, 0.5);
}

* {
  scrollbar-width: thin;
  scrollbar-color: var(--scroll-bar-color) var(--scroll-bar-bg-color);
}

/* Works on Chrome, Edge, and Safari */
*::-webkit-scrollbar {
  width: 12px;
  height: 12px;
}

*::-webkit-scrollbar-track {
  background: var(--scroll-bar-bg-color);
}

*::-webkit-scrollbar-thumb {
  background-color: var(--scroll-bar-color);
  border-radius: 20px;
  border: 3px solid var(--scroll-bar-bg-color);
}

.datatable select {
  margin: 0 5px;
}

#datatable {
  border-collapse: collapse;
  width: 100%;
}

#datatable td, #datatable th {
  border: 1px solid #ddd;
  padding: 6px;
}

#datatable tr:hover {
  background-color: #f1f1f1;
}

#datatable th {
  text-align: left;
}

.datatable .pagination {
  display: inline-block;
  margin-top: 12px;
  float: right;
}

.datatable .data-count {
  display: inline-block;
  margin-top: 12px;
}

.datatable .pagination a {
  color: black;
  float: left;
  padding: 3px 10px;
  text-decoration: none;
  transition: background-color .3s;
  border: 1px solid #ddd;
}

.datatable .pagination a.active {
  background-color: #626ed4;
  border-color: #626ed4;
  color: #fff;
}

.datatable .pagination a:hover:not(.active) {
  background-color: #ddd;
}

.datatable .isDisabled {
  cursor: not-allowed;
  opacity: 0.5;
}

thead {
  background: #333547;
  color: white;
}

.table {
  margin-bottom: unset !important;
}

.no-data-row {
  height: 100px;
  font-size: 20px;
  font-weight: 600;
  text-transform: capitalize;
}

.mx-datepicker {
  width: 100% !important;
}
</style>
