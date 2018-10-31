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

namespace Teknepay\Teknepay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;
use \Teknepay\Teknepay\Helper\Debugger;
 
class CreditCardConfigProvider implements ConfigProviderInterface
{

    /**
    * @param CcConfig $ccConfig
    * @param Source $assetSource
    */
    public function __construct(
        \Magento\Payment\Model\CcConfig $ccConfig,
        Source $assetSource
    ) {
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }
 
    /**
    * @var string[]
    */
    protected $_methodCode = 'teknepay_creditcard';

    protected $_cardTypes = [
        "AE" => "American Express",
        "VI" => "Visa",
        "MC" => "MasterCard",
        "DI" => "Discover",
        "JCB" => "JCB"
    ];

    protected $_availableTypes = [];
 
    /**
    * {@inheritdoc}
    */
    public function getConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configInstance = $objectManager->get('\Teknepay\Teknepay\Model\Config');
        
        $configInstance->setMethodCode($this->_methodCode);
        $enabledTypes = $configInstance->getEnabledCcTypes();
    
        if(count($enabledTypes)) {
            foreach($enabledTypes as $valType) {
                $this->_availableTypes[$valType] = $this->_cardTypes[$valType];
            }     
        }

        return [
            'payment' => [
                'teknepay_creditcard' => [
                    'availableTypes' => [$this->_methodCode => $this->_availableTypes],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()]
                ]
            ]
        ];
    }
}