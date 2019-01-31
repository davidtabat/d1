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
 * 	       1 - ratingAction - 09-04-2012
 * 	       
 * @copyright  Copyright 2012 © www.fmeextensions.com All right reserved
 */
 
class FME_Faqs_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() 
	{
		$this->loadLayout();		
		$this->renderLayout();
    }
    
 
    public function ratingAction() 
    {
	

	if($data = $this->getRequest()->getPost()){
	    
	    try{
	    
		$read_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$faqsTable = Mage::getSingleton('core/resource')->getTableName('faqs/faqs');
		$select = $read_connection->select()->from($faqsTable, array('*'))->where('faqs_id=(?)', $data['faq_id']); 
		$result_row =$read_connection->fetchRow($select);
	      
    
		if($result_row != null){
		    $write_connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		    $write_connection->beginTransaction();
		    
		    $fields = array();	    
		    $fields['rating_num']	= $result_row['rating_num']+$data['value'];
		    $fields['rating_count']	= $result_row['rating_count']+1;
		    $fields['rating_stars']	= $fields['rating_num']/$fields['rating_count'];
		    
		    $where = $write_connection->quoteInto('faqs_id =?', $data['faq_id']);
		    $write_connection->update($faqsTable, $fields, $where);
		    $write_connection->commit();
		    
		    
		    //Check session for faqs id
		    $faqs_session_array = Mage::getSingleton('customer/session')->getRatedFaqsId();
		    
		    if(!is_array($faqs_session_array)){		    
			$faqs_session_array = array();
		    }
		    
		    // check this array and increment the index to save next faq id
		       
		    $faqs_session_array[] = $data['faq_id'];
		    Mage::getSingleton('customer/session')->setRatedFaqsId($faqs_session_array);
		    
		    echo Mage::helper('faqs')->__('Thankyou for Rating ');
		}
	    }catch (Exception $e){
		
		echo Mage::helper('faqs')->__('Unable to process Rating ');
	    }
	    
	}
	
	
    }
    
    
    
    
        
    public function viewAction()
   {
	   
		$post = $this->getRequest()->getPost();
		if($post){
		    
			$sterm=$post['faqssearch'];
			$this->_redirect('*/*/search', array('term' => $sterm));
				return;   
		}
		
		$topicId = $this->_request->getParam('id', null);
	
    	if ( is_numeric($topicId) ) {
			
			$faqsTable = Mage::getSingleton('core/resource')->getTableName('faqs');
			$faqsTopicTable = Mage::getSingleton('core/resource')->getTableName('faqs_topics');
			$faqsStoreTable = Mage::getSingleton('core/resource')->getTableName('faqs_store');
		
			$sqry = "select f.*,t.title as cat from ".$faqsTable." f, ".$faqsTopicTable." t where f.topic_id='$topicId' and f.status=1 and t.topic_id='$topicId' ORDER BY f.faq_order ASC"; 
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $connection->query($sqry);
			$collection = $select->fetchAll();
			
			
			if(count($collection) != 0){
				Mage::register('faqs', $collection);
			} else {
				Mage::register('faqs', NULL); 
			}
			
    	} else {
			
			Mage::register('faqs', NULL); 
		}
		
		$this->loadLayout();   
		$this->renderLayout();	
    }
    
    public function searchAction()
    {
    	
		$faqsTable = Mage::getSingleton('core/resource')->getTableName('faqs');
		$faqsTopicTable = Mage::getSingleton('core/resource')->getTableName('faqs_topics');
		$faqsStoreTable = Mage::getSingleton('core/resource')->getTableName('faqs_store');
		
		$sterm = $this->getRequest()->getParam('term');
		$post = $this->getRequest()->getPost();
		if($post){  
			$sterm=$post['faqssearch'];    
		}
		
		
		
		if(isset($sterm)){
			$sqry = "select * from ".$faqsTable." f,".$faqsStoreTable." fs where (f.title like '%$sterm%' or f.faq_answar like '%$sterm%') and (status=1)
			and f.topic_id = fs.topic_id
			and (fs.store_id =".Mage::app()->getStore()->getId()." OR fs.store_id=0) ORDER BY f.faq_order ASC";
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $connection->query($sqry);
			$sfaqs = $select->fetchAll();
			if(count($sfaqs) != 0){
				Mage::register('faqs', $sfaqs);
			} 
		}
		
		
		$this->loadLayout();   
		$this->renderLayout();

    }

    public function topicsAction()
    {
		$this->loadLayout();   
		$this->renderLayout();
    }
 
}
