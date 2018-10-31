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
        'Magento_Customer/js/model/customer',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/validation',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url'
    ],
    function ($, _, quote, urlBuilder, storage, customerData, Component, placeOrderAction, customer, creditCardData, ccForm, cardNumberValidator, validator, validation, checkoutData, additionalValidators, url) {
        
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Teknepay_Teknepay/payment/creditcard',
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardVerificationNumber: '',
                selectedCardType: null
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'selectedCardType'
                    ]);
                    
                return this;
            },

            getCode: function () {
                return 'teknepay_creditcard';
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
 
            initialize: function() {
                var self = this;
                this._super();
 
                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function(value) {
                    creditCardData.creditCardNumber = value;
                });
 
                //Set expiration year to credit card data object
                this.creditCardExpYear.subscribe(function(value) {
                    creditCardData.expirationYear = value;
                });
 
                //Set expiration month to credit card data object
                this.creditCardExpMonth.subscribe(function(value) {
                    creditCardData.expirationMonth = value;
                });
 
                //Set cvv code to credit card data object
                this.creditCardVerificationNumber.subscribe(function(value) {
                    creditCardData.cvvCode = value;
                });
            },

            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_month': creditCardData.expirationMonth,
                        'cc_exp_year': creditCardData.expirationYear,
                        'cc_number': creditCardData.creditCardNumber,
                        'payment_type': 'CC'
                    }
                };
            },
 
            isActive: function () {
                return true;
            },

            isPlaceOrderActionAllowed: function() {
                return true;
            },
 
            getCcAvailableTypes: function() {
                return window.checkoutConfig.payment.teknepay_creditcard.availableTypes['teknepay_creditcard'];
            },
 
            getCcMonths: function() {
                return window.checkoutConfig.payment.teknepay_creditcard.months['teknepay_creditcard'];
            },
 
            getCcYears: function() {
                return window.checkoutConfig.payment.teknepay_creditcard.years['teknepay_creditcard'];
            },
 
            getCcAvailableTypesValues: function() {
                return _.map(this.getCcAvailableTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function() {
                return _.map(this.getCcMonths(), function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function() {
                return _.map(this.getCcYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },

            preparePayment: function (data) {
                this.validate();
                this.placeOrder();
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
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('teknepay/creditcard/index'));
            }
        });
    }
);