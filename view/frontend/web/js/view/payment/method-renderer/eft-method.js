define(
    [
        'jquery',
        'underscore',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'mage/validation',
        'mage/url'
    ],
    function (
        $,
        _,
        quote,
        urlBuilder,
        storage,
        customerData,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        accountData,
        checkoutData,
        url) {
        
        'use strict';

        return Component.extend({

            defaults: {
                template: 'Teknepay_Teknepay/payment/eft',
                transitNumber: '',
                institutionNumber: '',
                accountNumber: '',
                accountType: ''
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'transitNumber',
                        'institutionNumber',
                        'accountNumber',
                        'accountType'
                    ]);
                return this;
            },

            getCode: function () {
                return 'teknepay_eft';
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
 
 
            initialize: function() {
                var self = this;
                this._super();
 
                //Set routing number to data object
                this.transitNumber.subscribe(function(value) {
                    accountData.transitNumber = value;
                });

                //Set institution number to data object
                this.institutionNumber.subscribe(function(value) {
                    accountData.institutionNumber = value;
                });
 
                //Set account number to data object
                this.accountNumber.subscribe(function(value) {
                    accountData.accountNumber = value;
                });

                //Set account type to data object
                this.accountType.subscribe(function(value) {
                    accountData.accountType = value;
                });
            },

            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'transit_number': accountData.transitNumber,
                        'institution_number': accountData.institutionNumber,
                        'account_number': accountData.accountNumber,
                        'account_type': accountData.accountType,
                        'payment_type': 'EFT'
                    }
                };
            },

            placeOrder: function (data, event) {


                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },

            getAccountTypes: function() {
                return window.checkoutConfig.payment.teknepay_eft.accountTypes['teknepay_eft'];
            },

            getAccountTypesValues: function() {
                return _.map(this.getAccountTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
 
            isActive: function () {
                return true;
            },

            isPlaceOrderActionAllowed: function() {
                return true;
            },

            preparePayment: function (data) {
                this.validate();
                this.placeOrder();
            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('teknepay/eft/index'));
            }
        });
    }
);