<template>
    <div class="modal" id="tenderDetailsModal"  role="dialog">
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title">Tender Details</h6>
            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" @click="closeModal">&times;</span>
            </button> -->
          </div>
          <div class="modal-body">
            <div class="summary-box mb-2">
              <div class="summary-line">
                <div class="row">
                  <div class="col-md-4">MRP Total</div>
                  <div class="col-md-2">:</div>
                  <div class="col-md-6" style="text-align: right;">{{ calculateSummary.mrpTotal }}</div>
                </div>
              </div>
              <div class="summary-line">
                <div class="row">
                  <div class="col-md-4">(+) SD</div>
                  <div class="col-md-2">:</div>
                  <div class="col-md-6" style="text-align: right;">{{
                      parseFloat(calculateSummary.sd).toFixed(2)
                    }}
                  </div>
                </div>
              </div>
              <div class="summary-line">
                <div class="row">
                  <div class="col-md-4">(+) VAT/Tax</div>
                  <div class="col-md-2">:</div>
                  <div class="col-md-6" style="text-align: right;">{{
                      parseFloat(calculateSummary.vatTax).toFixed(2)
                    }}
                  </div>
                </div>
              </div>
              <div class="summary-line">
                <div class="row">
                  <div class="col-md-4">(-) Discount</div>
                  <div class="col-md-2">:</div>
                  <div class="col-md-6" style="text-align: right;">{{
                      parseFloat(calculateSummary.discount).toFixed(2)
                    }}
                  </div>
                </div>
              </div>
              <div class="line"></div>
              <div class="summary-line">
                <div class="row">
                  <div class="col-md-4">TOTAL</div>
                  <div class="col-md-2">:</div>
                  <div class="col-md-6" style="text-align: right;">{{ calculateSummary.total }}</div>
                </div>
              </div>
            </div>
            <div ref="scrollableTender" class="tender-box table-container">
              <table class="table table-sm table-bordered sticky-header">
                <thead>
                <tr>
                  <th style="width: 50%;">Tender Details</th>
                  <th style="width: 10%;">Amount</th>
                  <th style="width: 40%;">Ref No</th>
                </tr>
                </thead>
                <tbody class="tender-class">
                <tr v-for="(tender, index) in tenders" :key="index"
                    :class="{ 'selected': index === selectedTenderRowIndex, 'selected-row': index === selectedTenderRowIndex }"
                    tabindex="0" @keydown.enter="moveTenderSelection('enter')" @keydown.up.prevent="moveTenderSelection('up')" @keydown.down.prevent="moveTenderSelection('down')">
                  <td>{{ tender.TenderType }}</td>
                  <td style="text-align: right;">
                    <input
                        :style="`${tender.TenderID == 'CASH' || tender.TenderID == 'RDIF' || tender.TenderID == 'RETN' ? 'background-color: black; color: white; width: 150px; text-align: right;' : 'text-align: right;'}`"
                        type="number"
                        min="0"
                        :disabled="tender.disableStatus"
                        v-model="tender.TenderValue"
                        class="form-control form-control-sm"
                        :id="`tenderFieldID-${index}`"
                        @click="selectAllText($event,index)"
                        @input="calculateTender(tender,$event,index)"
                        @keydown="handleKeyDown"
                    />
                  </td>
                  <td>
                    <input type="text" autocomplete="off" min="0" class="form-control form-control-sm"
                            :disabled="tender.disableRefStatus"
                            v-model="tender.TenderRefValue"
                            :id="`tenderRefFieldID-${index}`"
                            @click="selectAllText($event,index)"
                            @input="calculateTender(tender,$event,index)"
                            @keydown="handleKeyDown"
                    />
                  </td>
                </tr>
                </tbody>
              </table>
            </div>

            <div class="print-btn pt-3" style="text-align: center;">
              <button :disabled="enablePrinting()" ref="printButton" class="btn btn-primary" @keydown.enter="printInvoice" @click="printInvoice"><i
                  class="ti-printer"></i> PRINT
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </template>
<script>
import {bus} from "../../app";
import {Common} from "../../mixins/common";
import {mapGetters} from "vuex";

  export default {
    mixins: [Common],
    data() {
      return {
        tenderTableContainerHeight: '380px',
        selectedTenderRowIndex: -1,
        selectedTenderColIndex: 1,
        selectedType: '',
        loyaltybtnStatus:'confirm',
        prevTenders:[]
      }
    },
    watch:{
      // loyaltybtnStatus(value){
      //   if (value=='confirm') {
      //     $(".swal2-cancel").css("border",'0px solid black')
      //     $(".swal2-confirm").css("border",'2px solid black')
      //   } else {
      //     $(".swal2-confirm").css("border",'0px solid black')
      //     $(".swal2-cancel").css("border",'2px solid black')
      //   }
      // }
    },
    computed: {
      ...mapGetters(['cartProducts', 'tenders', 'disabledTender', 'calculateSummary', 
                     'customerInfo', 'freeItems', 'discountData']),
    },
    created() {
    },
    mounted() {
      this.$el.focus();
      let height = window.screen.height
      this.tenderTableContainerHeight = (height - 210) + 'px'
      bus.$on('tender-list-modal', (selectedType) => {
        this.selectedType = selectedType
        $('#tenderDetailsModal').modal({backdrop: 'static', keyboard: false})
        $("#tenderDetailsModal").modal("toggle");
        const scrollableTender = this.$refs.scrollableTender;
        scrollableTender.scrollTo({
          top: scrollableTender.scrollHeight,
          behavior: 'smooth',
        }); 
        
        this.$store.commit('calculateTenderValue');
        this.selectedRowIndex = null
        this.tenders.forEach((tender, i) => {
          if(tender.TenderID == 'PAID'){
            this.selectedTenderRowIndex = i
            this.selectedTenderColIndex = 1
            setTimeout(() => {
              $("#tenderFieldID-" + i).focus();
              $("#tenderFieldID-" + i).select();
            })
          }
        })
      })

      $(document).keydown((event) => {
        if(event.keyCode == 27 && (this.selectedType == 'tender' || this.selectedType == 'print')) {      // Esc
          this.closeTenderDetailsInfo()
          bus.$emit('tender-list-close');
        }
        else if (event.keyCode == 38 && this.selectedType == 'print') {      // up Press
          event.preventDefault();
          this.selectedType = 'tender'
          this.moveTenderSelection('up')
        }
        else if (event.keyCode == 13 && this.selectedType == 'print') {      // Enter Press for print
          event.preventDefault();
          if(!this.enablePrinting()){
            this.printInvoice()
          }
        } 
        else if (event.keyCode == 40 && this.selectedType == 'print') {      // Down Press for print
          event.preventDefault();
        } 
        // else if(event.keyCode==37 || event.keyCode==39){
        //     this.loyaltybtnStatus == 'confirm' ? this.loyaltybtnStatus = 'cancel' : this.loyaltybtnStatus ='confirm'
        // }
      });
    },
    beforeDestroy() {
    },
    methods: {
      closeModal() {
        $("#tenderDetailsModal").modal("hide");
        this.focusBarcodeInput();
      },

      focusBarcodeInput() {
        const barcodeInput = document.getElementById("barcodeSearchInput");
        if (barcodeInput) {
            barcodeInput.focus();
        }
      },

      closeTenderDetailsInfo(){
        this.$store.commit('syncDisabledTender', 0)
        let tenders = this.tenders.map((tender)=>{
          tender.TenderValue = 0;
          tender.TenderRefValue = '';
          return tender
        }) 
        this.selectedType = 'cart'
        this.$store.commit('syncTenderData',tenders)
        this.$store.commit('calculateTenderValue',true);
      },

      calculateTender(tenderItem, event, index) {
        // validation
        let tenders = this.tenders;

        if (tenderItem.TenderValue === '' && event.target.id.includes("tenderFieldID")) {
          tenders.map((tender, tenderIndex) => {
            if (tenderIndex === index) tender.TenderValue = 0
            return tender
          })
          setTimeout(() => {
            $("#tenderFieldID-" + index).select()
          })
        } else if (tenderItem.TenderRefValue === '' && event.target.id.includes("tenderRefFieldID")) {
          tenders.map((tender, tenderIndex) => {
            if (tenderIndex === index) tender.TenderRefValue = 0
            return tender
          })
          setTimeout(() => {
            $("#tenderRefFieldID-" + index).select()
          })
        }

        let creditValue = 0, payableAmount = 0, paid = 0
        tenders.forEach((tender) => {
          if (tender.TenderID === 'CASH') {
            payableAmount = parseFloat(tender.TenderValue) 
          }
          if (tender.DepositType === 'CR') {
            if (tender.TenderValue !== '' && tender.TenderValue != null) {
              creditValue += parseFloat(tender.TenderValue)
            }
          }
        })

        let loyaltyPoints = 0
        if (tenderItem.TenderID == 'LCUS') {
          loyaltyPoints = parseFloat(tenderItem?.TenderRefValue)
          let points = 0, amount = 0;
          if (loyaltyPoints > parseFloat(this.customerInfo.LoyaltyPoints)) {
            this.errorNoti('exceed the loyalty points ' + this.customerInfo.LoyaltyPoints + ' !!')
            points = this.customerInfo.LoyaltyPoints
          } else {
            points = loyaltyPoints         
          }
          amount = parseFloat(points) * parseFloat(this.customerInfo.ConvertionRate)
          if(amount > Math.round(this.calculateSummary.total)){
            this.errorNoti('exceed the payable amount!!')
            tenders = tenders.map((tender)=>{
              if (tender.TenderID === 'LCUS'){
                tender.TenderRefValue = tender.TenderRefValue.replace(/.$/, "")
              }
              return tender
            })
          }
          else{
            tenders = tenders.map((tender)=>{
              if (tender.TenderID === 'LCUS'){
                  tender.TenderRefValue = points
                  tender.TenderValue = amount
              }
              if (tender.TenderID === 'PAID'){
                paid = tender.TenderValue
              }
              if (tender.TenderID === 'CASH') {
                payableAmount = parseFloat(tender.TenderValue) 
              }
              if (tender.TenderID === 'RETN') {
                tender.TenderValue = (creditValue + amount + paid) - payableAmount
              }
              return tender
            })
          }
        }

        if (creditValue > payableAmount) {
          this.errorNoti('Exceed the card limit!')
          tenders = tenders.map((tender, i) => {
            if (index === i){
              tender.TenderValue = tender.TenderValue.replace(/.$/, "")  //tender.TenderValue.replace(event.key, "")
            } 
            return tender
          })
        }
        else if (creditValue === payableAmount) {
          tenders = tenders.map((tender) => {
            // if (tender.TenderID === 'PAID') tender.Locked = 'Y'
            if (tender.TenderID === 'PAID'){
              tender.TenderValue = 0
              paid = tender.TenderValue
            }
            if (tender.TenderID === 'RETN') {
              tender.TenderValue = (creditValue + paid) - payableAmount
            }
            return tender
          })
        } 
        else {
          tenders = tenders.map((tender) => {
            if (tender.TenderID === 'PAID'){
              paid = tender.TenderValue
              // tender.TenderValue = tender.TenderValue
            }
            if (tender.TenderID === 'RETN') {
              tender.TenderValue = (creditValue + paid) - payableAmount
            }
            return tender
          })
        }

        if (tenderItem.TenderID == 'PAID') {
          if (creditValue <= payableAmount) {
            const leftAmount = payableAmount - creditValue
            const ceiling = Math.ceil(leftAmount / 1000)
            const canPay = ceiling * 1000
            if (tenderItem.TenderValue > canPay) {
              this.errorNoti('Exceed tha cash limit!')
              tenders = tenders.map((tender, i) => {
                if (index === i){
                  tender.TenderValue = tender.TenderValue.replace(/.$/, "")
                } 
                return tender
              })

            }
          }

          let cash = 0
          tenders = tenders.map((tender) => {
            if (tender.TenderID === 'PAID') {
              cash = tender.TenderValue
            }
            if (tender.TenderID === 'RETN') {
              if (cash === 0) {
                tender.TenderValue = 0
              } else {
                tender.TenderValue =  (parseFloat(cash) + creditValue) - parseFloat(payableAmount)
              }
            }
            return tender
          })
        }
        setTimeout(()=>{
          this.prevTenders = _.clone(tenders)
        })
        this.$store.commit('syncTenderData', tenders);
        this.$store.commit('calculateTenderValue')
      },

      moveTenderSelection(type) {
        if(type == 'enter' || type == 'down'){
          if(this.selectedTenderRowIndex >= this.tenders.length){
            this.selectedType = 'print'
            this.$refs.printButton.focus();
          }
          else{
            let check = true, tender;
            do {
              if (this.selectedTenderColIndex === 2) this.selectedTenderRowIndex++;
              tender = this.tenders[this.selectedTenderRowIndex]
              if (this.selectedTenderColIndex === 1 && !tender?.disableRefStatus) {
                this.selectedTenderColIndex = 2
                check = false;
              } else if (!tender?.disableRefStatus || !tender?.disableStatus) {
                if (tender?.disableStatus) this.selectedTenderColIndex = 2
                else this.selectedTenderColIndex = 1
                check = false;
              }
            }
            while (check)
      
            if (this.selectedTenderColIndex == 1) {
              $("#tenderFieldID-" + this.selectedTenderRowIndex).focus();
              $("#tenderFieldID-" + this.selectedTenderRowIndex).select();
            } else {
              $("#tenderRefFieldID-" + this.selectedTenderRowIndex).focus();
              $("#tenderRefFieldID-" + this.selectedTenderRowIndex).select()
            }
            // this.scrollIntoView(this.selectedTenderRowIndex);
          }
        }
        else if(type == 'up'){
          if(this.selectedTenderRowIndex >= this.tenders.length){
            this.tenders.forEach((tender, i) => {
              if(tender.TenderID == 'PAID'){
                this.selectedTenderRowIndex = i
                setTimeout(() => {
                  $("#tenderFieldID-" + i).focus();
                  $("#tenderFieldID-" + i).select();
                })
              }
            })
            this.selectedType = 'tender'
            this.$refs.printButton.blur();
          }
          else{
            let check = true, tender
            do {
              if (this.selectedTenderRowIndex < 1)
              {  
                if (tender?.disableRefStatus && tender?.disableStatus) {
                  this.selectedTenderRowIndex = 1;
                  check = false;
                }
                else if(!tender?.disableRefStatus){
                  this.selectedTenderRowIndex = 0;
                  this.selectedTenderColIndex = 2
                  check = false;
                }
              }
              else if(this.selectedTenderRowIndex <= 0){
                this.selectedTenderRowIndex = 0;
                  this.selectedTenderColIndex = 2
                  check = false;
              }
              else{
                if (this.selectedTenderColIndex === 1) this.selectedTenderRowIndex--;
                tender = this.tenders[this.selectedTenderRowIndex]
                if (this.selectedTenderColIndex === 1 && !tender?.disableRefStatus) {
                  this.selectedTenderColIndex = 2
                  check = false;
                } else if (!tender?.disableRefStatus || !tender?.disableStatus) {
                  if (tender?.disableStatus) this.selectedTenderColIndex = 2
                  else this.selectedTenderColIndex = 1
                  check = false;
                }
              }
            }
            while (check)
            if (this.selectedTenderColIndex == 1) {
              $("#tenderFieldID-" + this.selectedTenderRowIndex).focus();
              $("#tenderFieldID-" + this.selectedTenderRowIndex).select();
            } else {
              $("#tenderRefFieldID-" + this.selectedTenderRowIndex).focus();
              $("#tenderRefFieldID-" + this.selectedTenderRowIndex).select()
            }
          }
        }
        this.scrollIntoView(this.selectedTenderRowIndex);
      },

      enablePrinting() {
        if (this.disabledTender == 0) {
          return true
        }

        let creditValue = 0, payableAmount = 0, roundOff = 0, paid = 0, changeAmt = 0
        this.tenders.forEach((tender) => {
          if (tender.DepositType == 'CR') {
            if (tender.TenderValue != '' && tender.TenderValue != null) {
              creditValue += parseFloat(tender.TenderValue)
            }
          }
          if (tender.TenderID == 'CASH') {
            payableAmount = parseFloat(tender.TenderValue)
          }
          if (tender.TenderID == 'RDIF') {
            roundOff = parseFloat(tender.TenderValue)
          }
          if (tender.TenderID == 'PAID') {
            paid = parseFloat(tender.TenderValue)
          }
          if (tender.TenderID == 'RETN') {
            changeAmt = parseFloat(tender.TenderValue)
          }
        })

        if ((payableAmount + changeAmt == creditValue + paid) && changeAmt >= 0) {
          return false
        } else {
          return true;
        }
      },

      printInvoice() {
        this.$store.commit('syncDisabledTender', 0)
        this.selectedType = 'cart'
        $("#tenderDetailsModal").modal("toggle");
        this.printInvoiceAlert((result) => {
          if(result == 'confirm'){
            // bus.$emit("parcel-print")
            let cusCode = $('#customerLoyaltyCode').val()
            this.axiosPost('save-invoice', {
              carts: this.cartProducts,
              freeItems: this.freeItems,
              tenders: this.tenders,
              customerCode: cusCode,
              summary: this.calculateSummary,
              discountData: this.discountData
            }, (response) => {
              this.$store.commit('syncInvoiceNo',response.invoiceNo[0].InvoiceNo)
              localStorage.removeItem('Tenders');
              this.$store.commit('syncCartProducts', [])
              this.$store.commit('removeFreeItems')
              this.$store.commit('syncCustomerData', {customer: '', discountInfo: ''})
              this.$store.commit('syncDisabledTender', 0)
              let tenders = this.tenders.map((tender)=>{
                tender.TenderValue = 0;
                tender.TenderRefValue = '';
                return tender
              }) 
              this.selectedType = 'cart'
              this.$store.commit('syncTenderData',tenders)
              this.$store.commit('calculateTenderValue',true);

              bus.$emit('print-invoice-success');
            }, (error) => {
              console.log('asd')
              this.errorNoti(error);
            })
          }
          else{
            this.$store.commit('syncDisabledTender', 1)
            // this.selectedType = 'print'
            this.tenders.forEach((tender, i) => {
              if(tender.TenderID == 'PAID'){
                this.selectedTenderRowIndex = i
                setTimeout(() => {
                  $("#tenderFieldID-" + i).focus();
                  $("#tenderFieldID-" + i).select();
                },1000)
              }
            })
            this.selectedType = 'tender'
            this.$refs.printButton.blur();
            $("#tenderDetailsModal").modal("toggle");
          }
        }, 'Want to print the Invoice?', '') 
      },

      selectAllText(event, index = '') {
        if (event.target.id == 'tenderFieldID-' + index) {
          event.target.select();
          this.selectedTenderRowIndex = index
          this.selectedTenderColIndex = 1
        }
        if (event.target.id == 'tenderRefFieldID-' + index) {
          event.target.select();
          this.selectedTenderRowIndex = index
          this.selectedTenderColIndex = 2
        }
      },

      handleKeyDown(event) {
        if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
          event.preventDefault();
        }
      },

      scrollIntoView(index) {
        const selectedRow = this.$el.querySelector('.selected');
        if (selectedRow) {
          selectedRow.scrollIntoView({behavior: 'smooth', block: 'center'});
        }
      },

    },
  };
</script>

<style scoped>
.selected-row {
  background-color: black;
  color: #fff;
}

button:focus {
  outline: 2px solid blue;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>