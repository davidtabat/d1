<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Other_Mapping
{
    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $_account = null;

    protected $_mappingSettings = null;

    //########################################

    public function initialize(Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->_account         = $account;
        $this->_mappingSettings = NULL;
    }

    //########################################

    /**
     * @param array $otherListings
     * @return bool
     */
    public function autoMapOtherListingsProducts(array $otherListings)
    {
        $otherListingsFiltered = array();

        foreach ($otherListings as $otherListing) {
            if (!($otherListing instanceof Ess_M2ePro_Model_Listing_Other)) {
                continue;
            }

            /** @var $otherListing Ess_M2ePro_Model_Listing_Other */

            if ($otherListing->getProductId()) {
                continue;
            }

            $otherListingsFiltered[] = $otherListing;
        }

        if (empty($otherListingsFiltered)) {
            return false;
        }

        $sortedItems = array();

        /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
        foreach ($otherListingsFiltered as $otherListing) {
            $sortedItems[$otherListing->getAccountId()][] = $otherListing;
        }

        $result = true;

        foreach ($sortedItems as $otherListings) {
            foreach ($otherListings as $otherListing) {
                /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
                $temp = $this->autoMapOtherListingProduct($otherListing);
                $temp === false && $result = false;
            }
        }

        return $result;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function autoMapOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if ($otherListing->getProductId()) {
            return false;
        }

        $this->setAccountByOtherListingProduct($otherListing);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
            return false;
        }

        $mappingSettings = $this->getMappingRulesByPriority();

        foreach ($mappingSettings as $type) {
            $magentoProductId = NULL;

            if ($type == 'wpid') {
                $magentoProductId = $this->getWpidMappedMagentoProductId($otherListing);
            }

            if ($type == 'gtin') {
                $magentoProductId = $this->getGtinMappedMagentoProductId($otherListing);
            }

            if ($type == 'upc') {
                $magentoProductId = $this->getUpcMappedMagentoProductId($otherListing);
            }

            if ($type == 'sku') {
                $magentoProductId = $this->getSkuMappedMagentoProductId($otherListing);
            }

            if ($type == 'title') {
                $magentoProductId = $this->getTitleMappedMagentoProductId($otherListing);
            }

            if ($magentoProductId === null) {
                continue;
            }

            $otherListing->mapProduct($magentoProductId, Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

            return true;
        }

        return false;
    }

    //########################################

    protected function getMappingRulesByPriority()
    {
        if ($this->_mappingSettings !== null) {
            return $this->_mappingSettings;
        }

        $this->_mappingSettings = array();

        foreach ($this->getAccount()->getChildObject()->getOtherListingsMappingSettings() as $key=>$value) {
            if ((int)$value['mode'] == 0) {
                continue;
            }

            for ($i=0;$i<10;$i++) {
                if (!isset($this->_mappingSettings[(int)$value['priority'] + $i])) {
                    $this->_mappingSettings[(int)$value['priority'] + $i] = (string)$key;
                    break;
                }
            }
        }

        ksort($this->_mappingSettings);

        return $this->_mappingSettings;
    }

    // ---------------------------------------

    protected function getGtinMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getGtin();

        if (empty($temp)) {
            return NULL;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingGtinModeCustomAttribute()) {
            $storeId = $otherListing->getChildObject()->getRelatedStoreId();
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingGtinAttribute();
            $attributeValue = trim($otherListing->getChildObject()->getGtin());

            $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
            $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

            if ($productObj && $productObj->getId()) {
                return $productObj->getId();
            }
        }

        return NULL;
    }

    protected function getWpidMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getWpid();

        if (empty($temp)) {
            return NULL;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingWpidModeCustomAttribute()) {
            $storeId = $otherListing->getChildObject()->getRelatedStoreId();
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingWpidAttribute();
            $attributeValue = trim($otherListing->getChildObject()->getWpid());

            $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
            $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

            if ($productObj && $productObj->getId()) {
                return $productObj->getId();
            }
        }

        return NULL;
    }

    protected function getUpcMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getUpc();

        if (empty($temp)) {
            return NULL;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingUpcModeCustomAttribute()) {
            $storeId = $otherListing->getChildObject()->getRelatedStoreId();
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingUpcAttribute();
            $attributeValue = trim($otherListing->getChildObject()->getUpc());

            $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
            $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

            if ($productObj && $productObj->getId()) {
                return $productObj->getId();
            }
        }

        return NULL;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return null|int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getSkuMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getSku();

        if (empty($temp)) {
            return NULL;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeProductId()) {
            $productId = trim($otherListing->getChildObject()->getSku());

            if (!ctype_digit($productId) || (int)$productId <= 0) {
                return NULL;
            }

            $product = Mage::getModel('catalog/product')->load($productId);

            if ($product->getId()) {
                return $product->getId();
            }

            return NULL;
        }

        $attributeCode = NULL;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeDefault()) {
            $attributeCode = 'sku';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingSkuAttribute();
        }

        if ($attributeCode === null) {
            return NULL;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getSku());

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId()) {
            return $productObj->getId();
        }

        return NULL;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return null|int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getTitleMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getTitle();

        if (empty($temp)) {
            return NULL;
        }

        $attributeCode = NULL;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeDefault()) {
            $attributeCode = 'name';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingTitleAttribute();
        }

        if ($attributeCode === null) {
            return NULL;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getTitle());

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId()) {
            return $productObj->getId();
        }

        $findCount = preg_match('/^.+(\[(.+)\])$/', $attributeValue, $tempMatches);
        if ($findCount > 0 && isset($tempMatches[1])) {
            $attributeValue = trim(str_replace($tempMatches[1], '', $attributeValue));
            $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
            $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);
            if ($productObj && $productObj->getId()) {
                return $productObj->getId();
            }
        }

        return NULL;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->_account;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     */
    protected function setAccountByOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if ($this->_account !== null && $this->_account->getId() == $otherListing->getAccountId()) {
            return;
        }

        $this->_account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Account', $otherListing->getAccountId()
        );

        $this->_mappingSettings = NULL;
    }

    //########################################
}
