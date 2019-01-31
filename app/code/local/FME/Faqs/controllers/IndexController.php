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

    public function exportAction()
    {
    	$invoices = new Mage_Sales_Model_Order_Invoice_Api();
		$invoiceCollection = Mage::getResourceModel('sales/order_invoice_collection');
   		$data=$invoiceCollection->getData();
  		$fileName   = 'csvexport.csv';
        $store_collection = $invoiceCollection;
        $store_data = $invoiceCollection->getData();
        /** checks columns */
        $headers  = new Varien_Object(array(
            'entity_id' => Mage::helper('core')->__('entity id'),
            'store_id' => Mage::helper('core')->__('store id'),
            'base_grand_total' => Mage::helper('core')->__('base grand total'),
            'shipping_tax_amount' => Mage::helper('core')->__('shipping tax amount'),
            'tax_amount' => Mage::helper('core')->__('tax_amount'),
            'base_tax_amount' => Mage::helper('core')->__('base tax amount'),
            'store_to_order_rate' => Mage::helper('core')->__('store to order rate'),
            'base_shipping_tax_amount' => Mage::helper('core')->__('base_shipping_tax_amount'),
            'base_discount_amount' => Mage::helper('core')->__('base_discount_amount'),
            'base_to_order_rate' =>Mage::helper('core')->__('base_to_order_rate'),
            'grand_total' => Mage::helper('core')->__('grand_total'),
            'shipping_amount' => Mage::helper('core')->__('shipping_amount'),
            'subtotal_incl_tax' => Mage::helper('core')->__('subtotal_incl_tax'),
            'base_subtotal_incl_tax' => Mage::helper('core')->__('base_subtotal_incl_tax'),
            'store_to_base_rate' => Mage::helper('core')->__('store_to_base_rate'),
            'base_shipping_amount' => Mage::helper('core')->__('base_shipping_amount'),
            'total_qty' => Mage::helper('core')->__('total_qty'),
            'base_to_global_rate' => Mage::helper('core')->__('base_to_global_rate'),
            'subtotal' => Mage::helper('core')->__('subtotal'),
            'base_subtotal' => Mage::helper('core')->__('base_subtotal'),
            'discount_amount' => Mage::helper('core')->__('discount_amount'),
            'billing_address_id' => Mage::helper('core')->__('billing_address_id'),
            'is_used_for_refund' => Mage::helper('core')->__('is_used_for_refund'),
            'order_id' => Mage::helper('core')->__('order_id'),
            'email_sent' => Mage::helper('core')->__('email_sent'),
            'can_void_flag' => Mage::helper('core')->__('can_void_flag'),
            'state' => Mage::helper('core')->__('state'),
            'shipping_address_id' => Mage::helper('core')->__('shipping_address_id'),
            'store_currency_code' => Mage::helper('core')->__('store_currency_code'),
            'transaction_id' => Mage::helper('core')->__('transaction_id'),
            'order_currency_code' => Mage::helper('core')->__('order_currency_code'),
            'base_currency_code' => Mage::helper('core')->__('base_currency_code'),
            'global_currency_code' => Mage::helper('core')->__('global_currency_code'),
            'increment_id' => Mage::helper('core')->__('increment_id'),
            'created_at' => Mage::helper('core')->__('created_at'),
            'updated_at' => Mage::helper('core')->__('updated_at'),
            'hidden_tax_amount' => Mage::helper('core')->__('hidden_tax_amount'),
            'base_hidden_tax_amount' => Mage::helper('core')->__('base_hidden_tax_amount'),
            'shipping_hidden_tax_amount' => Mage::helper('core')->__('shipping_hidden_tax_amount'),
            'base_shipping_hidden_tax_amnt' => Mage::helper('core')->__('base_shipping_hidden_tax_amnt'),
            'shipping_incl_tax' => Mage::helper('core')->__('shipping_incl_tax'),
            'base_shipping_incl_tax' => Mage::helper('core')->__('base_shipping_incl_tax'),
            'base_total_refunded' => Mage::helper('core')->__('base_total_refunded')
        ));

       $template ='"{{entity_id}}","{{store_id}}","{{base_grand_total}}","{{shipping_tax_amount}}","{{tax_amount}}","{{base_tax_amount}}","{{store_to_order_rate}}","{{base_shipping_tax_amount}}","{{base_discount_amount}}","{{base_to_order_rate}}","{{grand_total}}","{{shipping_amount}}","{{subtotal_incl_tax}}","{{base_subtotal_incl_tax}}","{{store_to_base_rate}}","{{base_shipping_amount}}","{{total_qty}}","{{base_to_global_rate}}","{{subtotal}}","{{base_subtotal}}","{{discount_amount}}","{{billing_address_id}}","{{is_used_for_refund}}","{{order_id}}","{{email_sent}}","{{can_void_flag}}","{{state}}","{{shipping_address_id}}","{{store_currency_code}}","{{transaction_id}}","{{order_currency_code}}","{{base_currency_code}}","{{global_currency_code}}","{{increment_id}}","{{created_at}}","{{updated_at}}","{{hidden_tax_amount}}","{{base_hidden_tax_amount}}","{{shipping_hidden_tax_amount}}","{{base_shipping_hidden_tax_amnt}}","{{shipping_incl_tax}}","{{base_shipping_incl_tax}}","{{base_total_refunded}}"';
       $content = $headers->toString($template);
       $content .= "\n";
       $storeDataTemplate       = array();
       while ($data = $store_collection->fetchItem()) {
            
            $data->addData($storeDataTemplate);
            $content .= $data->toString($template) . "\n";
        }
        
        $this->_sendUploadResponse($fileName, $content);

    }

     protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
 
}
