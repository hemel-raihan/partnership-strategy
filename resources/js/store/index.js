import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);
import actions from './actions'
import getters from './getters'
import {mutations} from './mutations'

const state = {
    isSubmitButtonLoading: false,
    outletCode: '',
    outletName: '',
    status:[],
    me: {},
    allProducts: [],
    outlets:[],
    cartProducts:[],
    customerInfo:{},
    customerDiscountInfo:{},
    customerInfoStatus:false,
    tenders:[],
    summary: {},
    disabledTender: 0,
    freeItems: [],
    discountData: [],
    returnProducts: [],
    invoiceSummary: {
        TP: 0,
        VAT: 0,
        Discount: 0,
        NSI: 0,
    },
    returnSummary: {
        TP: 0,
        VAT: 0,
        Discount: 0,
        Return: 0,
    },
    lastInvoiceNo: '',
    selectedProducts: [],
    itemsToShow: 20,
    lastIndex: 0
}

export default new Vuex.Store({
    namespaced: true,
    state,
    getters,
    mutations,
    actions
})
