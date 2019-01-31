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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Widget_Grid_Column_Filter_MpReference extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {

    /**
     * Get condition
     * 
     * @return mixed boolean|array 
     */
    public function getCondition() {
        $searchString = $this->getValue();
        if (!$searchString)
            return false;
        $collection = mage::getModel('MarketPlace/Data')
                ->getCollection()
                ->addFieldToFilter('mp_reference', array('like' => '%' . $searchString . '%'));
        $productIds = array();
        foreach ($collection as $item) {
            $productIds[] = $item->getmp_product_id();
        }


        return array('in' => $productIds);
    }

}
