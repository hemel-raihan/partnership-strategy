<template>
    <div class="modal" id="rePrintModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-md" role="document" style="margin-left: 73%; margin-top: 25%">
        <div class="modal-content">
          <!-- <div class="modal-header">
            <h5 class="modal-title">Shortcut List</h5>
          </div> -->
          <div class="modal-body">
            <div class="row">
              <div class="col-md-9">
                <div class="form-group row">
                  <label class="col-md-4 col-form-label">Terminal:</label>
                  <div class="col-md-8">
                    <multiselect v-model="terminal" :options="terminals" label="Terminal" track-by="TerminalID" :multiple="false" id="terminal" placeholder="Select Terminal">
                    </multiselect>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-4 col-form-label">Date:</label>
                  <div class="col-md-8">
                    <ValidationProvider name="Date Range" mode="eager" rules="required" v-slot="{ errors }">
                      <div class="input-group">
                        <date-picker v-model="dateRange" id="dateRange" range format="MMM DD YYYY" valueType="format" placeholder="Date Range"></date-picker>
                      </div>
                      <span class="error-message"> {{ errors[0] }}</span>
                    </ValidationProvider>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-4 col-form-label">Time From:</label>
                  <div class="col-md-8">
                    <input type="time" v-model="timeFrom" class="form-control">
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-4 col-form-label">Time To:</label>
                  <div class="col-md-8">
                    <input type="time" v-model="timeTo" class="form-control">
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="row">
                  <div class="col-md-12">
                    <button @click="searchInvoices" class="btn btn-success btn-block">Show</button>
                    <button @click="printSelectedInvoice" class="btn btn-primary btn-block">Print</button>
                    <button class="btn btn-primary btn-block">Print All</button>
                    <button class="btn btn-danger btn-block">Close</button>
                  </div>
                </div>
              </div>
            </div>
            
            <hr>

            <div class="row mt-4">
              <div class="col-md-12">
                <div v-if="invoices.length > 0" @scroll="handleScroll" ref="tableContainer" class="invoice-list table-containerr" id="table-containerr">
                  <table class="table table-sm table-striped table-bordered sticky-header">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(invoice, index) in paginateInvoices" :key="index" @click="toggleRow(invoice.InvoiceNo)" :class="{ 'selected': isSelected(invoice.InvoiceNo) }">
                        <td><input type="checkbox" v-model="selectedRows" :value="invoice.InvoiceNo"></td>
                        <td>{{ invoice.InvoiceNo }}</td>
                        <td>{{ dateFormat(invoice.InvoiceDate) }}</td>
                        <td>{{ invoice.NSI }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else>
                  <p v-if="isErrShow" style="color: red; text-align: center;">No Invoice Found!</p>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </template>
<script>
import {bus} from "../../app";
import Multiselect from 'vue-multiselect'
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import {Common} from "../../mixins/common";

  export default {
    components: {Multiselect, DatePicker},

    mixins: [Common],
    data() {
      return {
        terminal: '',
        terminals: [
          {Terminal: '', TerminalID: ''}
        ],
        dateRange: '',
        timeFrom: '',
        timeTo: '',
        invoices: [],
        selectedRows: [],
        itemsToShow: 20,
        lastIndex: 0,
        paginateInvoices: [],
        isErrShow: false
      }
    },
    created() {
    },
    mounted() {
      this.$el.focus();
      bus.$on('re-print-modal', (selectedType) => {
        $("#rePrintModal").modal("toggle"); 
        this.loadTerminals()
      })
    },
    beforeDestroy() {
    },
    methods: {

      loadTerminals(){
        this.axiosGet('all-terminals',
          (response) => {
            this.terminals = response.data;
          }, (error) => {
            this.errorNoti(error)
        })
      },

      searchInvoices(){
        if(!this.terminal || !this.dateRange || !this.timeFrom || !this.timeTo){
          this.errorNoti('the fields must not be empty!')
        }
        else{
          this.isErrShow = false
          this.axiosPost('search-reprint-invoice', 
          {
            terminal: this.terminal.TerminalID,
            dateRange: this.dateRange,
            timeFrom: this.timeFrom,
            timeTo: this.timeTo,
          }, 
          (response) => {
            if(response.data.length > 0){
              this.invoices = response.data
              this.paginateInvoices = this.invoices.slice(0, this.itemsToShow);
              this.lastIndex = this.itemsToShow;
              this.isErrShow = false
            }
            else{
              this.isErrShow = true
            }
          }, (error) => {
            this.errorNoti(error);
          });
        }
      },

      toggleRow(invoiceNo) {
        const index = this.selectedRows.indexOf(invoiceNo);
        if (index === -1) {
          this.selectedRows.push(invoiceNo);
        } else {
          this.selectedRows.splice(index, 1);
        }
      },

      isSelected(invoiceNo) {
        return this.selectedRows.includes(invoiceNo);
      },

      handleScroll() {
        const tableContainer = this.$refs.tableContainer;
        const scrollHeight = tableContainer.scrollHeight;
        const clientHeight = tableContainer.clientHeight;
        const scrollTop = tableContainer.scrollTop;

        if (scrollTop + clientHeight >= 0.8 * scrollHeight) {
          this.loadNextPage();
        }
      },

      loadNextPage(){
        let nextBatch;
        nextBatch = this.invoices.slice(this.lastIndex, this.lastIndex + 20);
        this.paginateInvoices = this.paginateInvoices.concat(nextBatch);
        this.lastIndex += 20;
      },

      printSelectedInvoice() {
        console.log("Selected rows:", this.selectedRows);
      },

      // closeModal() {
      //   $("#shortCutModal").modal("hide");
      //   this.focusBarcodeInput();
      // },
      // focusBarcodeInput() {
      //   const barcodeInput = document.getElementById("barcodeSearchInput");
      //   if (barcodeInput) {
      //       barcodeInput.focus();
      //   }
      // },
    },
  };
  </script>
  <style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>