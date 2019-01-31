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
class MDN_MarketPlace_Helper_Feed extends Mage_Core_Helper_Abstract {

    const kFeedSubmitted = "_SUBMITTED_";
    const kFeedDone = "_DONE_";
    const kFeedInProgress = "_IN_PROGRESS_";
    const kFeedError = "_ERROR_";
    const kFeedDebug = "_DEBUG_";

    const kFeedTypeProductCreation = "_PRODUCT_CREATION_";
    const kFeedTypeUpdateStockPrice = "_UPDATE_STOCK_PRICE_";
    const kFeedTypeImportOrders = "_IMPORT_ORDERS_";
    const kFeedTypeAcceptOrders = "_ACCEPT_ORDERS_";
    const kFeedTypeCancelOrders = "_CANCEL_ORDERS_";
    const kFeedTypeTracking = "_TRACKING_";
    const kFeedTypeMedia = "_MEDIA_";
    const kFeedTypeMatchingProducts = "_MATCHING_PRODUCTS_";
    const kFeedTypeFulfillment = "_FULFILLMENT_";
    const kFeedTypeMatchingEAN = "_MATCHING_EAN_";
    const kFeedTypeShippingCost = "_SHIPPING_COST_";
    const kFeedTypeSubmissionResultProductCreation = "_SUBMISSION_RESULT_PRODUCT_CREATION_";
    const kFeedTypeSubmissionResultProductUpdate = "_SUBMISSION_RESULT_PRODUCT_UPDATE_";
    const kFeedTypeSubmissionResultTracking = "_SUBMISSION_RESULT_TRACKING_";
    const kFeedTypeGetOrdersToShip = "_GET_ORDERS_TO_SHIP_";
    const kFeedTypeUpdateOrders = '_UPDATE_ORDER_';
    const kFeedTypeSellerInformation = '_SELLER_INFORMATION_';
    const kFeedTypeDivers = '_DIVERS_';
    const kFeedTypeRelationship = '_RELATIONSHIP_';
    const kFeedTypeDeleteProduct = '_DELETE_PRODUCT_';
    const kFeedTypeUnshippedOrders = '_UNSHIPPED_ORDERS_';
    const kFeedTypeReviseProducts = '_REVISE_PRODUCTS_';

    /**
     * Get feed status
     *
     * @return array
     */
    public function getFeedStatusOptions(){

        return array(
            self::kFeedSubmitted => self::kFeedSubmitted,
            self::kFeedDone => self::kFeedDone,
            self::kFeedInProgress => self::kFeedInProgress,
            self::kFeedError => self::kFeedError,
			self::kFeedDebug => self::kFeedDebug
        );
    }

    /**
     * Get feed type
     *
     * @return array
     */
    public function getFeedTypeOptions(){

        return array(
            self::kFeedTypeProductCreation => self::kFeedTypeProductCreation,
            self::kFeedTypeUpdateStockPrice => self::kFeedTypeUpdateStockPrice,
            self::kFeedTypeImportOrders => self::kFeedTypeImportOrders,
            self::kFeedTypeAcceptOrders => self::kFeedTypeAcceptOrders,
            self::kFeedTypeCancelOrders => self::kFeedTypeCancelOrders,
            self::kFeedTypeGetOrdersToShip => self::kFeedTypeGetOrdersToShip,
            self::kFeedTypeTracking => self::kFeedTypeTracking,
            self::kFeedTypeMedia => self::kFeedTypeMedia,
            self::kFeedTypeMatchingProducts => self::kFeedTypeMatchingProducts,
            self::kFeedTypeFulfillment => self::kFeedTypeFulfillment,
            self::kFeedTypeMatchingEAN => self::kFeedTypeMatchingEAN,
            self::kFeedTypeShippingCost => self::kFeedTypeShippingCost,
            self::kFeedTypeSubmissionResultProductCreation => self::kFeedTypeSubmissionResultProductCreation,
            self::kFeedTypeSubmissionResultProductUpdate => self::kFeedTypeSubmissionResultProductUpdate,
            self::kFeedTypeSubmissionResultTracking => self::kFeedTypeSubmissionResultTracking,
            self::kFeedTypeUpdateOrders => self::kFeedTypeUpdateOrders,
            self::kFeedTypeSellerInformation => self::kFeedTypeSellerInformation,
            self::kFeedTypeDivers => self::kFeedTypeDivers,
            self::kFeedTypeRelationship => self::kFeedTypeRelationship,
            self::kFeedTypeDeleteProduct => self::kFeedTypeDeleteProduct,
            self::kFeedTypeUnshippedOrders => self::kFeedTypeUnshippedOrders,
            self::kFeedTypeReviseProducts => self::kFeedTypeReviseProducts
        );
    }

    /**
     * Prune feeds older than delay days,
     * and put in error in ERROR feeds in SUBMITTED status older that 24 hours
     *
     * @param $delay : default value is one week
     */
    public function prune($delay = 7)
    {
        $count = 0;

        $limit = 3000;
        $maxDate = date('Y-m-d', time() - $delay * 24 * 3600);
        $feeds = Mage::getModel('MarketPlace/Feed')
                            ->getCollection()
                            ->addFieldToFilter('mp_date', array('lt' => $maxDate))
                            ->setOrder('mp_date', 'ASC');
        $feeds->getSelect()->limit($limit);
        foreach($feeds as $feed)
        {
            $feed->delete();
            $count++;
            if ($count > $limit)
                break;
        }

        return $count;
    }

}
