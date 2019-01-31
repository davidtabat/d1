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
 * 
**/ 

class Ext4mage_Shipment2edit_Block_Shipment_Edit extends Mage_Adminhtml_Block_Sales_Order_View
{
   const XPATH_CONFIG_SETTINGS_IS_ACTIVE		= 'shipment2edit/settings/is_active';
   const XPATH_CONFIG_HTML2PDF_SETTINGS_IS_ACTIVE		= 'html2pdf/settings/is_active';
   
   protected function _isHtml2pdfActive() {
   	return Mage::getStoreConfig(self::XPATH_CONFIG_HTML2PDF_SETTINGS_IS_ACTIVE);
   }

   protected function _isActive() {
   	return Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_IS_ACTIVE);
   }
   
   public function getPrintUrl() {
   	return $this->getUrl('html2pdf/admin_order/print', array(
   			'order_id' => $this->getOrder()->getId()
   	));
   }	
   
	public function __construct() {
        parent::__construct();
        
        if($this->_isHtml2pdfActive())
        	$this->_addButton('print', array(
        			'label'     => Mage::helper('sales')->__('Print'),
        			'class'     => 'save',
        			'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
        	)
        );
         
        if($this->_isActive() && $this->getOrder()->canEdit()){
        	$methodOption = $this->getAllShippingMethods();
        	$methodOptionText = '';
        	foreach( $methodOption as $value ) {
        		$selected = '';
        		if(strcasecmp($value['value'], $this->getOrder()->getShippingMethod())==0)
        			$selected = ' selected';
        		$methodOptionText = $methodOptionText.'<option value=\"'.$value['value'].'\"'.$selected.'>'.$value['label'].'</option>';
        	}
        	
        	     
        	$shippingInput = '<form action=\"'.Mage::helper('adminhtml')->getUrl('shipment2edit/edit').'\" id=\"shipment2edit_order_form\" method=\"post\">
		          <fieldset>
		              <input type=\"hidden\" value=\"'.$this->getOrder()->getId().'\" name=\"order_id\" id=\"order_id\">
		              <input type=\"hidden\" name=\"form_key\" value=\"'.Mage::getSingleton('core/session')->getFormKey().'\" />
		              <table width=\"100%\" cellspacing=\"5\">
		                <tbody>
		                  <tr>
		                      <td width=\"200px\"><label>'.Mage::helper('sales')->__('Shipping Information').'</label></td>
		                      <td><input type=\"text\" style=\"width:250px\" value=\"'.$this->getOrder()->getShippingDescription().'\" name=\"shipment2edit_desc\" id=\"shipment2edit_desc\" class=\"input-text\"></td>
		                  </tr>
		                  <tr>
		                      <td width=\"200px\"><label>'.Mage::helper('sales')->__('Shipping Amount').' ('.Mage::helper('sales')->__('Excl. Tax').')</label></td>
		                      <td><input type=\"text\" style=\"width:250px\" value=\"'.$this->getOrder()->getShippingAmount().'\" name=\"shipment2edit_amount\" id=\"shipment2edit_amount\" class=\"input-text\"></td>
		                  </tr>
		                  <tr>
		                      <td width=\"200px\"><label>'.Mage::helper('sales')->__('Shipping Tax').'</label></td>
		                      <td><input type=\"text\" style=\"width:250px\" value=\"'.$this->getOrder()->getShippingTaxAmount().'\" name=\"shipment2edit_tax\" id=\"shipment2edit_tax\" class=\"input-text\"></td>
		                  </tr>
		                  <tr>
		                      <td width=\"200px\"><label>'.Mage::helper('sales')->__('Shipping Method').'</label></td>
		                      <td>
		                      		<select name=\"shipment2edit_method\" id=\"shipment2edit_method\" style=\"width:256px\" class=\"input-text\" onchange=\"document.getElementById(\'shipment2edit_desc\').value=this.options[this.selectedIndex].text\">'.$methodOptionText.'</select>
		                      </td>
		                  </tr>
		                  <tr>
		                      <td width=\"200px\">&nbsp;</td>
		                      <td style=\"padding-top:5px;\"><button style=\"\" class=\"scalable save\" type=\"submit\" title=\"'.Mage::helper('sales')->__('Save').'\"><span>'.Mage::helper('sales')->__('Save').'</span></button></td>
		                  </tr>
		                </tbody>
		              </table>
		          </fieldset>
		        </form>';
        	
        	$shippingInput = str_replace(array("\r\n", "\r"), "\n", $shippingInput);
        	$lines = explode("\n", $shippingInput);
        	$new_lines = array();
        	
        	foreach ($lines as $i => $line) {
        		if(!empty($line))
        			$new_lines[] = trim($line);
        	}
        	$shippingInput = implode($new_lines);
        	
        	$this->_formScripts[] = '
        			
			    function addShipment2EditCode(){
			    	$$(\'h4.head-shipping-method\')[0].up().next().insert({bottom: "<br /><br />'.$shippingInput.'"});
			    }
			    Event.observe(window,"load",addShipment2EditCode);
        	';
        	 
        }
        
	}
	
	public function getAllShippingMethods()
	{
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
	
		$_methodOptions = array();
		foreach($methods as $_ccode => $_carrier)
		{
			if($_methods = $_carrier->getAllowedMethods())
			{
				if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
					$_title = $_ccode;
				
				foreach($_methods as $_mcode => $_method)
				{
					$_code = $_ccode . '_' . $_mcode;
					$_methodOptions[] = array('value' => $_code, 'label' => $_title.' - '.$_method);
				}
			}
		}
		return $_methodOptions;
	}	
}
