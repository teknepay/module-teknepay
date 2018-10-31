
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'teknepay_creditcard',
                component: 'Teknepay_Teknepay/js/view/payment/method-renderer/creditcard-method'
            },
            {
                type: 'teknepay_check21',
                component: 'Teknepay_Teknepay/js/view/payment/method-renderer/check21-method'
            },
            {
                type: 'teknepay_eft',
                component: 'Teknepay_Teknepay/js/view/payment/method-renderer/eft-method'
            }
            
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
