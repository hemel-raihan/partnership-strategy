<template>
    <div class="modal" id="myModal" tabindex="-1" role="dialog"
     @keydown.down.prevent="moveSelection('down')"
     @keydown.up.prevent="moveSelection('up')"
     @focus="setDefaultSelection"
     @keydown.enter="addSelectedProductToCart">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <!-- <h5 class="modal-title">Modal Title</h5> -->
            <div class="barcode-search-bar" style="width: 40%;">
              <div class="form-group" style="margin-bottom: unset !important;">
                <select style="width: 200px !important;" v-model="searchType" class="form-control form-control-sm" id="user-type" name="user-type" >
                  <option value="">Select search type</option>
                  <option value="BarCode">Barcode</option>
                  <option value="ProductName">Product Name</option>
                  <option value="ProductCode">Product Code</option>
                </select>
              </div>
              <div class="form-group barcode-search">
                <input type="text" style="width:200px" :disabled="searchType===''" class="form-control form-control-sm"
                        id="SearchInput"
                        v-model="searchProduct"
                        @input="filteredProducts"
                        placeholder="Product Search">
              </div>      
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" @click="closeModal">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div @scroll="handleScroll" ref="tableContainer" class="search-product-list table-containerr" id="table-containerr">
                <table class="table table-sm  table-bordered sticky-header">
                  <thead>
                  <tr>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th>UOM</th>
                    <th style="text-align: right;">VAT Percentage</th>
                    <!-- <th style="text-align: right;">Quantity</th> -->
                    <th style="text-align: right;">Stock Qty</th>
                    <th style="text-align: right;">Stock Status</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr v-for="(product, index) in selectedProducts" :key="index" 
                      :class="{ 'selected': index === selectedRowIndex, 'selected-row': index === selectedRowIndex }"
                      @click="selectRow(index)"
                      tabindex="0"
                      >
                    <td>{{ product.ProductCode }}</td>
                    <td>{{ product.ProductName }}</td>
                    <td style="text-align: right;">{{ Number(product.UnitPrice).toFixed(2) }}</td>
                    <td>{{ product.PackSize }}</td>
                    <td style="text-align: right;">{{ Number(product.VATPerc).toFixed(2) }}</td>
                    <!-- <td style="text-align: right;">{{ Number(product.Quantity).toFixed(2) }}</td> -->
                    <td style="text-align: right;">{{ Number(product.StockQuantity).toFixed(2) }}</td>
                    <td style="text-align: right;">{{ product.Flag == 'Stock' ? 'Regular Stock' : 'Stock Clearance' }}</td>
                  </tr>
                  </tbody>
                </table>
                <div v-if="isLoading" class="loading-indicator">Loading...</div>
              </div>
          </div>
        </div>
      </div>
    </div>
  </template>
<script>
import {bus} from "../../app";
import {Common} from "../../mixins/common";
  export default {
    mixins: [Common],
    data() {
      return {
        products: [],
        tempProducts: [],
        selectedRowIndex: -1,
        searchProduct: '',
        searchType: 'ProductName',
        isLoading: false,
        selectedProducts: [],
        itemsToShow: 20,
        lastIndex: 0
      }
    },
    created() {
    },
    mounted() {
      this.$el.focus();
      bus.$on('product-search-modal', (row) => {
        if (row) {
          this.products = row
          this.selectedProducts = this.products.slice(0, this.itemsToShow);
          this.lastIndex = this.itemsToShow;
          this.tempProducts = row
          this.selectedRowIndex = -1
        }
        $("#myModal").modal("toggle");
        const productSearchField = document.getElementById("SearchInput");
        productSearchField.focus();
        const tableContainer = this.$refs.tableContainer;
        tableContainer.scrollTop = 0
      })
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

      filteredProducts() {
        this.selectedRowIndex = -1
        let nextBatch,products;
        if(this.searchProduct){
          const inputSearch = this.searchProduct.toLowerCase();
          if(this.searchType == 'BarCode'){
            products = this.products.filter(product => product.BarCode.includes(inputSearch));
          }
          else if(this.searchType == 'ProductName'){
            products = this.products.filter(product => product.ProductName.toLowerCase().includes(inputSearch));
          }
          else if(this.searchType == 'ProductCode'){
            products = this.products.filter(product => product.ProductCode.toLowerCase().includes(inputSearch));
          }
        }
        else{
          products = this.products
        }
        this.lastIndex = 0;
        this.selectedProducts = []
        nextBatch = products.slice(this.lastIndex, this.lastIndex + 20);
        this.selectedProducts = this.selectedProducts.concat(nextBatch);
        this.lastIndex += 20;
      },

      // closeModalAndFocusInput() {
      //   this.closeModal();
      //   this.focusBarcodeInput();
      // },
      closeModal() {
        $("#myModal").modal("hide");
        this.focusBarcodeInput();
      },
      focusBarcodeInput() {
        const barcodeInput = document.getElementById("barcodeSearchInput");
        if (barcodeInput) {
            barcodeInput.focus();
        }
      },
      selectRow(index) {
        this.selectedRowIndex = index;
      },
      setDefaultSelection() {
        if (this.selectedRowIndex === -1) {
          this.selectedRowIndex = 0;
        }
      },
      moveSelection(direction) {
        if (direction === 'down' && this.selectedRowIndex < this.products.length - 1) {
          this.selectedRowIndex++;
          this.scrollIntoView(this.selectedRowIndex);
        } else if (direction === 'up' && this.selectedRowIndex > 0) {
          this.selectedRowIndex--;
          this.scrollIntoView(this.selectedRowIndex);
        }
      },
      scrollIntoView(index) {
        const selectedRow = this.$el.querySelector('.selected');
        if (selectedRow) {
          selectedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      },
      addSelectedProductToCart() {
        if (this.selectedRowIndex >= 0) {
          const selectedProduct = this.selectedProducts[this.selectedRowIndex];
          bus.$emit('select-product-cart', selectedProduct);
          this.focusBarcodeInput();
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
</style>