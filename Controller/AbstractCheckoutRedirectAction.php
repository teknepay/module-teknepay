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
 * Base Checkout Redirect Controller Class
 * Class AbstractCheckoutRedirectAction
 * @package Teknepay\Teknepay\Controller
 */
abstract class AbstractCheckoutRedirectAction extends \Teknepay\Teknepay\Controller\AbstractCheckoutAction
{
    /**
     * @var \Teknepay\Teknepay\Helper\Checkout
     */
    private $_checkoutHelper;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Teknepay\Teknepay\Helper\Checkout $checkoutHelper
    ) {
        parent::__construct($context, $logger, $checkoutSession, $orderFactory);
        $this->_checkoutHelper = $checkoutHelper;
    }

    /**
     * Get an Instance of the Magento Checkout Helper
     * @return \Teknepay\Teknepay\Helper\Checkout
     */
    protected function getCheckoutHelper()
    {
        return $this->_checkoutHelper;
    }

    /**
     * Handle Success Action
     * @return void
     */
    protected function executeSuccessAction()
    {
        if ($this->getCheckoutSession()->getLastRealOrderId()) {
            $this->getMessageManager()->addSuccess(__("Your payment is complete"));
            $this->redirectToCheckoutOnePageSuccess();
        }
    }

    /**
     * Handle Cancel Action from Payment Gateway
     */
    protected function executeCancelAction()
    {
        $this->getCheckoutHelper()->cancelCurrentOrder('Payment has failed.');
        $this->getCheckoutHelper()->restoreQuote();
        $this->redirectToCheckoutCart();
    }

    /**
     * Get the redirect action
     *      - success
     *      - cancel
     *      - failure
     *
     * @return string
     */
    protected function getReturnAction()
    {
        return $this->getRequest()->getParam('action');
    }
}
