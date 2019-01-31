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
 * 	       1 - All topics - 09-04-2012
 * 	       2 - Selected topics - 09-04-2012
 * 	       3 - Faqs of topics - 09-04-2012
 * 	       
 * @copyright  Copyright 2012 © www.fmeextensions.com All right reserved
 */

class FME_Faqs_Block_Topic extends Mage_Core_Block_Template
{
	
	public function _prepareLayout()
    {
    	
    	 if ( Mage::getStoreConfig('web/default/show_cms_breadcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) ) {
            $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
            $breadcrumbs->addCrumb('faqs_home', array('label' => Mage::helper('faqs')->getListPageTitle(), 'title' => Mage::helper('faqs')->getListPageTitle()));
        }
        
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle(Mage::helper('faqs')->getListPageTitle());
            $head->setDescription(Mage::helper('faqs')->getListMetaDescription());
            $head->setKeywords(Mage::helper('faqs')->getListMetaKeywords());
        }
		
        return parent::_prepareLayout();
        
    }
    
    
    /* Get all topics by Order ASC */    
     public function getTopics()     
     {	
		$collection = Mage::getModel('faqs/topic')->getCollection()
					->addStoreFilter(Mage::app()->getStore(true)->getId())
					->addFieldToFilter('status',1)
					->setOrder('main_table.topic_order','ASC')
					->getData();
			
        if (!$this->hasData('topic')) {
            $this->setData('topic', $collection);
        }
        return $this->getData('topic');
        
    }
    
    
    /* Get SELECTED topics by Order ASC */    
     public function getSelectedTopics()     
     {	
		$collection = Mage::getModel('faqs/topic')->getCollection()
					->addStoreFilter(Mage::app()->getStore(true)->getId())
					->addFieldToFilter('status',1)
					->addFieldToFilter('main_table.show_on_main',1)
					->setOrder('main_table.topic_order','ASC')
					->getData();
			
        if (!$this->hasData('topic')) {
            $this->setData('topic', $collection);
        }
        return $this->getData('topic');
        
    }
    
    
    
    /* Get FAQs of topics by faq Order ASC*/    
     public function getFaqsOfTopics($topicId){
	
	$collection = Mage::getModel('faqs/faqs')->getCollection()
						->addFieldToFilter('topic_id',$topicId)
						->addFieldToFilter('status',1)
						->addFieldToFilter('main_table.show_on_main',1)
						->setOrder('main_table.faq_order','ASC')
						->getData();
	
	
	return $collection;
	
	
    }
}