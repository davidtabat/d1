<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 * @todo : check where it is used. Maybe deprecated
 */
class MDN_MarketPlace_Helper_FlatOrder extends Mage_Core_Helper_Abstract
{

        /* @var boolean */
	private $_isFlatOrder = null;
	
	/**
	 * Return true if magento version uses flat model for orders
	 * Return false if magento uses EAV model for orders
	 * Used to keep only one branch for both eav model and flat model
	 *
         * @return boolean
	 */
	public function isFlatOrder()
	{
		if ($this->_isFlatOrder == null)
		{
			$tableName = mage::getResourceModel('sales/order')->getTable('sales/order');
			if ($tableName == $this->getTablePrefix().'sales_order')
				$this->_isFlatOrder = false;
			else 
				$this->_isFlatOrder = true;
		}
		return $this->_isFlatOrder;
	}
	
	/**
	 * Same function, reverse name
	 *
	 * @return unknown
	 */
	public function ordersUseEavModel()
	{
		return !$this->isFlatOrder();
	}
	
	/**
	 * Return table prefix
	 *
	 * @return unknown
	 */
	public function getTablePrefix()
	{
		return (string)Mage::getConfig()->getTablePrefix();
	}
}