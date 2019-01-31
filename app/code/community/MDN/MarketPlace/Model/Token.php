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
class MDN_MarketPlace_Model_Token extends Mage_Core_Model_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Token');
    }

    /**
     * After save
     * 
     * @return int 
     */
    public function _afterSave(){

        $collection = $this->getCollection()
                        ->addFieldToFilter('mp_marketplace_id', $this->getmp_id())
                        ->setOrder('mp_id', 'desc');

        $i = 0;
        foreach($collection as $item){

            if($i > 50){
                $item->delete();
            }

            $i++;

        }

        return 0;

    }

}