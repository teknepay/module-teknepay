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

namespace Teknepay\Teknepay\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    const EXTENSION_URL = 'http://www.teknepay.com';

    /**
     * @var \Teknepay\Magento2\Helper\Data $helper
     */
    protected $_helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Teknepay\Magento2\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Teknepay\Teknepay\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        parent::__construct($context);
    }


    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $extensionVersion   = $this->_helper->getExtensionVersion();
        $extensionTitle     = 'Teknepay Gateway';
        $versionLabel       = sprintf(
            '<a href="%s" title="%s" target="_blank">%s</a>',
            self::EXTENSION_URL,
            $extensionTitle,
            $extensionVersion
        );
        $element->setValue($versionLabel);

        return $element->getValue();
    }
}