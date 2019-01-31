<?php

class Jay_Googlesnippetsprice_Helper_Data extends Mirasvit_Seo_Helper_Data {

	    protected function _formatPrice($price, $noSymbol = true) {
	    	$productFinalPrice = parent::_formatPrice($price, $noSymbol);
  	        $productFinalPrice = str_replace(',','',Mage::getModel('directory/currency')->format( $productFinalPrice, array('display'=>Zend_Currency::NO_SYMBOL,'currency' => 'USD', 'locale' => 'en_US'), false ));
	
		return $productFinalPrice;
	    }
    }
