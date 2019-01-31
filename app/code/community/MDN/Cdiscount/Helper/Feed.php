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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Cdiscount_Helper_Feed extends MDN_MarketPlace_Helper_Feed {

    const kFeedTypeGetAllowedCategoryTree = '_GET_ALLOWED_CATEGORY_';
    const kFeedTypeGetAllAllowedCategoryTree = '_GET_ALL_ALLOWED_CATEGORY_';
    const kFeedTypeGetModelList = '_GET_MODEL_LIST_';
    const kFeedTypeGetAllModelList = '_GET_ALL_MODEL_LIST_';
    const kFeedTypeGetSellerInformation = '_GET_SELLER_INFORMATION_';
    const kFeedTypeUpdateOrders = '_UPDATE_ORDER_';
    
    /**
     * Get feed submission result
     * 
     * @throws Exception 
     */
    public function getFeedSubmissionResult(){
        throw new Exception('Not implemented yet!');
    }

}
