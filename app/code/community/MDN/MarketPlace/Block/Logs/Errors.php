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

class MDN_MarketPlace_Block_Logs_Errors extends Mage_Adminhtml_Block_Widget{
    
    /**
     * Get errors
     *
     * @return <type> $collection
     * @todo mettre à jour le filtre avec le champs is_error
     * @todo verifier que les erreurs s'affichent correctement
     */
    public function getErrors(){

        $account = Mage::getModel('MarketPlace/Countries')->getAccountByCountryId($this->getRequest()->getParam('country_id'));

        $mp = $account->getmpa_mp();
        
        //charge
        $collection = mage::getModel('MarketPlace/Logs')
                                ->getCollection()
                                ->addFieldToFilter('mp_is_error', array('nin'=>array(0,1,2)))
                                ->addFieldToFilter('mp_marketplace', $mp)
                                ->addAttributeToSort('mp_id', 'desc');

        $collection->getSelect()->limit(5);

        return $collection;

    }

}
