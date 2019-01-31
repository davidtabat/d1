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
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */

class MDN_MarketPlace_Model_Internationalization extends Mage_Core_Model_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Internationalization');
    }

    /**
     * get association value
     * 
     * @param int $store_id
     * @param string $mp_name
     * @return type string 
     */
    public function getAssociationValue($store_id, $mp_name){

        $obj = $this->getCollection()
                ->addFieldToFilter('mpi_store_id', $store_id)
                ->addFieldToFilter('mpi_marketplace_id', $mp_name)
                ->getFirstItem();

        return (!$obj->getmpi_id()) ? '' : $obj->getmpi_language();

    }

    /**
     * Updat association
     * 
     * @param string $marketplace
     * @param int $store_id
     * @param string $language
     * @return boolean 
     */
    public function updateAssociation($marketplace, $store_id, $language){

        $obj = $this->getCollection()
                ->addFieldToFilter('mpi_marketplace_id', $marketplace)
                ->addFieldToFilter('mpi_store_id', $store_id)
                ->getFirstItem();

        if($obj->getmpi_id())
                $obj->delete();

        if(count($language) > 0){

            $this->setmpi_marketplace_id($marketplace)
                    ->setmpi_store_id($store_id)
                    ->setmpi_language(implode(",",$language))
                    ->save();

        }

        return true;

    }

    /**
     * Get store id
     * 
     * @param string $marketplace
     * @param string $country
     * @return mixed null|int 
     */
    public function getStoreId($marketplace, $country){

        $obj = null;

        $collection = $this->getCollection()
                        ->addFieldToFilter('mpi_marketplace_id', $marketplace);

        foreach($collection as $item){

            if(in_array($country, explode(",",$item->getmpi_language()))){

                $obj = $item;
                break;

            }

        }

        return ($obj !== null) ? $obj->getmpi_store_id() : null;

    }

}
