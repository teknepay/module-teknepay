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
 
class EftConfigProvider implements ConfigProviderInterface
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
    protected $_methodCode = 'teknepay_eft';
 
    /**
    * {@inheritdoc}
    */
    public function getConfig()
    {
        return [
            'payment' => [
                'teknepay_eft' => [
                    'accountTypes' => [$this->_methodCode => ["S" => "Savings", "C" => "Checking"]]
                ]
            ]
        ];
    }
}