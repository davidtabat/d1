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

class MDN_MarketPlace_Helper_ToolBox extends Mage_Core_Helper_Abstract {
    
    const ACTIVE = 'enable';
    const DESACTIVE = 'disable';

    /* @var array */
    private $_actions = array(self::ACTIVE, self::DESACTIVE);
    /* @var array */
    private static $_availableMp = null;

    /**
     * Get available Marketplaces as array
     *
     * @return array
     */
    protected function getAvailableMp() {

        if (self::$_availableMp === null) {

            self::$_availableMp = Mage::helper('MarketPlace')->getMarketPlaceOptions();
        }

        return self::$_availableMp;
    }

    /**
     * Active product on marketplace
     * if $mp is null then active product on all available marketplaces
     *
     * @param integer $id
     * @param string $mp
     */
    public function activeProductOnMarketplace($id, $mp = null) {

        $this->updateProduct($id, $mp, self::ACTIVE);
    }

    /**
     * Desactive product on marketplace
     * if $mp is null then desactive product on all available marketplaces
     *
     * @param integer $id
     * @param string $mp
     */
    public function desactiveProductOnMarketplace($id, $mp = null) {

        $this->updateProduct($id, $mp, self::DESACTIVE);
    }

    /**
     * Update product : active or desactive
     *
     * @param int $id
     * @param string $mp
     * @param string $action
     * @return int
     */
    protected function updateProduct($id, $mp, $action) {

        // check if action is allowed
        if (in_array($action, $this->_actions)) {

            // load product
            $p = Mage::getModel('catalog/product')->load($id);

            // if product exists
            if ($p->getentity_id()) {

                // get $mp or all available marketplaces
                $mp = ($mp === null) ? $this->getAvailableMp() : $mp;

                // for all marketplaces
                if (is_array($mp)) {

                    foreach ($mp as $current_mp) {
                        // update product on marketplace $current_mp
                        $this->update($id, $current_mp, $action);
                    }
                } else {
                    // update product on marketplace $mp
                    $this->update($id, $mp, $action);
                }
            } else {
                // unexisting product
                throw new Exception('Bad product (id : ' . $id . ')');
            }
        }

        return 1;
    }

    /**
     * Update
     *
     * @param int $id
     * @param string $mp
     * @param string $action
     * @return int
     */
    protected function update($id, $mp, $action) {

        if (in_array($mp, $this->getAvailableMp())) {

            // check if product exist in market_place_data
            $p = Mage::getModel('MarketPlace/Data')
                            ->getCollection()
                            ->addFieldToFilter('mp_marketplace_id', $mp)
                            ->addFieldToFilter('mp_product_id', $id);

            if ($p->count() > 0) {

                $p = $p->getFirstItem();

                // product already on marketplace
                // update status
                // force export
                switch ($action) {
                    case self::ACTIVE :
                        $p->setmp_exclude(0)
                                ->setmp_force_export(1)
                                ->save();
                        break;
                    case self::DESACTIVE :
                        $p->setmp_exclude(1)
                                ->save();
                        break;
                }
            } else {

                switch ($action) {
                    case self::ACTIVE :
                        // add product on mp
                        $this->addProduct($id, $mp);
                        // force export
                        Mage::getModel('MarketPlace/Data')->forceExport($id, $mp);
                        break;
                    case self::DESACTIVE :
                        // nothing to do !
                        break;
                }
            }

            return 0;
        } else {

            throw new Exception('Unknow marketplace : ' . $mp . ')');
        }
    }

    /**
     * Add product on marketplace
     *
     * @param int $id
     * @param string $mp
     * @return int
     */
    protected function addProduct($id, $mp) {

        if ($mp == "fnac")
            return 0;

        // build request programmaticaly
        $request = new Zend_Controller_Request_Http();
        $ids = array($id);
        $request->setParam('product_ids', $ids);

        $helperName = ucfirst($mp);
        // check if marketplace allow product creation
        if (Mage::helper($helperName)->allowProductCreation() === true) {

            $helper = Mage::helper($helperName . '/ProductCreation');

            if ($helper->buildMassProductFile($request) == 0)
                if ($mp != "rueducommerce")
                    $helper->sendProductFile();
                else
                    return 1;
        }

        return 0;
    }

}