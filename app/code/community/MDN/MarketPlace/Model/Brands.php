<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 */

class MDN_MarketPlace_Model_Brands extends Mage_Core_Model_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Brands');
    }
	
	/**
     * Get marketplace brand code
	 * 
	 * @param int $manufacturer_id
	 * @param string $mp
	 *
	 * @return $retour 
     */
	public function getMpbCode($manufacturer, $mp){
	
		$retour = NULL;
	
		$item = $this->getCollection()
					->addFieldToFilter('mpb_marketplace_id', $mp)
					->addFieldToFilter('mpb_code', $manufacturer)
					->getFirstItem();
		
		if($item->getmpb_id())
			$retour = $item->getmpb_code();
			
		return $retour;
		
	}

    public function getBrandForManufacturer($mp, $manufacturerId)
    {
        $item = $this->getCollection()
            ->addFieldToFilter('mpb_marketplace_id', $mp)
            ->addFieldToFilter('mpb_manufacturer_id', $manufacturerId)
            ->getFirstItem();

        if($item->getmpb_id())
            return $item;
        else
            return false;
    }

}
