<?php
/**
 * This file is part of a FireGento e.V. module.
 *
 * This FireGento e.V. module is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category  FireGento
 * @package   FireGento_MageSetup
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2013 FireGento Team (http://www.firegento.com)
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   $Id:$
 * @since     0.1.0
 */
/**
 * Rewrite to fetch required agreement ids.
 *
 * @category FireGento
 * @package  FireGento_MageSetup
 * @author   FireGento Team <team@firegento.com>
 */
class FireGento_MageSetup_Helper_Checkout_Data
    extends Mage_Checkout_Helper_Data
{
    /**
     * Avoid loss of translation
     */
    public function __construct()
    {
        $this->_moduleName = 'Mage_Checkout';
    }

    /**
     * Get all required agreement ids
     *
     * @return array Agreement Ids
     **/
    public function getRequiredAgreementIds()
    {
        if (is_null($this->_agreements)) {
            if (!Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
                $this->_agreements = array();
            } else {
                $agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('is_required', 1); // Only get Required Elements

                if ($this->_getCustomerSession()->isLoggedIn()) {
                    $agreements->addFieldToFilter('agreement_type', array('in' => array(
                        FireGento_MageSetup_Model_Source_AgreementType::AGREEMENT_TYPE_CHECKOUT,
                        FireGento_MageSetup_Model_Source_AgreementType::AGREEMENT_TYPE_BOTH,
                    )));
                } else {
                    $agreements->addFieldToFilter('agreement_type', array('in' => array(
                        FireGento_MageSetup_Model_Source_AgreementType::AGREEMENT_TYPE_CUSTOMER,
                        FireGento_MageSetup_Model_Source_AgreementType::AGREEMENT_TYPE_CHECKOUT,
                        FireGento_MageSetup_Model_Source_AgreementType::AGREEMENT_TYPE_BOTH,
                    )));
                }

                $this->_addRevocationProductTypesFilter($agreements);

                $this->_agreements = $agreements->getAllIds();
            }
        }

        return $this->_agreements;
    }

    /**
     * Retrieve the customer session
     *
     * @return Mage_Customer_Model_Session Customer Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Display only those checkout agreements which match the product in cart
     *
     * @param Mage_Checkout_Model_Resource_Agreement_Collection $agreements
     */
    protected function _addRevocationProductTypesFilter($agreements)
    {
        /** @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('entity_id', array('in' => $this->_getProductIdsInQuote()))
            ->joinAttribute('revocation_product_type', 'catalog_product/revocation_product_type', 'entity_id', null, 'left')
            ->addAttributeToSelect('revocation_product_type');

        $revocationProductTypes = array(FireGento_MageSetup_Model_Source_RevocationProductType::REVOCATION_PRODUCT_TYPE_ALL => FireGento_MageSetup_Model_Source_RevocationProductType::REVOCATION_PRODUCT_TYPE_ALL);
        $defaultRevocationProductType = Mage::getStoreConfig('checkout/options/default_revocation_product_type');

        foreach ($productCollection->getColumnValues('revocation_product_type') as $revocationProductType) {
            if ($revocationProductType) {
                $revocationProductTypes[$revocationProductType] = $revocationProductType;
            } else {
                $revocationProductTypes[$defaultRevocationProductType] = $defaultRevocationProductType;
            }
        }

        $agreements->addFieldToFilter('revocation_product_type', array('in' => $revocationProductTypes));
    }

    /**
     * Retrieve the customer quote
     *
     * @return Mage_Sales_Model_Quote Customer Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @return array
     */
    protected function _getProductIdsInQuote()
    {
        $productIds = array();
        foreach ($this->_getQuote()->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Quote_Item $item */

            if ($item->getParentItemId()) {
                continue;
            }

            $productIds[] = $item->getProductId();
        }
        return $productIds;
    }
}
