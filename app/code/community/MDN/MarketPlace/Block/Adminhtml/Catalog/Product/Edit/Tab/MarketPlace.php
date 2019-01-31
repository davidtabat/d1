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
class MDN_MarketPlace_Block_Adminhtml_Catalog_Product_Edit_Tab_MarketPlace extends Mage_Adminhtml_Block_Abstract {

    /**
     * Construct
     */
    public function __construct() {

        parent::__construct();
        $this->setTemplate('MarketPlace/Adminhtml/Catalog/Product/Edit/Tab/MarketPlace.phtml');
    }

    /**
     * Retrieve currently edited product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        return Mage::registry('current_product');
    }

    /**
     * Get infos
     * 
     * @return type 
     */
    public function getInfos() {

        $productId = $this->getProduct()->getId();
        $retour = array();
        $account = null;
        $country = null;

        $collection = Mage::getModel('MarketPlace/Data')->getCollection()
                ->addFieldToFilter('mp_product_id', $productId);

        foreach ($collection as $item) {

            $country = Mage::getModel('MarketPlace/Countries')->load($item->getmp_marketplace_id());
            $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());

            $status = ($item->getmp_marketplace_status() == null) ? 'notCreated' : $item->getmp_marketplace_status();

            $extra = ($item->getmp_last_update() < $this->getProduct()->getupdated_at()) ? $this->__('Waiting for update') : '';

            $retour[] = array(
                'id' => $item->getmp_product_id(),
                'countryId' => $country->getId(),
                'marketplace' => $account->getmpa_mp(),
                'account' => $account->getmpa_name(),
                'country' => $country->getmpac_country_code(),
                'reference' => $item->getmp_reference(),
                'status' => $status,
                'last_update' => $item->getmp_last_update(),
                'last_stock_sent' => $item->getmp_last_stock_sent(),
                'last_price_sent' => $item->getmp_last_price_sent(),
                'last_delay_sent' => $item->getmp_last_delay_sent(),
                'update_status' => $item->getmp_update_status(),
                'extra' => $extra
            );
        }

        return $retour;
    }

    /**
     * get actions
     * 
     * @param array $info
     * @return string $html 
     */
    public function getActionSwitcher($info) {

        $suffix = $info['countryId'] . '_' . $info['id'];

        $html = '';

        $html .= '<select name="mpActionSwitch" id="mpActionSwitcher" onchange="processMarketplaceAction(this.value);">';

        $html .= '<option value=""></option>';
        $html .= '<optgroup label="' . Mage::Helper('MarketPlace')->__('Feeds') . '">';
        $html .= '<option value="match_' . $suffix . '">' . Mage::Helper('MarketPlace')->__('Match') . '</option>';
        $html .= '<option value="add_' . $suffix . '">' . Mage::Helper('MarketPlace')->__('Add') . '</option>';
        $html .= '<option value="revise_' . $suffix . '">' . Mage::Helper('MarketPlace')->__('Revise product') . '</option>';
        $html .= '<option value="update_' . $suffix . '">' . Mage::helper('MarketPlace')->__('Update stock & price') . '</option>';
        $html .= '<option value="updateimage_' . $suffix . '">' . Mage::Helper('MarketPlace')->__('Update Image') . '</option>';
        $html .= '<option value="delete_' . $suffix . '">' . Mage::helper('MarketPlace')->__('Delete') . '</option>';
        $html .= '</optgroup>';
        $html .= '<optgroup label="' . Mage::helper('MarketPlace')->__('Status') . '">';
        $html .= '<option value="setascreated_' . $suffix . '">' . Mage::Helper('MarketPlace')->__('Set as created') . '</option>';
        $html .= '<option value="setaspending_' . $suffix . '">' . Mage::Helper('MarketPlace')->__('Set as pending') . '</option>';
        $html .= '<option value="setasnotcreated_' . $suffix . '">' . Mage::Helper('MarketPlace')->__('Set as not created') . '</option>';
        $html .= '</optgroup>';

        $html .= '</select>';

        return $html;
    }

}
