<?php
/**
 * Advance FAQ Management Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Advance FAQ Management
 * @author     Kamran Rafiq Malik <support@fmeextensions.com>
 *                          
 * 	       Asif Hussain <support@fmeextensions.com>
 * 	        	       
 * @copyright  Copyright 2012 © www.fmeextensions.com All right reserved
 */

class FME_Faqs_Block_Block extends Mage_Core_Block_Template
{
	 public function getItems($limit = 5)  {

		
					
	 if(Mage::getStoreConfig('faqs/list/display_categories') == 'selected'):  
	
		  $collection = Mage::getModel('faqs/topic')->getCollection()
					->addStoreFilter(Mage::app()->getStore(true)->getId())
					->addFieldToFilter('status',1)
					->addOrder('main_table.topic_order', 'asc')
					->addFieldToFilter('show_on_main',1)
					->setPageSize($limit)
					->getData();
	 else :
	 
	 
		  $collection = Mage::getModel('faqs/topic')->getCollection()
					->addStoreFilter(Mage::app()->getStore(true)->getId())
					->addFieldToFilter('status',1)
					->addOrder('main_table.topic_order', 'asc')
					->setPageSize($limit)
					->getData();
	 endif;
	 
	 
      	return $collection;
        
    }  
}