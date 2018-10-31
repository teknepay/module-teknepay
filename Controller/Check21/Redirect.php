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

namespace Teknepay\Teknepay\Controller\Check21;

/**
 * Return Action Controller (used to handle Redirects from the Payment Gateway)
 *
 * Class Redirect
 * @package Teknepay\Teknepay\Controller\Checkout
 */
class Redirect extends \Teknepay\Teknepay\Controller\AbstractCheckoutRedirectAction
{
    /**
     * Handle the result from the Payment Gateway
     *
     * @return void
     */
    public function execute()
    {
        switch ($this->getReturnAction()) {
            case 'success':
                $this->executeSuccessAction();
                break;

            case 'cancel':
                $this->getMessageManager()->addWarning(
                    __("You have successfully canceled your order")
                );
                $this->executeCancelAction();
                break;

            case 'failure':
                $this->getMessageManager()->addError(
                    __("Charge was declined. Please, contact your bank for more information or use different payment details.")
                );
                $this->executeCancelAction();
                break;

            default:
                $this->getResponse()->setHttpResponseCode(
                    \Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED
                );
        }
    }
}
