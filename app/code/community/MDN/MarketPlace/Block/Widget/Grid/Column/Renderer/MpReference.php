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
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_MpReference extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Render
     * 
     * @param Varien_Object $row
     * @return string $html
     */
    public function render(Varien_Object $row) {

        $html = "";

        $collection = Mage::getModel('MarketPlace/Data')->getCollection()
                            ->addFieldToFilter('mp_product_id', $row->getId());
        
        $html .= '<ul>';
        foreach($collection as $item){
            if($item->getmp_reference()){
                $country = Mage::getModel('MarketPlace/Countries')->load($item->getmp_marketplace_id());
                $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
                $html .= '<li><b>'.ucfirst($account->getmpa_mp()).' '.$account->getmpa_name().' '.$country->getmpac_country_code().'</b> : '.Mage::Helper(ucfirst($account->getmpa_mp()))->getProductUrl($item->getmp_reference(), $country).'</li>';
            }
        }
        $html .= '</ul>';

        return $html;

    }


}
