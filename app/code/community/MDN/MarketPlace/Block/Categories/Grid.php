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
 */
class MDN_MarketPlace_Block_Categories_Grid extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Return categories
         * 
         * @return array
	 */
	public function getCategories()
	{
		return mage::helper('MarketPlace/Categories')->getCategories();
	}
	
        /**
         * Get marketplaces
         * 
         * @return array $retour 
         */
	public function getMarketPlaces()
	{
		$retour = array();
		
		$marketPlaces = mage::helper('MarketPlace')->getHelpers();
		foreach($marketPlaces as $marketPlace)
		{
			$helper = mage::helper($marketPlace);
			if ($helper->needCategoryAssociation())
				$retour[] = $helper;
		}
		
		return $retour;
	}
	
        /**
         * get association description
         * 
         * @param Mage_Catalog_Model_Category $category
         * @param MDN_MarketPlace_Helper_Abstract $marketPlace
         * @return string 
         */
	public function getAssociationDescription($category, $marketPlace)
	{
		$value = mage::getModel('MarketPlace/Category')->getAssociationValue($category->getId(), $marketPlace->getMarketPlaceName());
		if ($value != '')
		{
			return '<font color="green">'.$this->__('Associated').'</font>';
		}
		else
		{
			return '<font color="red">'.$this->__('Not associated').'</font>';		
		}
	}
	

}