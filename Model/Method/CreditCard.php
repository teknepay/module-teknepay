<?php

/**
 * This is a payment module for teknepay gateway.
 * Copyright (C) 2018  All copyrights reserved to Teknepay
 * 
 * This file is part of Teknepay/Teknepay.
 * 
 * Teknepay/Teknepay is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Teknepay\Teknepay\Model\Method;

use \Teknepay\Teknepay\Helper\Debugger;
use \Teknepay\Teknepay\Helper\Sanitizer;

class CreditCard extends \Magento\Payment\Model\Method\AbstractMethod
{
    use \Teknepay\Teknepay\Model\Traits\OnlinePaymentMethod;

    const CODE = 'teknepay_creditcard';

    protected $_code = self::CODE;
    protected $_isOffline = false;
    protected $_minOrderTotal = 0;
    protected $_maxOrderTotal = 0;
    protected $_supportedCurrencyCodes = array('USD','GBP','EUR','CAD');

     /**
     * Checkout constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\App\Action\Context $actionContext
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Teknepay\Teknepay\Helper\Data $moduleHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Action\Context $actionContext,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger  $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Teknepay\Teknepay\Helper\Data $moduleHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_actionContext = $actionContext;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_moduleHelper = $moduleHelper;
        $this->_configHelper =
            $this->getModuleHelper()->getMethodConfig(
                $this->getCode()
            );   

        Debugger::$debugEnabled = $this->_configHelper->getLoggingStatus();    
    }

    /**
     * Get Instance of the Magento Code Logger
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get Default Payment Action On Payment Complete Action
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;
    }

    /**
     * Create a Web-Payment Form Instance
     * @param array $data
     * @return \stdClass
     * @throws \Magento\Framework\Webapi\Exception
     */
    protected function checkout($data)
    {
      $transaction = new \Teknepay\GetCardPaymentToken;
    
      $transaction->paymentDetails->setToken($data['transaction']['Key']);
      $transaction->paymentDetails->setCardNumber($data['transaction']['CC_number']);
      $transaction->paymentDetails->setCardExpMonth($data['transaction']['CC_exp_month']);
      $transaction->paymentDetails->setCardExpYear($data['transaction']['CC_exp_year']);
      $transaction->paymentDetails->setCardCvv($data['transaction']['CVV']);
      $transaction->paymentDetails->setCardType($data['transaction']['CC_type']);
      $transaction->paymentDetails->setPaymentType($data['transaction']['PaymentType']);
      $transaction->paymentDetails->setTransactionType($data['transaction']['TransType']);
      $transaction->paymentDetails->setCustomerTransId($data['transaction']['CustomerTransID']);

      $transaction->money->setAmount($data['transaction']['Amount']);
      $transaction->money->setCurrency($data['transaction']['Currency']);
      $transaction->customer->setCustomerId($data['transaction']['CustomerID']);
      $transaction->customer->setFirstName($data['transaction']['FirstName']);
      $transaction->customer->setLastName($data['transaction']['LastName']);
      $transaction->customer->setHolder($data['transaction']['CardHolder']);
      $transaction->customer->setAddress($data['transaction']['Address']);
      $transaction->customer->setCity($data['transaction']['City']);
      $transaction->customer->setCountry($data['transaction']['Country']);
      $transaction->customer->setZip($data['transaction']['PostalCode']);
      $transaction->customer->setState($data['transaction']['State']);

      if (!empty($data['transaction']['Email'])) {
        $transaction->customer->setEmail($data['transaction']['Email']);
      }

      $transaction->customer->setPhone($data['transaction']['Phone']);

      $response = $transaction->submit();

      Debugger::info($response, "RESPONSE INTO CC Model: [" . __FUNCTION__ . " in file " . __FILE__ . "]");

      return $response;
    }

    /**
     * Order Payment
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        \Teknepay\Settings::$successRedirect = $this->getModuleHelper()->getReturnUrl( $this->getCode(), 'success' );
        \Teknepay\Settings::$failRedirect = $this->getModuleHelper()->getReturnUrl( $this->getCode(), 'failure' );

        $order = $payment->getOrder(); 
        $orderId = $order->getIncrementId();

        Debugger::info($amount, 'AMOUNT: ');
        Debugger::info($orderId, 'CURRENT ORDER ID: ');

        # get payment form values
        $submittedFormInfo = $this->getInfoInstance();
        $cardDetails = $submittedFormInfo->getAdditionalInformation();

        $key = MD5($this->getConfigHelper()->getGatewayProfileId()."|".MD5($this->getConfigHelper()->getGatewayPassword())."|".ltrim($orderId, '0')."|".number_format($amount, 2, '.', ''));
		$data = [
                    "authenticate" => [
                                        "user" => $this->getConfigHelper()->getGatewayUsername(), 
                                        "password" => $this->getConfigHelper()->getGatewayPassword()
                                    ],

                    "transaction" => [
                                        "ProfileID" => $this->getConfigHelper()->getGatewayProfileId(),
                                        "FirstName" => strval($order->getBillingAddress()->getFirstname()),
                                        "LastName" => strval($order->getBillingAddress()->getLastname()),
                                        "Email" => $order->getCustomerEmail(),
                                        "Phone" => preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', substr(preg_replace('/[^0-9]/', '',$order->getBillingAddress()->getTelephone()),-10)),
                                        "Address" => strval($order->getBillingAddress()->getStreetLine(1)),
                                        "City" => strval($order->getBillingAddress()->getCity()),
                                        "CardHolder" => strval($order->getBillingAddress()->getName()),
                                        "PostalCode" => strval($order->getBillingAddress()->getPostcode()),
                                        "Action" => "Sale",
                                        "PaymentType" => $cardDetails['payment_type'],
                                        "Amount" => number_format($amount, 2, '.', ''),
                                        "Country" => strval($order->getBillingAddress()->getCountryId()),
                                        "State" => strval($order->getBillingAddress()->getRegioncode()),
                                        "Currency" => strtoupper($order->getBaseCurrencyCode()),
                                        "CC_exp_month" => $cardDetails['cc_exp_month'],
                                        "CC_exp_year" => $cardDetails['cc_exp_year'],
                                        "CC_number" => $cardDetails['cc_number'],
                                        "CVV" => $cardDetails['cc_cid'],
                                        "CC_type" => $cardDetails['cc_type'],
                                        "TransType" => 'DT',
                                        "CustomerID" => ($order->getCustomerId() ? $order->getCustomerId() : 2),
                                        "CustomerTransID" => ltrim($orderId, '0'),
                                        "Key" => $key
                                    ]
        ];

        $this->getConfigHelper()->initGatewayClient();

        try {
            $responseObject = $this->checkout($data);

            $isTeknepaySuccessful =
                $responseObject->isSuccess() && !empty($responseObject->getRedirectUrl());

            if (!$isTeknepaySuccessful) {
                $errorMessage = $responseObject->getMessage();

                $this->getCheckoutSession()->setTeknepayLastCheckoutError(
                    $errorMessage
                );

                $this->getModuleHelper()->throwWebApiException($errorMessage);
            }

            if($responseObject->getGatewayStatus() == 1) {
                $payment->setTransactionId($responseObject->getGatewayTransactionId())->setIsTransactionClosed(false);
            } else {
                if($responseObject->getGatewayTransactionId()) {
                    $payment->setTransactionId($responseObject->getGatewayTransactionId());
                } else {
                    $payment->setSkipTransactionCreation(true);
                }
                
                #$payment->setIsTransactionPending(true);
                $payment->setIsFraudDetected(true);
                $payment->setIsTransactionClosed(true);
                #throw new \Magento\Framework\Validator\Exception(__('Charge was declined. Please, contact your bank for more information or use different payment details.'));
            }

            $sanitizedCardDetails = Sanitizer::sanitizeRequest($cardDetails);

            $this->getModuleHelper()->setPaymentTransactionAdditionalInfo(
                $payment,
                $responseObject, 
                $sanitizedCardDetails
            );

            $this->getCheckoutSession()->setTeknepayCheckoutRedirectUrl(
                $responseObject->getRedirectUrl()
            );

            return $this;
        } catch (\Exception $e) {
            $this->getLogger()->error(
                $e->getMessage()
            );

            $this->getCheckoutSession()->setTeknepayLastCheckoutError(
                $e->getMessage()
            );

            $this->getModuleHelper()->maskException($e);
        }
    }

    /**
     * Assign data to info model instance
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        // Call parent assignData
        parent::assignData($data);

        // Get Mage_Payment_Model_Info instance from quote 
        $info = $this->getInfoInstance();

        // Add some arbitrary post data to the Mage_Payment_Model_Info instance 
        // so it is saved in the DB in the 'additional_information' field        
        $info->setAdditionalInformation(
            [
                'cc_number' => $data['additional_data']['cc_number'],
                'cc_exp_month' => $data['additional_data']['cc_exp_month'],
                'cc_exp_year' => $data['additional_data']['cc_exp_year'],
                'cc_cid' => $data['additional_data']['cc_cid'],
                'cc_type' => $data['additional_data']['cc_type'],
                'payment_type' => $data['additional_data']['payment_type']
            ]
        );

        return $this;
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $this->_minOrderTotal = $this->getConfigData('min_order_total');
        if($quote && $quote->getBaseGrandTotal() < $this->_minOrderTotal) {
            return false;
        }

        if($this->getConfigData('max_order_total')) {
            $this->_maxOrderTotal = $this->getConfigData('max_order_total');
            if($quote && $quote->getBaseGrandTotal() > $this->_maxOrderTotal) {
                return false;
            }
        }

        return parent::isAvailable($quote);
    }
}
