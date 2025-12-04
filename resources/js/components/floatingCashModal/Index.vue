<template>
    <div class="modal" ref="floatCashModalDiv" id="floatCashModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <!-- <div class="modal-header">
            
          </div> -->
          <div class="modal-body">
            <div class="row">
              <!-- <div class="col-md-4">
                <h5 class="modal-title text-center" >Cash Oppening: 5000</h5>
              </div> -->
              <div class="col-md-12">
                <h5 class="modal-title text-center">Cash {{denominationStatus}}</h5>
                <hr>
                <h6 class="text-left"  v-if="denominationStatus == 'Closing'">Cash Opening: {{denomination}}</h6>
                <h6 class="text-left">Opening Date: {{denominationDate}}</h6>
                <h6 class="text-left">User: {{authUser.UserName}}</h6>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-12">
                <div v-if="notes.length > 0" ref="tableContainer" class="cash-float table-containerr" id="table-containerr">
                  <table class="table table-sm table-striped table-bordered sticky-header">
                    <thead>
                      <tr>
                        <th style="text-align: center; width: 40%;">Denomination</th>
                        <th v-if="denominationStatus == 'Closing'" style="text-align: center; width: 20%;">Opening Note Qty</th>
                        <th style="text-align: center; width: 20%;">Current Note Qty</th>
                        <th style="text-align: center;">Net Amount (BDT)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(note, index) in notes" :key="index">
                        <td style="text-align: center;">{{ note.NoteAmount }}</td>
                        <td v-if="denominationStatus == 'Closing'" style="text-align: center;">{{ note.prevQty}}</td>
                        <td style="text-align: center;">
                          <input type="number"
                               min="1"
                               maxlength="8"
                               class="form-control"
                               :id="`qtyFieldID-${index}`"
                               style="text-align: center; height: 25px;"
                               v-model="note.Quantity"
                               @input="updateAmount(note)"/>
                        </td>
                        <td style="text-align: center;">{{ note.totalAmount }}</td>
                      </tr>
                      <tr>
                        <td></td>
                        <td v-if="denominationStatus == 'Closing'" style="text-align: center;">
                          <label style="font-weight: bold; background: black; color: white; padding: 0px 10px 0px 10px;">
                            {{ denomination }}
                          </label>
                        </td>
                        <td></td>
                        <td style="text-align: center;">
                          <label style="font-weight: bold; background: black; color: white; padding: 0px 10px 0px 10px;">
                            {{ netTotalAmount() }}
                          </label>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else>
                  <p v-if="isErrShow" style="color: red; text-align: center;">No Data Found!</p>
                </div>
              </div>
            </div>
            <div style="float: right;">
              <button @click="saveDenomination" class="btn btn-success mt-4">{{!isSaveLoading ? 'Save' : 'Loading...'}}</button>
              <button @click="closeDenomination" class="btn btn-danger mt-4">Exit</button>
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
import moment from "moment";

  export default {
    components: {Multiselect, DatePicker},

    mixins: [Common],
    data() {
      return {
        denominationStatus: '',
        cashStatus: '',
        notes: [],
        // netTotalAmount: '',
        selectedType: '',
        timeFrom: '',
        timeTo: '',
        invoices: [],
        totalInvoiceCashSum: '',
        selectedRows: [],
        itemsToShow: 20,
        lastIndex: 0,
        paginateInvoices: [],
        isErrShow: false,
        isSaveLoading: false,
        denomination: '',
        denominationDetails: [],
        denominationDate: '',
        authUser: '',
        isUserAuth: false
      }
    },
    created() {
    },
    mounted() {
      this.$el.focus();
      this.getAuthUserData()
      bus.$on('float-cash-modal', (selectedType, cashStatus, denomination, details) => {
        this.loadNotes()
        if(cashStatus == 'needToBeOpen'){
          this.denominationStatus = 'Opening'
          this.denominationDate = moment().format("MMM DD YYYY")
        }
        else{
          this.loadAllInvoiceCashForClosing()
          this.denominationStatus = 'Closing'
          this.denomination = denomination.TotalAmount
          this.denominationDate = denomination.DenominationDate
          this.denominationDetails = details
        }
        this.selectedType = selectedType
        $('#floatCashModal').modal({backdrop: 'static', keyboard: false})
        $("#floatCashModal").modal("toggle"); 
      })

      setTimeout(() => {
        $("#qtyFieldID-0").focus();
      },2000)

      $(document).keydown((event) => {
        if(event.keyCode == 27 && (this.selectedType == 'floatingCash')) {      // Esc
          event.preventDefault();
        }
      });

      bus.$on('cash-closing-auth', () => {
        this.doUpdateDenomination()
      });

      bus.$on('auth-cancel', () => {
        const div = this.$refs.floatCashModalDiv;
        div.style.zIndex  = '1050';
      });

    },
    beforeDestroy() {
    },
    methods: {
      getAuthUserData() {
        this.axiosPost('me', {}, (response) => {
          this.authUser = response
          if (response.grpAdd == 1 || response.grpSup == 1) {
            this.isUserAuth = true
          } else if(response.grpUser == 1) {
            this.isUserAuth = false
          }
        }, (error) => {
          this.errorNoti(error);
        });
      },

      loadAllInvoiceCashForClosing(){
        this.axiosGet('all-invoices-for-cash-closing',
          (response) => {
            this.invoices = response.invoiceInfo;
            this.totalInvoiceCashSum = response.netDetails[0].CashSum ? response.netDetails[0].CashSum : 0
          }, (error) => {
            this.errorNoti(error)
        })
      },

      loadNotes(){
        this.axiosGet('all-notes',
          (response) => {
            // this.notes = response.data;
            this.notes = response.data.map((data, i) => {
                data.totalAmount = 0
                let denom = this.denominationDetails.find((denom) => denom.NoteID == data.NoteID)
                data.prevQty = denom ? denom.NoteQuantity : 0
                return data
            })
          }, (error) => {
            this.errorNoti(error)
        })
      },

      updateAmount(note){
        if (note.Quantity.toString().length > 8) {
          note.Quantity = parseInt(note.Quantity.toString().substring(0, 8), 10);
        }
        this.notes = this.notes.map((data, i) => {
          if (data.NoteAmount === note.NoteAmount){
            // data.Quantity = note.Quantity
            data.totalAmount = note.NoteAmount * note.Quantity
          }
          return data
        })
        
      },

      netTotalAmount(){
        return this.notes.reduce((total, note) => total + note.totalAmount, 0);
      },

      closeDenomination(){
        bus.$emit('floating-cash-exit', this.denominationStatus);
      },

      doUpdateDenomination(){
        if(this.denominationStatus == 'Closing'){
          let totalCash = parseFloat(this.denomination) + parseFloat(this.totalInvoiceCashSum)
          console.log(totalCash, this.netTotalAmount())
          if(totalCash == this.netTotalAmount()){
            this.isSaveLoading = true
            this.axiosPost('save-denomination', 
            {
              notes: this.notes,
              netTotalAmount: this.netTotalAmount(),
            }, 
            (response) => {
              this.isSaveLoading = false
              this.successNoti(response.msg);
              bus.$emit('floating-cash-updated', this.denominationStatus);
            }, (error) => {
              this.errorNoti(error);
            });
          }
          else{
            this.errorNoti('The Cash is not matching with invoice bill and opening balance!');
          }
        }
        else{
          this.isSaveLoading = true
          this.axiosPost('save-denomination', 
          {
            notes: this.notes,
            netTotalAmount: this.netTotalAmount(),
          }, 
          (response) => {
            this.isSaveLoading = false
            this.successNoti(response.msg);
            bus.$emit('floating-cash-updated', this.denominationStatus);
          }, (error) => {
            this.errorNoti(error);
          });
        }
      },

      saveDenomination(){
        if(this.denominationStatus == 'Closing'){
          if (this.isUserAuth) {
            this.doUpdateDenomination()
          } 
          else {
            const div = this.$refs.floatCashModalDiv;
            div.style.zIndex  = '1';
            bus.$emit('digital-authentication', 'cash-closing', '');
          }
        }
        else{
          this.doUpdateDenomination()
        }
      },
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