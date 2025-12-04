<template>
    <div class="modal" id="returnModal" tabindex="-1" role="dialog"
     @keydown.down.prevent="moveSelection('down')"
     @keydown.up.prevent="moveSelection('up')"
     @focus="setDefaultSelection">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <!-- <h5 class="modal-title">Modal Title</h5> -->
            <div class="barcode-search-bar" style="width: 50%;">
              <div class="form-group barcode-search">
                <input type="text" style="width:350px" class="form-control form-control-sm"
                        id="invoiceSearchInput"
                        v-model="invSearchProduct"
                        @keydown.enter="filteredReturnProducts"
                        placeholder="Invoice/Bill">
              </div> 
              <div class="form-group barcode-search">
                <label>Bill date: {{ dateFormat(invoiceSummary.InvoiceDate) }}</label>
              </div>      
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" @click="closeModal">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-9" style="margin-right: 0; padding-right: 0;">
                    <div @scroll="handleScroll" ref="tableContainer" class="return-product-list table-containerr" id="table-containerr">
                        <table class="table table-sm small  table-bordered sticky-header">
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Discount</th>
                            <th>Pack Size</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Sales Qty</th>
                            <th style="text-align: right;">Total</th>
                            <th style="text-align: right; width: 10%;">Return Qty</th>
                            <th style="text-align: right; width: 15%;">Return Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(product, index) in returnProducts" :key="index" 
                            :class="{ 'selected': index === selectedReturnRowIndex, 'selected-row': index === selectedReturnRowIndex }"
                            @click="selectRow(index)"
                            tabindex="0"
                            >
                            <td>{{ product.ProductCode }}</td>
                            <td>{{ product.ProductName }}</td>
                            <td>{{ product.AllDiscount }}</td>
                            <td>{{ product.PackSize }}</td>
                            <td style="text-align: right;">{{ Number(product.SalesTP).toFixed(2) }}</td>
                            <td style="text-align: right;">{{ Number(product.SalesQTY).toFixed(2) }}</td>
                            <td style="text-align: right;">{{ Number(product.TP).toFixed(2) }}</td>
                            <td style="text-align: right;">
                                <input type="number"
                                  class="form-control" 
                                  :id="`returnqty-${index}`" 
                                  style="text-align: center; height: 30px;"
                                  v-model="product.ReturnQty"
                                  @input="updateTotal(product)"
                                  />
                            </td>
                            <td style="text-align: right;">
                                <input type="number"
                                  class="form-control" 
                                  disabled
                                  :id="`returnamt-${index}`" 
                                  style="text-align: center; height: 30px;"
                                  v-model="product.ReturnAmt"
                                  />
                            </td>
                        </tr>
                        </tbody>
                        </table>
                        <div v-if="isLoading" class="loading-indicator">Loading...</div>
                    </div>

                    <h6 class="text-left">Free Items</h6>
                    <div class="free-items table-container">
                      <table class="table table-sm small table-striped table-bordered sticky-header">
                        <thead>
                        <tr>
                          <th style="width: 10%;">Code</th>
                          <th style="width: 30%;">Description</th>
                          <th style="text-align: right; width: 10%;">Free Qty</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in returnFreeItems" :key="index">
                          <td>{{ item.ProductCode }}</td>
                          <td>{{ item.ProductName }}</td>
                          <td style="text-align: right;">{{ item.BonusQTY }}</td>
                        </tr>
                        </tbody>
                      </table>
                    </div>

                </div>
                <div class="col-md-3" style="margin-left: 0; padding-left: 5px;">
                    <div style="padding: 5px; border: 1px dotted; margin-bottom: 5px;">
                        <div style="text-align: center;">Invoice Value</div>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                            <tbody>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">MRP Total</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(invoiceSummary.TP).toFixed(2)}}</td>
                                </tr>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">(+) VAT/Tax</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(invoiceSummary.VAT).toFixed(2)}}</td>
                                </tr>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">(-) Discount</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(invoiceSummary.Discount).toFixed(2)}}</td>
                                </tr>
                                <tr>
                                    <td colspan="3"><div style="border-bottom: 3px solid black; margin-top: 5px; margin-left: 10px;"></div></td>
                                    <!-- <td><div style="border-bottom: 1px dotted black; margin-top: 5px; margin-left: 10px;"></div></td>
                                    <td>
                                        <div style="border-bottom: 1px dotted black; margin-top: 5px; margin-left: 10px;"></div>
                                    </td> -->
                                </tr>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">Bill Amount</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(invoiceSummary.NSI).toFixed(2)}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 5px; border: 1px dotted; margin-bottom: 5px;">
                        <div style="text-align: center;">Return Value</div>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                            <tbody>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">MRP Total</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(calculateReturnSummary.TP).toFixed(2)}}</td>
                                </tr>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">(+) VAT/Tax</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(calculateReturnSummary.VAT).toFixed(2)}}</td>
                                </tr>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">(-) Discount</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(calculateReturnSummary.Discount).toFixed(2)}}</td>
                                </tr>
                                <tr>
                                    <td colspan="3"><div style="border-bottom: 3px solid black; margin-top: 5px; margin-left: 10px;"></div></td>
                                </tr>
                                <tr style="font-weight: 600;">
                                    <td style="text-align: left;">Return TK</td>
                                    <td>: </td>
                                    <td style="text-align: right;">{{parseFloat(calculateReturnSummary.Return).toFixed(2)}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 15px; border: 1px dotted">
                        <div class="print-btn pt-3" style="text-align: center;">
                            <button @click="addToInvoice" ref="printAddButton" class="btn btn-primary">Add to Invoice</button>
                        </div>
                        <div class="print-btn pt-3" style="text-align: center;">
                            <button @click="closeModal" class="btn btn-danger">Close</button>
                        </div>
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
import {Common} from "../../mixins/common";
import {mapGetters} from "vuex";

  export default {
    mixins: [Common],
    data() {
      return {
        products: [],
        selectedReturnRowIndex: -1,
        invSearchProduct: '',
        searchType: 'ProductName',
        isLoading: false,
        selectedProducts: [],
        returnFreeItems: [],
        itemsToShow: 20,
        lastIndex: 0,
        selectedType: '',
      }
    },
    computed: {
      ...mapGetters(['returnProducts', 'invoiceSummary', 'calculateReturnSummary']),
    },
    created() {
    },
    mounted() {
      this.$el.focus();
      bus.$on('return-inv-list-modal', (selectedType) => {
        // if (row) {
        //   this.selectedType = selectedType
        //   this.selectedProducts = this.products.slice(0, this.itemsToShow);
        //   this.lastIndex = this.itemsToShow;
        // }
        this.selectedReturnRowIndex = -1
        this.selectedType = selectedType
        this.selectedProducts = this.products.slice(0, this.itemsToShow);
        this.lastIndex = this.itemsToShow;
        $("#returnModal").modal("toggle");
        const returnSearchField = document.getElementById("invoiceSearchInput");
        returnSearchField.focus();
      })

      $(document).keydown((event) => {
        if (event.keyCode == 27 && (this.selectedType == 'return' || this.selectedType == 'print')) {      // Esc
          this.closeModal()
        }
        else if (event.keyCode == 38 && this.selectedType == 'print') {      // up Press
          event.preventDefault();
          this.selectedType = 'return'
          this.moveSelection('up')
        }
        else if (event.keyCode == 13 && this.selectedType == 'print') {      // Enter Press for print
          event.preventDefault();
          this.addToInvoice()
        } 
        else if (event.keyCode == 40 && this.selectedType == 'print') {      // Down Press for print
          event.preventDefault();
        } 
      });
    },
    beforeDestroy() {
    },
    methods: {

      handleScroll() {
        const tableContainer = this.$refs.tableContainer;
        const scrollHeight = tableContainer.scrollHeight;
        const clientHeight = tableContainer.clientHeight;
        const scrollTop = tableContainer.scrollTop;

        if (scrollTop + clientHeight >= 0.8 * scrollHeight) {
          this.loadNextPage();
        }
      },

      loadNextPage() {
        let nextBatch,products;
        if(this.searchProduct){
          if(this.searchType == 'BarCode'){
            const inputSearch = this.searchProduct;
            products = this.products.filter(product => product.BarCode.includes(inputSearch));
          }
          if(this.searchType == 'ProductName'){
            const inputSearch = this.searchProduct.toLowerCase();
            products = this.products.filter(product => product.ProductName.toLowerCase().includes(inputSearch));
          }
          if(this.searchType == 'ProductCode'){
            const inputSearch = this.searchProduct.toLowerCase();
            products = this.products.filter(product => product.ProductCode.toLowerCase().includes(inputSearch));
          }
        }
        else{
          products = this.products
        }
        nextBatch = products.slice(this.lastIndex, this.lastIndex + 20);
        this.selectedProducts = this.selectedProducts.concat(nextBatch);
        this.lastIndex += 20;
      },

      filteredReturnProducts() {
        this.selectedReturnRowIndex = -1
        this.axiosPost('get-return-invoice-product', {
            invoiceNo: this.invSearchProduct,
        }, (response) => {
            this.returnFreeItems = []
            if(response.data1.length > 0 && response.data2.length > 0){
              response.data1.forEach((product, i) => {
                if(Number(product.BonusQTY) != 0){
                  this.returnFreeItems.push(product)
                }
              })
              this.$store.commit('syncReturnInvoiceProducts', response.data1);
              this.$store.commit('syncReturnSummary', response.data2[0]);
            }
            else{
                this.errorNoti('No invoice found!');    
            }
        }, (error) => {
            this.errorNoti(error);
        })
      },

      updateTotal(prod) {
        let returnProducts = this.returnProducts

        if(prod.PackSize == 'EA' || prod.PackSize == 'BOX'){
          if(prod.ReturnQty < 0) {
            prod.ReturnQty = 0
            returnProducts = returnProducts.map((product)=>{
              if(prod.ProductCode == product.ProductCode) product.ReturnQty = prod.ReturnQty
              return product
            })
          }
          else{
            if(prod.ReturnQty < 1){
              // prod.ReturnQty = 0
              returnProducts = returnProducts.map((product)=>{
                if(prod.ProductCode == product.ProductCode) product.ReturnQty = 0
                return product
              })
            }
             else{
            //   prod.ReturnQty = parseInt(prod.ReturnQty);
              returnProducts = returnProducts.map((product)=>{
                if(prod.ProductCode == product.ProductCode) product.ReturnQty = parseInt(prod.ReturnQty,10)
                return product
              })
             }
          }
        }
        else{
          if (prod.ReturnQty == '') {
            prod.ReturnQty = 0
            returnProducts = returnProducts.map((product)=>{
              if(prod.ProductCode == product.ProductCode) product.ReturnQty = prod.ReturnQty
              return product
            })
          }
        }

        // if (prod.ReturnQty < 0) {
        //   prod.ReturnQty = 0
        //   returnProducts = returnProducts.map((product)=>{
        //     if(prod.ProductCode == product.ProductCode) product.ReturnQty = prod.ReturnQty
        //     return product
        //   })
        // }

        if (parseFloat(prod.ReturnQty) > parseFloat(prod.SalesQTY)) {
          this.errorNoti('Exceed the Qty limit!')
          returnProducts = returnProducts.map((product, i) => {
            if(prod.ProductCode == product.ProductCode) product.ReturnQty = (prod.ReturnQty.toString()).replace(/.$/, "")
            return product
          })
        }

        returnProducts = returnProducts.map((product)=>{
            if(prod.ProductCode == product.ProductCode) product.ReturnAmt = prod.ReturnQty * prod.SalesTP
            return product
        })

        this.$store.commit('syncReturnInvoiceProducts', returnProducts);
      },

      closeModal() {
        this.$store.commit('syncReturnInvoiceProducts', []);
        this.returnFreeItems = []
        this.invSearchProduct = ''
        this.selectedType = 'cart'
        $("#returnModal").modal("hide");
        bus.$emit('selected-return-product');
        this.focusBarcodeInput();
      },

      addToInvoice(){
        $("#returnModal").modal("hide");
        let returnProducts = this.returnProducts
        const filteredProducts = returnProducts.filter(product => product.ReturnQty > 0);
        this.$store.commit('syncReturnInvoiceProducts', filteredProducts);
        bus.$emit('selected-return-product', this.invSearchProduct);
        this.invSearchProduct = ''
        this.focusBarcodeInput();
      },

      focusBarcodeInput() {
        const barcodeInput = document.getElementById("barcodeSearchInput");
        if (barcodeInput) {
            barcodeInput.focus();
        }
      },
      selectRow(index) {
        this.selectedReturnRowIndex = index;
      },
      setDefaultSelection() {
        if (this.selectedReturnRowIndex === -1) {
          this.selectedReturnRowIndex = 0;
        }
      },
      moveSelection(direction) {
        if (direction === 'down' && this.selectedReturnRowIndex <= this.returnProducts.length - 1) {
          if(this.selectedReturnRowIndex == this.returnProducts.length - 1){
            this.selectedReturnRowIndex = this.returnProducts.length + 1
            this.selectedType = 'print'
            this.$refs.printAddButton.focus();
          }
          else{
            this.selectedReturnRowIndex++;
            $("#returnqty-" + this.selectedReturnRowIndex).focus();
            $("#returnqty-" + this.selectedReturnRowIndex).select()
            this.scrollIntoView(this.selectedReturnRowIndex);
          }
        } 
        else if (direction === 'up' && this.selectedReturnRowIndex > 0) {
          if(this.selectedReturnRowIndex >= this.returnProducts.length){
            this.selectedReturnRowIndex = this.returnProducts.length -1
            $("#returnqty-" + this.selectedReturnRowIndex).focus();
            $("#returnqty-" + this.selectedReturnRowIndex).select()
            this.selectedType = 'return'
            this.$refs.printAddButton.blur();
          }
          else{
            this.selectedReturnRowIndex--;
            $("#returnqty-" + this.selectedReturnRowIndex).focus();
            $("#returnqty-" + this.selectedReturnRowIndex).select()
            this.scrollIntoView(this.selectedReturnRowIndex);
          }
        }
      },
      scrollIntoView(index) {
        const selectedRow = this.$el.querySelector('.selected');
        if (selectedRow) {
          selectedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      },
      
    },
  };
  </script>
<style scoped>
.selected-row {
  background-color: #626ed4;
  color: #fff;
}

button:focus {
  outline: 3px dotted green;
}
</style>