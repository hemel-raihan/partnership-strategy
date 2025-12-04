// import Vue from 'vue'
// import Toaster from 'v-toaster'
// import 'v-toaster/dist/v-toaster.css'

// Vue.use(Toaster, {timeout: 5000})

function setCustomer(state,customer){
    state.customerInfo = {
        CustomerName: customer.CustomerName,
        LoyaltyPoints: customer.LoyaltyPoints,
        LoyaltyAmount: customer.LoyaltyAmount,
        BaseOutlet: customer.BaseOutlet,
        ConvertionRate: customer.ConvertionRate,
        CustomerCode : customer.CustomerCode,
        TenderDisable: customer.TenderDisable,
        DiscountPerc: customer.DiscountPerc
    }

    localStorage.setItem('CustomerInformation', JSON.stringify(state.customerInfo));
}

export const mutations = {
    submitButtonLoadingStatus(state, payload) {
        state.isSubmitButtonLoading = payload
    },
    supportingData(state, payload) {
        state.outlets = payload.depot
    },
    me(state, payload) {
        state.me = payload
    },
    syncAllProducts(state, products){
      if(products.length > 0){
        state.allProducts = products
      }
    },
    updateCartProductsQty(state,selectproduct){ 
      state.cartProducts = state.cartProducts.map((product)=>{
        if(
          selectproduct.ProductCode == product.ProductCode &&
          selectproduct.InvoiceType == product.InvoiceType
        ){
          product.Quantity = selectproduct.Quantity
        }
        return product
      })
      localStorage.setItem('cartProducts', JSON.stringify(state.cartProducts))
    },

    syncDeleteCartProducts(state,product){
      state.cartProducts = state.cartProducts.filter((cart)=> cart.ProductCode !== product.ProductCode || cart.InvoiceType !== product.InvoiceType)
      localStorage.setItem('cartProducts', JSON.stringify(state.cartProducts))
    },

    syncCartProducts(state,selectproduct){
      if(selectproduct.length !== 0){
        const existingCartItem = state.cartProducts.find(item => 
          item.ProductCode === selectproduct.ProductCode && 
          item.InvoiceType === selectproduct.InvoiceType
        )
        if(existingCartItem){
          state.cartProducts = state.cartProducts.map((product)=>{
            if(
              selectproduct.ProductCode == product.ProductCode && 
              selectproduct.InvoiceType === product.InvoiceType
            ){
              product.Quantity = parseFloat(product.Quantity) + 1
            }
            return product
          })
        }
        else{
            // state.cartProducts.push(selectproduct)
            state.cartProducts.push(JSON.parse(JSON.stringify(selectproduct)));
        }
      }
      else{
        state.cartProducts = []
      }

      localStorage.setItem('cartProducts', JSON.stringify(state.cartProducts))
      // Force summary recalculation
      state.summary = this.getters.calculateSummary
    },

    syncCartIncDecre(state,payload){
        let selectproduct = payload.product, qty = payload.qty
        state.cartProducts = state.cartProducts.map((product)=>{
            if(
              selectproduct.ProductCode == product.ProductCode &&
              selectproduct.InvoiceType == product.InvoiceType
            ){
              product.Quantity = parseFloat(qty) + parseFloat(product.Quantity)
            }
            return product
        })
        localStorage.setItem('cartProducts', JSON.stringify(state.cartProducts))
    },
    syncRecallCartProducts(state, products){
      if(products && products.length > 0){
        products.forEach((product, i) => {
          state.cartProducts.push(
            {
              AllowNegative:"Y",
              BarCode: product.ProductCode,
              DisableStatus: product.InvoiceType == 'R' ? "1" : "0",
              Flag: "",
              PackSize: product.PackSize,
              ProductCode: product.ProductCode,
              ProductName: product.ProductName,
              Quantity: product.Quantity,
              StockQuantity: product.StockQuantity,
              UnitPrice: product.UnitPrice,
              // SalesTP: product.SalesTP,
              VATPerc: product.VATPerc,
              VATDiscPerc: product.VATDiscPerc,
              Discount: product.Discount,
              VATDiscount: product.VATDiscount,
              ReturnDiscount: product.InvoiceType == 'R' ? true : false,
              InvoiceType: product.InvoiceType
            }
          )
        })
        localStorage.setItem('cartProducts', JSON.stringify(products))
      }
    },
    syncLocalStorageData(state){
      const storedCartData = JSON.parse(localStorage.getItem('cartProducts'));
      if (storedCartData && storedCartData.length > 0) {
        state.cartProducts = storedCartData;
        //this.calculateSummary()
      }
      const storedCustomerData = JSON.parse(localStorage.getItem('CustomerInformation'));
      if (storedCustomerData) {
        state.customerInfo = {}
        state.customerInfoStatus = true;
        setCustomer(state,storedCustomerData)
      }
      else{
        state.customerInfo = ''
        state.customerInfoStatus = false;
      }
      const storedCustomerDiscountData = JSON.parse(localStorage.getItem('CustomerDiscountInformation'));
      if (storedCustomerDiscountData) {
        state.customerDiscountInfo = storedCustomerDiscountData;
      }
      const storedTenderData = JSON.parse(localStorage.getItem('Tenders'));
      if (storedTenderData && storedTenderData.length > 0) {
        state.tenders = storedTenderData;
      }
      const storedFreeItem = JSON.parse(localStorage.getItem('FreeItems'));
      if (storedFreeItem && storedFreeItem.length > 0) {
        state.freeItems = storedFreeItem;
      }
      const storedLastInvoice = JSON.parse(localStorage.getItem('lastInvoiceNo'));
      if (storedLastInvoice) {
        state.lastInvoiceNo = storedLastInvoice;
      }
    },
    syncCustomerData(state, customerInfo){
      let customer = customerInfo.customer
      let discountInfo = customerInfo.discountInfo
      if(customer){
        if(customer.CustomerType !== null && customer.ActiveStatus !== 'N') {
          state.customerInfoStatus = true;
          setCustomer(state,customer)
          if(discountInfo.OfferEligible == 'Y'){
            state.customerDiscountInfo = discountInfo
            localStorage.setItem('CustomerDiscountInformation', JSON.stringify(state.customerDiscountInfo));
          }
        }
        else{
          state.customerInfoStatus = true;
          state.customerInfo = {
            CustomerCode : customer.CustomerCode,
            status: 'Customer Not Found!'
          }
          state.customerDiscountInfo = ''
          localStorage.setItem('CustomerInformation', null);
          localStorage.setItem('CustomerDiscountInformation', null);
        }
      }
      else{
        state.customerInfo = {
          CustomerCode : customer.CustomerCode
        }
        state.customerInfoStatus = false;
        state.customerDiscountInfo = ''
        localStorage.setItem('CustomerInformation', null);
        localStorage.setItem('CustomerDiscountInformation', null);
      }
    },
    updateCustomerCodeAfterRecall(state, customerCode){
      state.customerInfo = {
        CustomerCode : customerCode
      }
      state.customerInfoStatus = true;
    },
    syncTenderData(state, tender){
      if (tender.length > 0) {
        state.tenders = tender;
      }
    },
    syncDisabledTender(state, value){
        state.disabledTender = value;
    },

    // calculateTenderValue(state,payload = false) {
    //     let loyaltyAmt = 0
    //     state.tenders.forEach((data) => {
    //       if(data.TenderID == 'LCUS'){
    //         loyaltyAmt = data.TenderValue
    //       }
    //     })
    //     let mrpTotal =  state.cartProducts.reduce((acc, product) => acc + product.UnitPrice * product.Quantity, 0)
    //     let calculateTotal = ((mrpTotal - state.summary.discount) + state.summary.vatTax) - loyaltyAmt;
    //     state.tenders.map((tender, index) => {
    //       if(!payload){  
    //         if (tender.TenderID == 'CASH') {
    //           tender.TenderValue = Math.round(calculateTotal)
    //         }
    //         else if (tender.TenderID == 'RDIF') {
    //             const rounded = Math.round(calculateTotal);
    //             tender.TenderValue = (rounded - calculateTotal).toFixed(2);
    //         }
    //         else if (tender.TenderID == 'LCUS') {
    //             tender.RefColumn = 'Y';
    //         }
    //         else {
    //             if(tender.TenderValue){
    //               tender.TenderValue = parseFloat(tender.TenderValue) 
    //             }else{
    //               tender.TenderValue = 0.00
    //             } 
    //         }
    //       }
    //       tender.disableStatus = state.disabledTender == 0 || tender.Locked == 'Y' || tender.TenderID == 'LCUS'
    //       tender.disableRefStatus = state.disabledTender == 0 || tender.RefColumn == 'N' || (tender.TenderID == 'LCUS' && state.customerInfo.TenderDisable == 'Y') || (tender.TenderID == 'LCUS' && state.outletCode != state.customerInfo.BaseOutlet)
    //     })

    //     localStorage.setItem('Tenders', JSON.stringify(state.tenders));
    // },

    calculateTenderValue(state, payload = false) {
      const loyaltyTender = state.tenders.find(t => t.TenderID === 'LCUS');
      const loyaltyAmt = loyaltyTender ? parseFloat(loyaltyTender.TenderValue) || 0 : 0;

      // 2. Calculate totals
      // const mrpTotal = state.cartProducts.reduce((acc, product) => acc + product.UnitPrice * product.Quantity, 0);
      let mrpTotal = state.cartProducts.reduce((acc, product) => {
        const price = parseFloat(product.UnitPrice) || 0;
        const qty = parseFloat(product.Quantity) || 0;
        return acc + price * qty;
      }, 0);

      const discount = parseFloat(state.summary.discount) || 0;
      // const vatTax = parseFloat(state.summary.vatTax) || 0;

      let calculateTotal = (mrpTotal - discount) - loyaltyAmt;

      // 3. Update tenders
      state.tenders.forEach(tender => {
        if (!payload) {
          switch (tender.TenderID) {
            case 'CASH':
              tender.TenderValue = Math.round(calculateTotal);
              break;
            case 'RDIF': {
              const rounded = Math.round(calculateTotal);
              tender.TenderValue = Number((rounded - calculateTotal).toFixed(2)); // always number
              break;
            }
            case 'LCUS':
              tender.RefColumn = 'Y';
              break;
            default:
              tender.TenderValue = tender.TenderValue ? Number(tender.TenderValue) : 0.00;
              break;
          }
        }

        // 4. Lock / disable logic
        tender.disableStatus = (
          state.disabledTender === 0 ||
          tender.Locked === 'Y' ||
          tender.TenderID === 'LCUS'
        );

        tender.disableRefStatus = (
          state.disabledTender === 0 ||
          tender.RefColumn === 'N' ||
          (tender.TenderID === 'LCUS' && state.customerInfo.TenderDisable === 'Y') ||
          (tender.TenderID === 'LCUS' && state.outletCode !== state.customerInfo.BaseOutlet)
        );
      });

      // 5. Persist
      localStorage.setItem('Tenders', JSON.stringify(state.tenders));
    },

    // syncDiscountData(state, discountData){
    //   state.cartProducts  = state.cartProducts.map((product)=>{
    //     if(!product.ReturnDiscount){
    //       product.Discount = 0
    //       product.DiscountID = ''
    //     }
    //     return product
    //   })
    //   state.freeItems = []
    //   discountData.forEach((discount)=>{
    //     if(discount.calculate_by == 'Bonus'){
    //         state.freeItems.push(discount)
    //     }
    //     else{
    //       state.cartProducts  = state.cartProducts.map(product => {
    //         if(discount.productcode == product.ProductCode){
    //           if(discount.discount == '') discount.discount = 0
    //           if(discount.mandiscount == '') discount.mandiscount = 0

    //           product.Discount += parseFloat(discount.discount) + parseFloat(discount.mandiscount);
    //           product.DiscountID = discount.discount_id
    //         }
    //         return product
    //       });
    //     }
    //   })
    //   state.discountData = discountData
    //   localStorage.setItem('FreeItems', JSON.stringify(state.freeItems));
    // },


    syncDiscountData(state, discountData) {
      state.cartProducts = state.cartProducts.map(product => {
        if (!product.ReturnDiscount) {
          return { ...product, Discount: 0, DiscountID: '' };
        }
        return product;
      });

      state.freeItems = [];

      const discountMap = {};
      discountData.forEach(discount => {
        if (discount.calculate_by === 'Bonus') {
          state.freeItems.push(discount);
        } 
        else {
          const code = String(discount.productcode).trim();
          if (!discountMap[code]) discountMap[code] = [];
          discountMap[code].push(discount);
        }
      });

      // Apply discounts to cart products
      state.cartProducts = state.cartProducts.map(product => {
        const code = String(product.ProductCode).trim();
        const discounts = discountMap[code] || [];

        let totalDiscount = 0;
        let discountIDs = [];

        discounts.forEach(discount => {
          const disc = parseFloat(discount.discount) || 0;
          const mandisc = parseFloat(discount.mandiscount) || 0;
          const invoicedisc = parseFloat(discount.invoicediscount) || 0;

          totalDiscount += disc + mandisc + invoicedisc;
          
          if (discount.discount_id) discountIDs.push(discount.discount_id);
        });

        // Apply calculated discount only if not a ReturnDiscount
        if (!product.ReturnDiscount) {
          return {
            ...product,
            Discount: totalDiscount,
            DiscountID: discountIDs.join(',')
          };
        }

        return product;
      });

      state.discountData = discountData;
      localStorage.setItem('FreeItems', JSON.stringify(state.freeItems));
    },

    removeFreeItems(state){
      state.freeItems = []
      localStorage.removeItem('FreeItems');
    },

    syncDeleteFreeItemProducts(state,product){
      state.freeItems = state.freeItems.filter((item)=> item.productcode !== product.productcode)
      localStorage.setItem('FreeItems', JSON.stringify(state.freeItems))
    },

    syncReturnInvoiceProducts(state, products){
      state.returnProducts = []
      products = products.map((product, i) => {
        product.AllDiscount = parseFloat(product.Discount) + parseFloat(product.LoyaltyDiscount) + parseFloat(product.VATDiscount)
        return product
      })
      products.forEach((product, i) => {
        if(Number(product.BonusQTY) == 0 || Number(product.SalesQTY) != 0){
          state.returnProducts.push(product)
        }
      })
    },
    syncReturnSummary(state, summary){
      state.invoiceSummary = summary
    },
    syncCartReturnProducts(state,selectproduct){ 
      if(selectproduct.length !== 0){
        selectproduct.forEach(product => {
          const existingCartItem = state.cartProducts.find(item => item.ProductCode === product.ProductCode && item.InvoiceType === product.InvoiceType)
          if(existingCartItem){
            state.cartProducts = state.cartProducts.map((prod)=>{
              if(product.ProductCode === prod.ProductCode && product.InvoiceType === prod.InvoiceType){
                prod.Quantity = parseFloat(prod.Quantity) - product.ReturnQty
              }
              return prod
            })
          }
          else{
            state.cartProducts.push(
              {
                AllowNegative:"Y",
                BarCode: product.ProductCode,
                DisableStatus: "1",
                Flag: "",
                PackSize: product.PackSize,
                ProductCode: product.ProductCode,
                ProductName: product.ProductName,
                Quantity: -1 * product.ReturnQty,
                StockQuantity: product.StockQuantity,
                UnitPrice: product.UnitPrice,
                // SalesTP: product.SalesTP,
                VATPerc: product.VATPerc,
                VATDiscPerc: product.VATDiscPerc,
                Discount: -1 * product.Discount,
                VATDiscount: -1 * product.VATDiscount,
                ReturnDiscount: true,
                InvoiceType: 'R'
              }
            )
          }
        })
      }
      // else{
      //   state.cartProducts = []
      // }
      localStorage.setItem('cartProducts', JSON.stringify(state.cartProducts))
    },

    syncOutlet(state, data){
      state.outletCode = data.DepotCode
      state.outletName = data.DepotName
    },

    syncInvoiceNo(state, data){
      state.lastInvoiceNo = data
      localStorage.setItem('lastInvoiceNo', JSON.stringify(data))
    },

    productSearch(state, products){
      state.selectedProducts = []
      let nextBatch
      state.selectedProducts = products
      nextBatch = products.slice(0, state.lastIndex + 20);
      state.selectedProducts = state.selectedProducts.concat(nextBatch);
    },

    loadNextPage(state) {
      state.lastIndex += 20;
    },
}
