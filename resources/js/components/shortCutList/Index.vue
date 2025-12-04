<template>
    <div class="modal" id="shortCutModal" tabindex="-1" role="dialog"
     >
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Shortcut List</h5>
            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" @click="closeModal">&times;</span>
            </button> -->
          </div>
          <div class="modal-body">
            <div class="summary-box">
                <div v-for="(list, i) in shortCutList" :key="i" class="summary-line">
                    <div class="row">
                    <div class="col-md-4">{{list.key}}</div>
                    <div class="col-md-2">=</div>
                    <div class="col-md-6" style="text-align: right;">{{list.details}}</div>
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
  export default {
    mixins: [Common],
    data() {
      return {
        shortCutList: [
            {key: 'F1', details: 'Help Show/Hide'},
            {key: 'F3', details: 'Product Return'},
            {key: 'F8', details: 'Calculate Discount/Bonus'},
            {key: 'F10', details: 'Recall Invoice'},
            {key: 'F11', details: 'Hold Invoice'},
            {key: 'F12', details: 'Save'},
            {key: 'CtrlL+L', details: 'Loyal Customer'},
            {key: 'Ctrl+P', details: 'Print Invoice'},
        ],
        selected: ''
      }
    },
    created() {
    },
    mounted() {
      this.$el.focus();
      bus.$on('shortcut-list-modal', (selectedType) => {
        this.selected = selectedType
        $('#shortCutModal').modal({backdrop: 'static', keyboard: false})
        $("#shortCutModal").modal("toggle"); 
      })

      $(document).keydown((event) => {
        if (event.keyCode == 27 && this.selected == 'shortcut') {      // Esc
          this.closeModal()
        }
      });
    },
    beforeDestroy() {
    },
    methods: {

      closeModal() {
        bus.$emit('hide-shortcut-modal');
        this.focusBarcodeInput();
      },

      focusBarcodeInput() {
        const barcodeInput = document.getElementById("barcodeSearchInput");
        if (barcodeInput) {
            barcodeInput.focus();
        }
      },
    },
  };
  </script>
<style scoped>
</style>