<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Product_Stop_Multiple
    extends Ess_M2ePro_Model_Connector_Play_Product_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','update','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return 'stop_and_remove';
        }
        return 'stop';
    }

    protected function getResponserModel()
    {
        return 'Play_Product_Stop_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
        }
        return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function filterManualListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isStoppable()) {

                $this->removeAndUnlockListingProduct($listingProduct);

                if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {

                    // M2ePro_TRANSLATIONS
                    // Item is not listed or not available
                    $this->addListingsProductsLogsMessage(
                        $listingProduct,
                        'Item is not listed or not available',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );
                } else {
                    $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                    $listingProduct->deleteInstance();
                }

                continue;
            }

            /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
            $playListingProduct = $listingProduct->getChildObject();

            $dispatchTo = $playListingProduct->getDispatchTo();
            empty($dispatchTo) && $dispatchTo = $playListingProduct->getAddingDispatchTo();

            if ($dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_BOTH ||
                $dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_UK) {

                if ($playListingProduct->getPriceGbr(true) <= 0) {
                // M2ePro_TRANSLATIONS
                // The price GBP must be greater than 0. Please, check the Selling Format Template and Product settings.
                    $this->addListingsProductsLogsMessage(
                        $listingProduct,
                        'The price GBP must be greater than 0. Please, '.
                        'check the Selling Format Template and Product settings.',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );

                    $this->removeAndUnlockListingProduct($listingProduct);
                    continue;
                }
            }

            if ($dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_BOTH ||
                $dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_EUROPA) {

                if ($playListingProduct->getPriceEuro(true) <= 0) {
                // M2ePro_TRANSLATIONS
                // The price EUR must be greater than 0. Please, check the Selling Format Template and Product settings.
                    $this->addListingsProductsLogsMessage(
                        $listingProduct,
                        'The price EUR must be greater than 0. Please, '.
                        'check the Selling Format Template and Product settings.',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );

                    $this->removeAndUnlockListingProduct($listingProduct);
                    continue;
                }
            }
        }
    }

    // ########################################

    protected function getRequestData()
    {
        $requestData = array();

        $requestData['items'] = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $nativeData = Mage::getModel('M2ePro/Connector_Play_Product_Helper')
                                         ->getStopRequestData($listingProduct,$this->params);

            $sendedData = $nativeData;
            $sendedData['id'] = $listingProduct->getId();

            $this->listingProductRequestsData[$listingProduct->getId()] = array(
                'native_data' => $nativeData,
                'sended_data' => $sendedData
            );

            $requestData['items'][] = $sendedData;
        }

        return $requestData;
    }

    // ########################################
}