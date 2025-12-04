export default {
    me(state) {
        return state.me;
    },
    outlets(state) {
        return state.outlets;
    },
    cartProducts(state) {
        let products = state.cartProducts
        // logic
        return products;
    },
    customerInfo(state) {
        // if(!state.customerInfo || state.customerInfo == undefined || state.customerInfo == null) return ''
        // else return state.customerInfo
        return state.customerInfo
    },
    customerDiscountInfo(state){
      return state.customerDiscountInfo
    },
    customerCode(state) {
      return state.customerInfo ? state.customerInfo.CustomerCode : ''
    },
    customerInfoStatus(state) {
        return state.customerInfoStatus
    },

    calculateSummary(state) {
      let mrpTotal = state.cartProducts.reduce((acc, p) => acc + (parseFloat(p.UnitPrice) * parseFloat(p.Quantity)), 0);

      // -------- Customer Discount (basket level) --------
      let customerDiscountAMT = 0;
      if (state.customerDiscountInfo && mrpTotal >= state.customerDiscountInfo.MinBasketLimit) {
        if (state.customerDiscountInfo.DiscountType === '%') {
          customerDiscountAMT = (mrpTotal * parseFloat(state.customerDiscountInfo.DiscountAmount)) / 100;
        } else {
          customerDiscountAMT = parseFloat(state.customerDiscountInfo.DiscountAmount);
        }
      }

      let totalVAT = 0;
      let productDiscount = 0;
      let vatDiscount = 0;

      const updatedProducts = state.cartProducts.map(product => {
        let unitPrice = parseFloat(product.UnitPrice);
        let quantity = parseFloat(product.Quantity);
        let vatPerc = parseFloat(product.VATPerc) || 0;
        let vatDiscPerc = parseFloat(product.VATDiscPerc) || 0;
        let discount = parseFloat(product.Discount) || 0;

        // Loyalty allocation
        product.LoyaltyDiscount = customerDiscountAMT > 0 ? ((unitPrice * quantity) / mrpTotal) * customerDiscountAMT : 0;

        // Product Discount (for summary later)
        productDiscount += discount;

        // VAT calculation
        let singleVatDiscount = 0;
        if (vatPerc > 0) {
          product.UnitVat = (vatPerc * unitPrice) / 100;

          // VAT Discount allocation
          singleVatDiscount = ((quantity * unitPrice) - (discount + product.LoyaltyDiscount)) /
                              (100 + (vatPerc * (vatDiscPerc / 100))) * vatPerc * (vatDiscPerc / 100);

          product.SalesVat = singleVatDiscount;
          vatDiscount += singleVatDiscount;

          // Total VAT
          totalVAT += vatPerc * (quantity * unitPrice - (discount + product.LoyaltyDiscount + singleVatDiscount)) / 100;
        }

        // Final assignments
        product.VATDiscount = Number(singleVatDiscount.toFixed(2));
        product.TP = Number((unitPrice * quantity).toFixed(2));
        product.NSI = Number(((unitPrice * quantity) - (discount + product.LoyaltyDiscount + singleVatDiscount) + (vatPerc * quantity * unitPrice) / 100).toFixed(2));

        return product;
      });

      let totalDiscount = productDiscount + customerDiscountAMT + vatDiscount;

      let summary = {
        mrpTotal: Number(mrpTotal.toFixed(2)),
        sd: 0,
        vatPerc: 7.5, // <-- hardcoded, might need to be dynamic
        discount: Number(totalDiscount.toFixed(2)),
        invoiceDiscount: Number(productDiscount.toFixed(2)),
        totalLoyaltyDiscount: Number(customerDiscountAMT.toFixed(2)),
        totalVatDiscount: Number(vatDiscount.toFixed(2))
      };

      summary.afterDiscount = Number((mrpTotal - summary.discount).toFixed(2));
      summary.afterVat = Number(((mrpTotal - summary.discount) + totalVAT).toFixed(2));
      summary.total = Number((mrpTotal - summary.discount).toFixed(2));
      summary.totalRoundOff = Math.round(mrpTotal - summary.discount);

      state.summary = summary;
      return summary;
    },

    returnProducts(state){
      let products = state.returnProducts
      return products;
    },

    invoiceSummary(state){
      return state.invoiceSummary;
    },

    discountData(state){
      return state.discountData;
    },

    calculateReturnSummary(state) {
      let totalVAT = 0;
      state.returnProducts.forEach((value) => {
          if (value.ReturnQty > 0) {
            let calculateVat = (parseFloat(value.VAT) / parseFloat(value.SalesQTY)) * parseFloat(value.ReturnQty)
            totalVAT += calculateVat
          }
      })

      let mrpTotal =  state.returnProducts.reduce((acc, product) => acc + product.UnitPrice * product.ReturnQty, 0)
      let totalDiscount = 0 
      state.returnProducts.forEach((product) => {
        if (product.ReturnQty > 0) {
          // let calculateDiscount = (parseFloat(product.Discount) / parseFloat(product.SalesQTY)) * parseFloat(product.ReturnQty)
          totalDiscount += product.AllDiscount
        }
      })
      let summary = {
          TP: mrpTotal.toFixed(2),
          VAT: totalVAT,
          Discount: totalDiscount,
      }
      summary.Return = parseFloat((mrpTotal - summary.Discount) + totalVAT).toFixed(2)

      state.returnSummary = summary
      return summary
    },

    disabledTender(state) {
      return state.disabledTender
    },
    outletCode(state) {
      return state.outletCode
    },
    outletName(state) {
      return state.outletName
    },
    tenders(state) {
        return state.tenders
    },
    freeItems(state){
      return state.freeItems
    },
    allProducts(state){
      return state.allProducts
    },
    filteredProduct: (state) => (parameter='') => { 
        return state.allProducts
    },
    selectedProducts(state){
      // state.selectedProducts = state.allProducts.slice(0, state.itemsToShow);
      // state.lastIndex = state.itemsToShow;
      let nextBatch;
      if(state.selectedProducts.length > 0){
        nextBatch = state.selectedProducts.slice(state.lastIndex, state.lastIndex + 20);
        state.selectedProducts = state.selectedProducts.concat(nextBatch);  
      }
      else{
        nextBatch = state.allProducts.slice(state.lastIndex, state.lastIndex + 20);
        state.selectedProducts = state.selectedProducts.concat(nextBatch);
      }
      return state.selectedProducts
    },

    lastInvoiceNo(state){
      return state.lastInvoiceNo
    }
  }

  