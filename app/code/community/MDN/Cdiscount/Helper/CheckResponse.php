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
 */
class MDN_Cdiscount_Helper_CheckResponse extends MDN_MarketPlace_Helper_CheckResponse {

    const kSuccess = 'Integrated';

    protected $_updateStatus = true;
    protected $_limit = 200;

    public function checkExport($feedId = null, $submit = false) {

        $error = false;

        $collection = $this->getCollection($feedId);

        foreach ($collection as $feed) {

            $error = false;

            // request response
            $response = Mage::Helper('Cdiscount/Services')->getOfferPackageSubmissionResult($feed->getmp_feed_id());

            $xml = new DomDocument('1.0', 'utf-8');
            $xml->loadXML($response['content']);

            if ($xml->getElementsByTagName('OperationSuccess')->item(0) && $xml->getElementsByTagName('OperationSuccess')->item(0)->nodeValue == "true") {

                // cas ou le statut n'est pas renseigné...
                if (!$xml->getElementsByTagName('PackageIntegrationStatus')->item(0)->nodeValue) {

                    $feed->setmp_status(MDN_MarketPlace_Helper_ProductCreation::kFeedError)
                            ->save();

                    continue;
                }

                if ($xml->getElementsByTagName('PackageIntegrationStatus')->item(0)->nodeValue != self::kSuccess) {

                    $feed->setmp_status(MDN_MarketPlace_Helper_ProductCreation::kFeedInProgress)
                            ->save();
                } else {

                    foreach ($xml->getElementsByTagName('OfferReportLog') as $logNode) {

                        $this->_cpt++;

                        if ($logNode->getElementsByTagName('OfferIntegrationStatus')->item(0)->nodeValue != self::kSuccess) {

                            $error = true;

                            if ($logNode->getElementsByTagName('LogMessage')->item(0)) {
                                $tmp = explode('|', $logNode->getElementsByTagName('LogMessage')->item(0)->nodeValue);

                                $this->_cptError++;

                                $productId = Mage::getModel('catalog/product')->getIdBySku($tmp[0]);

                                $this->_idsInError[] = $productId;

                                $this->_errors[$productId] = array(
                                    'sku' => $tmp[0],
                                    'ean' => $tmp[1],
                                    'code' => $tmp[4],
                                    'message' => $tmp[5],
                                    'log' => $tmp[5]
                                );
                            }
                        }
                    }

                    if ($error === true) {
                        $feed->setmp_status(MDN_MarketPlace_Helper_ProductCreation::kFeedError)
                                ->save();
                    } else {

                        $feed->setmp_status(MDN_MarketPlace_Helper_ProductCreation::kFeedDone)
                                ->save();
                    }
                }
            }
        }

        return $this->notifyErrors();
    }

    public function getMp() {
        return Mage::Helper('Cdiscount')->getMarketPlaceName();
    }

}
