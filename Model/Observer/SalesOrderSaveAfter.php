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

namespace Teknepay\Teknepay\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Teknepay\Teknepay\Helper\Debugger;
use \Rpl\Rpl\Helper\Sanitizer;

class SalesOrderSaveAfter implements ObserverInterface
{
    protected $_invoiceService;
    protected $_transactionFactory;
    protected $_moduleHelper;
    protected $_configHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
      \Magento\Sales\Model\Service\InvoiceService $invoiceService,
      \Magento\Framework\DB\TransactionFactory $transactionFactory,
      \Teknepay\Teknepay\Helper\Data $moduleHelper
    ) {
        $this->_invoiceService = $invoiceService;
        $this->_transactionFactory = $transactionFactory;
        
        $this->_moduleHelper = $moduleHelper; 
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quoteId = $order->getQuoteId();
        $payment = $order->getPayment();
        $transactionId = $payment->getTransactionId();
        $ccOwner = strval($order->getBillingAddress()->getFirstname()) . " " . strval($order->getBillingAddress()->getLastname());
        
        $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();

        $this->_configHelper = $this->_moduleHelper->getMethodConfig(
            $payment_method_code
        ); 

        try {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            # sanitize 'sales_order_payment.additional_information' 
            $tableNameSOP = $resource->getTableName('sales_order_payment'); 

            $sqlGetSOPData = "SELECT entity_id, additional_information FROM " . $tableNameSOP . " WHERE method = '" . $payment_method_code . "' AND last_trans_id = " . $transactionId;  
            $resultSOP = $connection->fetchAll($sqlGetSOPData); 

            if(count($resultSOP)) {
                foreach($resultSOP as $valueResultSOP) {
                    $arrayAdditionalInformationSOP = json_decode($valueResultSOP['additional_information'], true);
                    $sqlSOP = "UPDATE " . $tableNameSOP . " SET additional_information = '" . json_encode(Sanitizer::sanitizeRequest($arrayAdditionalInformationSOP)) . "' WHERE method = '" . $payment_method_code . "' AND entity_id = " . $valueResultSOP['entity_id'];
                    $connection->query($sqlSOP);
                }
            }

            # sanitize 'quote_payment.additional_information' 
            $tableNameQP = $resource->getTableName('quote_payment'); 

            $sqlGetQPData = "SELECT payment_id, additional_information FROM " . $tableNameQP . " WHERE method = '" . $payment_method_code . "' AND quote_id = " . $quoteId;  
            $resultQP = $connection->fetchAll($sqlGetQPData); 

            if(count($resultQP)) {
                foreach($resultQP as $valueResultQP) {
                    $arrayAdditionalInformationQP = json_decode($valueResultQP['additional_information'], true);
                    $sqlQP = "UPDATE " . $tableNameQP . " SET additional_information = '" . json_encode(Sanitizer::sanitizeRequest($arrayAdditionalInformationQP)) . "' WHERE method = '" . $payment_method_code . "' AND payment_id = " . $valueResultQP['payment_id'];
                    $connection->query($sqlQP);
                }
            }

            if($payment_method_code == 'teknepay_creditcard') {

                # store into 'quote_payment'
                $sqlStoreQP = "UPDATE " . $tableNameQP . " SET cc_last_4 = '" . substr($arrayAdditionalInformationQP['cc_number'], -4, 4) . "', cc_owner = '" . $ccOwner . "', cc_type = '" . $arrayAdditionalInformationQP['cc_type'] . "', cc_exp_month = '" . $arrayAdditionalInformationQP['cc_exp_month'] . "', cc_exp_year = '" . $arrayAdditionalInformationQP['cc_exp_year'] . "' WHERE method = '" . $payment_method_code . "' AND quote_id = '" . $quoteId . "'";
                $connection->query($sqlStoreQP);

                # store into 'sales_order_payment'
                $sqlStoreSOP = "UPDATE " . $tableNameSOP . " SET cc_last_4 = '" . substr($arrayAdditionalInformationSOP['cc_number'], -4, 4) . "', cc_owner = '" . $ccOwner . "', cc_type = '" . $arrayAdditionalInformationSOP['cc_type'] . "', cc_exp_month = '" . $arrayAdditionalInformationSOP['cc_exp_month'] . "', cc_exp_year = '" . $arrayAdditionalInformationSOP['cc_exp_year'] . "'  WHERE method = '" . $payment_method_code . "' AND last_trans_id = '" . $transactionId . "'";
                $connection->query($sqlStoreSOP);

            }

        } catch (\Exception $e) {
            Debugger::info($e->getMessage(), 'Exception message: ');
            return null;
        }

        Debugger::info($this->_configHelper->getGenerateInvoice(), "GENERATE INVOICE ENABLED [METHOD: " . $payment_method_code . "]: ");

        try {
            if(!$order->canInvoice() || !$this->_configHelper->getGenerateInvoice()) {
                return null;
            }
            if(!$order->getState() == 'new') {
                return null;
            }

            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();

            $transaction = $this->_transactionFactory->create()
              ->addObject($invoice)
              ->addObject($invoice->getOrder());

            $transaction->save();

        } catch (\Exception $e) {
            $order->addStatusHistoryComment('Exception message: '.$e->getMessage(), false);
            $order->save();
            return null;
        }
    }
}