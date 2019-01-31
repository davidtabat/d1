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
 * @copyright  Copyright (c) 2012 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_Requiredfields extends Mage_Core_Model_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Requiredfields');
    }

    /**
     *  Load value for path
     * 
     * @param string $path
     * @param string $marketplace
     * @return string $retour
     */
    public function loadValueForPath($path, $marketplace){

        $retour = "";

        $requiredfield = $this->getCollection()
                            ->addFieldToFilter('mp_marketplace_id', $marketplace)
                            ->addFieldToFilter('mp_path', $path)
                            ->getFirstItem();

        if($requiredfield->getmp_id()){
            $retour = $requiredfield->getmp_attribute_name();
        }

        return $retour;

    }

    /**
     * Load default for path
     * 
     * @param string $path
     * @param string $mp
     * @return string $retour  
     */
    public function loadDefaultForPath($path, $mp){

        $retour = "";

        $requiredField = $this->getCollection()
                                ->addFieldToFilter('mp_marketplace_id', $mp)
                                ->addFieldToFilter('mp_path', $path)
                                ->getFirstItem();

        if($requiredField->getmp_id()){
            $retour = $requiredField->getmp_default();
        }

        return $retour;

    }

}