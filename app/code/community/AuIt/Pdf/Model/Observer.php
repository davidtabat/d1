<?php
/**
 * AuIt
 *
 * @category   AuIt
 * @package    AuIt_Editor
 * @author     M Augsten
 * @copyright  Copyright (c) 2008 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_Pdf_Model_Observer
{
	public function pdfinvoicesPredispatch(Varien_Event_Observer $observer)
    {
    	if ( $observer->getEvent()->getControllerAction()->getFullActionName() == 'adminhtml_sales_order_pdfinvoices')
    	{
			static $c=0;
			$c++;
			if (  $c > 2 ) return;
    		$e = new Mage_Core_Controller_Varien_Exception();
    		//$e->prepareForward('pdfinvoices','order','auit_pdf');
    		$e->prepareForward('pdfinvoices','auitpdf_order');
    		throw $e;
    	}
    }
	public function pdfshipmentsPredispatch(Varien_Event_Observer $observer)
    {
    	if ( $observer->getEvent()->getControllerAction()->getFullActionName() == 'adminhtml_sales_order_pdfshipments')
    	{
			static $c=0;
			$c++;
			if (  $c > 2 ) return;
    		$e = new Mage_Core_Controller_Varien_Exception();
    		//$e->prepareForward('pdfshipments','order','auit_pdf');
    		$e->prepareForward('pdfshipments','auitpdf_order');
    		throw $e;
    	}
    }
    public function pdfcreditmemosPredispatch(Varien_Event_Observer $observer)
    {
    	if ( $observer->getEvent()->getControllerAction()->getFullActionName() == 'adminhtml_sales_order_pdfcreditmemos')
    	{
			static $c=0;
			$c++;
			if (  $c > 2 ) return;
    		$e = new Mage_Core_Controller_Varien_Exception();
    		//$e->prepareForward('pdfcreditmemos','order','auit_pdf');
    		$e->prepareForward('pdfcreditmemos','auitpdf_order');
    		
    		throw $e;
    	}
    }
    public function pdfdocsPredispatch(Varien_Event_Observer $observer)
    {
    	if ( $observer->getEvent()->getControllerAction()->getFullActionName() == 'adminhtml_sales_order_pdfdocs')
    	{
			static $c=0;
			$c++;
			if (  $c > 2 ) return;
    		$e = new Mage_Core_Controller_Varien_Exception();
    		//$e->prepareForward('pdfdocs','order','auit_pdf');
    		$e->prepareForward('pdfdocs','auitpdf_order');
    		throw $e;
    	}
    }
    
    
    
	public function modelLoad(Varien_Event_Observer $observer)
    {
    	$object = $observer->getEvent()->getObject();
    	if ( $object instanceof Mage_Core_Model_Email_Template )
		{
			if ( Mage::getStoreConfigFlag('auit_pdf/general/attach_to_email') )
			{
				if ( !($object instanceof AuIt_Pdf_Model_Rewrite_Email_Template) )
				{
					if ( $object->getAuitAddAttachTemplate() != 10 )
					{
						$tmpId = $object->getTemplateId();
						$bfound = false;
						if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE) ||
							$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE) )
							$bfound = true;
						if ( !$bfound )
						{
							foreach ( Mage::app()->getStores() as $store )
							{
								if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE,$store) ||
									$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE,$store) )
								{
									$bfound = true;
									break;
								}
							}
						}
						if ( $bfound )
						{
							$block = '{{block type=\'core/template\' area=\'frontend\' template=\'auit/pdf/addpdf.phtml\' processor=$this shipment=$shipment order=$order}}';
							if ( strpos($object->getTemplateText(),'auit/pdf/addpdf') === false )
								$object->setTemplateText($object->getTemplateText().$block);
				  			$object->setAuitAddAttachTemplate(10);
						}
					}
				}
			}

			if ( Mage::getStoreConfigFlag('auit_pdf/general/attach_to_email_offer') )
			{
				if ( !($object instanceof AuIt_Pdf_Model_Rewrite_Email_Template) )
				{
					if ( $object->getAuitAddAttachTemplate() != 10 )
					{
						$tmpId = $object->getTemplateId();
						$bfound = false;
						if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE) ||
							$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE) )
							$bfound = true;
						if ( !$bfound )
						{
							foreach ( Mage::app()->getStores() as $store )
							{
								if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE,$store) ||
									$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE,$store) )
								{
									$bfound = true;
									break;
								}
							}
						}
						if ( $bfound )
						{
							$block = '{{block type=\'core/template\' area=\'frontend\' template=\'auit/pdf/addpdf_offer.phtml\' processor=$this shipment=$shipment order=$order}}';
							if ( strpos($object->getTemplateText(),'auit/pdf/addpdf') === false )
								$object->setTemplateText($object->getTemplateText().$block);
				  			$object->setAuitAddAttachTemplate(10);
						}
					}
				}
			}
			if ( Mage::getStoreConfigFlag('auit_pdf/general/attach_to_email_shipment') )
			{
				if ( !($object instanceof AuIt_Pdf_Model_Rewrite_Email_Template) )
				{
					if ( $object->getAuitAddAttachTemplate() != 10 )
					{
						$tmpId = $object->getTemplateId();
						$bfound = false;
						if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_TEMPLATE) ||
								$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_GUEST_TEMPLATE) )
							$bfound = true;
						if ( !$bfound )
						{
							foreach ( Mage::app()->getStores() as $store )
							{
								if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_TEMPLATE,$store) ||
										$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_GUEST_TEMPLATE,$store) )
								{
									$bfound = true;
									break;
								}
							}
						}
						if ( $bfound )
						{
							$block = '{{block type=\'core/template\' area=\'frontend\' template=\'auit/pdf/addpdf_shipment.phtml\' processor=$this shipment=$shipment order=$order}}';
							if ( strpos($object->getTemplateText(),'auit/pdf/addpdf') === false )
								$object->setTemplateText($object->getTemplateText().$block);
							$object->setAuitAddAttachTemplate(10);
						}
					}
				}
			}
			if ( Mage::getStoreConfigFlag('auit_pdf/general/attach_to_email_creditmemo') )
			{
				if ( !($object instanceof AuIt_Pdf_Model_Rewrite_Email_Template) )
				{
					if ( $object->getAuitAddAttachTemplate() != 10 )
					{
						$tmpId = $object->getTemplateId();
						$bfound = false;
						if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_TEMPLATE) ||
								$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_GUEST_TEMPLATE) )
							$bfound = true;
						if ( !$bfound )
						{
							foreach ( Mage::app()->getStores() as $store )
							{
								if ( $tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_TEMPLATE,$store) ||
										$tmpId == Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_GUEST_TEMPLATE,$store) )
								{
									$bfound = true;
									break;
								}
							}
						}
						if ( $bfound )
						{
							$block = '{{block type=\'core/template\' area=\'frontend\' template=\'auit/pdf/addpdf_creditmemo.phtml\' processor=$this shipment=$shipment order=$order}}';
							if ( strpos($object->getTemplateText(),'auit/pdf/addpdf') === false )
								$object->setTemplateText($object->getTemplateText().$block);
							$object->setAuitAddAttachTemplate(10);
						}
					}
				}
			}

		}
    }

	public function adminhtmlBefore(Varien_Event_Observer $observer)
    {
    	$block = $observer->getEvent()->getData('block');
    	
    	if ( $block instanceof Mage_Adminhtml_Block_Sales_Order_Grid )
    	{
    		$block->getMassactionBlock()->addItem('auit_pdfdocs_order_all', array(
    				'label'=> Mage::helper('auit_pdf')->__('Print Orders'),
    				'url'  => $block->getUrl('adminhtml/auitpdf_order/printall'),
    		));
    	}
    	else if (get_class($block) == 'BL_CustomGrid_Block_Rewrite_Mw_Ddate_Block_Adminhtml_Sales_Order_Grid')
    	{
    		$block->getMassactionBlock()->addItem('auit_pdfdocs_order_all', array(
    				'label'=> Mage::helper('auit_pdf')->__('SNM Print Orders'),
    				'url'  => $block->getUrl('adminhtml/auitpdf_order/printall'),
    		));
    	}
    	else if ( $block instanceof Mage_Adminhtml_Block_Sales_Order_View )
    	{
    		if ($block->getOrder()->getId()) {
    			
    			//$url = $block->getUrl('auit_pdf/order/print', array('order_id' => $block->getOrder()->getId()));
    			//$url = $block->getUrl('adminhtml/auitpdf_order/print', array('order_id' => $block->getOrder()->getId()));
    			
    			$url = $block->getUrl('adminhtml/auitpdf_order/print', array('order_id' => $block->getOrder()->getId()));
    			 
    			$block->addButton('print', array(
    					'label'     => Mage::helper('auit_pdf')->__('Print Order'),
    					'class'     => 'save',
    					'onclick'   => 'setLocation(\''.$url.'\')'
    			));
    		}
    	}
    }
	public function _checkPfad($toPfad)
	{
		$io = new Varien_Io_File();
		$path = Mage::getBaseDir('base').DS.$toPfad;
		if ( !$io->isWriteable($path) && !$io->mkdir($path, 0777, true)) {
			$msg = Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path);
			Mage::log($msg );
			return false;
		}
		return $path;
	}
	public function invoiceSaveAfter(Varien_Event_Observer $observer)
	{
		$_invoice = $observer->getEvent()->getInvoice();
		if ( $_invoice && $_invoice->getOrder())
		{
			$order=$_invoice->getOrder();
			$toPfad = trim(Mage::getStoreConfig('auit_pdf/general/save_pdf_invoice', $order->getStoreId()));
			if ( $toPfad ){
				$toPfad = $this->_checkPfad($toPfad);
				if ( $toPfad )
				{
					try {
						$pdf = Mage::getModel('auit_pdf/invoice')->getPdf(array($_invoice));
						$fname = Mage::helper('auit_pdf')->getPdfFName('fname_to_email',array($_invoice));
						file_put_contents($toPfad.DS.$fname,$pdf->render());
					}catch (Exception $e)
					{
						Mage::log("Can't create pdf after invoice save");
					}
				}
			}
		}
	}
	public function orderSaveAfter(Varien_Event_Observer $observer)
	{
		$order = $observer->getEvent()->getOrder();
		if ( $order )
		{
			$toPfad = trim(Mage::getStoreConfig('auit_pdf/general/save_pdf_order', $order->getStoreId()));
			if ( $toPfad ){
				$toPfad = $this->_checkPfad($toPfad);
				if ( $toPfad )
				{
					try {
						$pdf = Mage::getModel('auit_pdf/offer')->getPdf(array($order));
						$fname = Mage::helper('auit_pdf')->getPdfFName('fname_to_email_offer',array($order));
						file_put_contents($toPfad.DS.$fname,$pdf->render());
					}catch (Exception $e)
					{
						Mage::log("Can't create pdf after order save");
					}
				}
			}
		}
	}
	public function shipmentSaveAfter(Varien_Event_Observer $observer)
	{
		$shipment = $observer->getEvent()->getShipment();
		if ( $shipment && $shipment->getOrder())
		{
			$order=$shipment->getOrder();
			$toPfad = trim(Mage::getStoreConfig('auit_pdf/general/save_pdf_shipment', $order->getStoreId()));
			if ( $toPfad ){
				$toPfad = $this->_checkPfad($toPfad);
				if ( $toPfad ) {
					try {
						$pdf = Mage::getModel('auit_pdf/shipment')->getPdf(array($shipment));
						$fname = Mage::helper('auit_pdf')->getPdfFName('fname_to_email_shipment', array($shipment));
						file_put_contents($toPfad . DS . $fname, $pdf->render());
					} catch (Exception $e) {
						Mage::log("Can't create pdf after shipment save");
					}
				}
			}
		}
	}
	public function creditmemoSaveAfter(Varien_Event_Observer $observer)
	{
		$creditmemo = $observer->getEvent()->getCreditmemo();
		if ( $creditmemo && $creditmemo->getOrder())
		{
			$order=$creditmemo->getOrder();
			$toPfad = trim(Mage::getStoreConfig('auit_pdf/general/save_pdf_creditmemo', $order->getStoreId()));
			if ( $toPfad )
			{
				$toPfad = $this->_checkPfad($toPfad);
				if ( $toPfad ) {
					try {
						$pdf = Mage::getModel('auit_pdf/creditmemo')->getPdf(array($creditmemo));
						$fname = Mage::helper('auit_pdf')->getPdfFName('fname_to_email_creditmemo', array($creditmemo));
						file_put_contents($toPfad . DS . $fname, $pdf->render());
					} catch (Exception $e) {
						Mage::log("Can't create pdf after creditmemo save");
					}
				}
			}
		}
	}

}
