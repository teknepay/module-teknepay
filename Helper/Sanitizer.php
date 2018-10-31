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

namespace Teknepay\Teknepay\Helper;

class Sanitizer
{
	protected static $arrPossibleVariables = [
        'cc_cid',
        'CVV',
        'password'
    ];
	
	public static function sanitizeRequest($element) {
		
		$tmp = null;
		
		switch(gettype($element)) {
			
			case 'array':
			
				$tmp = $element;
				
				foreach($tmp as $key => $value) {
					if(is_array($value) || is_object($value) || self::isJSON($value)) {
						$tmp[$key] = self::sanitizeRequest($value);
					} else {
						$tmp[$key] = self::replaceString($key, $value);	
					}
				}
			
			break;
			
			case 'object':
			
				$tmp = clone $element;
				
				foreach($tmp as $key => $value) {
					if(is_array($value) || is_object($value) || self::isJson($value)) {
						$tmp->{$key} = self::sanitizeRequest($value);
					} else {
						$tmp->{$key} = self::replaceString($key, $value);	
					}
				}
			
			break;
			
			case 'string':
			
				$tmp = $element;
			
				if(self::isJSON($tmp)) {
					$tmp = self::sanitizeRequest(json_decode($tmp, true));
				}
			
			break;
		}
		
		return $tmp;
	}
	
	private static function replaceString($key, $value) {
		
		if (preg_match('/\d{10,}/', $value)) {
			return self::sanitizeNumber($value);
		}
		
		if (preg_match('/accountNumber|account_number|accountNo/i', $key)) {
			return self::sanitizeNumber($value);
		}
		
		if (preg_match('/' . implode('|', self::$arrPossibleVariables) . '/i', $key)) {
			return self::sanitizeString();
		}
		
		return $value;
	}

    public static function sanitizeNumber($element)
    {
        return 'XXXX - ' . substr($element, -4, 4);
    }

    public static function sanitizeString()
    {
        return '****';
    }
	
	public static function isJSON($string){
	   return is_string($string) && is_array(json_decode($string, true)) ? true : false;
	}


}