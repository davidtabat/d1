<?php
/**
* Ext4mage Shipment2edit Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to Henrik Kier <info@ext4mage.com> so we can send you a copy immediately.
*
* @category   Ext4mage
* @package    Ext4mage_Shipment2edit
* @copyright  Copyright (c) 2012 Ext4mage (http://ext4mage.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @author     Henrik Kier <info@ext4mage.com>
* */

class Ext4mage_Shipment2edit_EditController extends Mage_Adminhtml_Controller_Action{

	const XPATH_CONFIG_SETTINGS_ALLOW_OVER		= 'shipment2edit/settings/allow_over';
	const XPATH_CONFIG_SETTINGS_LICENSE			= 'shipment2edit/settings/license_code';
	
	protected function _allowOver() {
		return Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_ALLOW_OVER);
	}
	
    /**
     * Default index action
     * Used to save the new shipment values
     *
     */
    public function indexAction()
    {
    	$postData = $this->getRequest()->getPost();
    	
    	$order = Mage::getModel('sales/order')->load($postData['order_id']);
    	$oldShipping = $order->getData('shipping_amount');
    	$oldShippingTax = $order->getData('shipping_tax_amount');
    	$oldTotal = $order->getData('grand_total');
    	$oldTotalTax = $order->getData('tax_amount');
		
    	$newTotal = $oldTotal - (($oldShipping+$oldShippingTax)-($postData['shipment2edit_amount']+$postData['shipment2edit_tax']));
    	$newTotalTax = $oldTotalTax - ($oldShippingTax - $postData['shipment2edit_tax']);
    	
    	if(!$this->_allowOver() && ($newTotal-$oldTotal)>0){
    		//Because new amount is bigger then existing and this is not allow - return with error
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shipment2edit')->__('New shipping amount bigger than existing'));
    	}else{
    	
	    	$order->setData('shipping_method', $postData['shipment2edit_method']);
	    	$order->setData('shipping_description', $postData['shipment2edit_desc']);
	    	$order->setData('base_grand_total', $newTotal);
	    	$order->setData('grand_total', $newTotal);
	    	$order->setData('base_tax_amount', $newTotalTax);
	    	$order->setData('tax_amount', $newTotalTax);
	    	$order->setData('base_shipping_amount', $postData['shipment2edit_amount']);
	    	$order->setData('shipping_amount', $postData['shipment2edit_amount']);
	    	$order->setData('shipping_incl_tax', $postData['shipment2edit_amount']+$postData['shipment2edit_tax']);
	    	$order->setData('base_shipping_incl_tax', $postData['shipment2edit_amount']+$postData['shipment2edit_tax']);
	    	$order->setData('base_shipping_tax_amount', $postData['shipment2edit_tax']);
	    	$order->setData('shipping_tax_amount', $postData['shipment2edit_tax']);
	    	$order->setData('shipping_hidden_tax_amount', '0.0000');
	    	$order->setData('base_shipping_hidden_tax_amnt', '0.0000');
	    	$order->setData('base_shipping_discount_amount', '0.0000');
	    	$order->setData('shipping_discount_amount', '0.0000');
	    	$order->save();
	    	
	    	$payment = $order->getPayment();
	    	$payment->setData('base_amount_ordered',$newTotal);
	    	$payment->setData('amount_ordered',$newTotal);
	    	$payment->setData('base_shipping_amount',$postData['shipment2edit_amount']);
	    	$payment->setData('shipping_amount',$postData['shipment2edit_amount']);
	    	$payment->save();
	    	
	    	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('shipment2edit')->__('New shipping amount has been applied'));
    	}
    	
    	try{
    		Mage::helper('ext4mageshared')->checkLicenseOnline("shipment2edit",Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_LICENSE));
    	}catch (Exception $e){
    		//Do nothing - just so it do not break anything
    	}
    	$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));
    }
}
