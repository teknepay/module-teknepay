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

namespace Teknepay\Teknepay\Controller;

/**
 * Base Checkout Controller Class
 * Class AbstractCheckoutAction
 * @package Teknepay\Teknepay\Controller
 */
abstract class AbstractCheckoutAction extends \Teknepay\Teknepay\Controller\AbstractAction
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $_orderFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        parent::__construct($context, $logger);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Get an Instance of the Magento Checkout Session
     * @return \Magento\Checkout\Model\Session
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get an Instance of the Magento Order Factory
     * It can be used to instantiate an order
     * @return \Magento\Sales\Model\OrderFactory
     */
    protected function getOrderFactory()
    {
        return $this->_orderFactory;
    }

    /**
     * Get an Instance of the current Checkout Order Object
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        $orderId = $this->getCheckoutSession()->getLastRealOrderId();

        if (!isset($orderId)) {
            return null;
        }

        $order = $this->getOrderFactory()->create()->loadByIncrementId(
            $orderId
        );

        if (!$order->getId()) {
            return null;
        }

        return $order;
    }

    /**
     * Does a redirect to the Checkout Payment Page
     * @return void
     */
    protected function redirectToCheckoutFragmentPayment()
    {
        $this->_redirect('checkout', ['_fragment' => 'payment']);
    }

    /**
     * Does a redirect to the Checkout Success Page
     * @return void
     */
    protected function redirectToCheckoutOnePageSuccess()
    {
        $this->_redirect('checkout/onepage/success');
    }

    /**
     * Does a redirect to the Checkout Cart Page
     * @return void
     */
    protected function redirectToCheckoutCart()
    {
        $this->_redirect('checkout/cart');
    }
}
