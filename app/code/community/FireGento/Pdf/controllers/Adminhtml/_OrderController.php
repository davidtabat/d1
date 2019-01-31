<?php
class FireGento_Pdf_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function printAction()
    {
 	echo "Testing"; exit();
        if ($id = $this->getRequest()->getParam('order_id')) {
            if ($order = Mage::getModel('sales/order')->load($id)) {
                $pdf = Mage::getModel('auit_pdf/offer')->getPdf(array($order));
                $fname = Mage::helper('auit_pdf')->getPdfFName('fname_to_email_offer',array($order));
                $this->_prepareDownloadResponse($fname, $pdf->render(), 'application/pdf');
            }
        }
        else {
            echo "Testing"; exit();
           // $this->_forward('noRoute');
        }
    }

     /**
    * Set unpaid order to paid
    */
    public function paidAction()
    {
        // 
       if ($id = $this->getRequest()->getPost('order_id')) {

            if ($order = Mage::getModel('sales/order')->load($id)) {
                if ($this->getRequest()->getPost('amount')) 
                {
                    $amount=$this->getRequest()->getPost('amount');
                    $basetotaldue=$amount;

                }else{
                $amount=$order->getTotalDue();
                $basetotaldue=$order->getBaseTotalDue();
                    }
               try {
                    $order->setTotalPaid($amount); 
                    $order->setBaseTotalPaid($basetotaldue); 
                    $order->save();
                 
               } catch (Exception $e) {
                   echo "Error ". $e->getMessage();
               }
                  
            }
            echo $amount .' Set';
           // Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$id)));
        }
        else {
            
            echo 'No order ';
        } 
       
    }

     public function setpaidAction()
    {
        // 
       if ($id = $this->getRequest()->getParam('order_id')) {

            if ($order = Mage::getModel('sales/order')->load($id)) {
                
                
                $basetotalpaid=$order->getTotalPaid();
                $basetotaldue=0;
            
               try {
                    $order->setBaseTotalDue($basetotaldue); 
                    $order->setBaseTotalPaid($basetotalpaid); 
                  $order->save();
                  
                    
                    
                 
               } catch (Exception $e) {
                   echo "Error ". $e->getMessage();
               }
                  
            }
            
           Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$id)));
        }
        else {
            
            echo 'No order ';
        } 
       
    }
    
    /**
     * Print all documents for selected orders
     */
    public function printallAction(){
    	$orderIds = $this->getRequest()->getPost('order_ids');
    	$flag = false;
    	if (!empty($orderIds)) {
    		foreach ($orderIds as $orderId) {
    			if ($order = Mage::getModel('sales/order')->load($orderId)) {
    				$flag = true;
    				if (!isset($pdf)){
    					$model = Mage::getModel('auit_pdf/offer');
    					$model->bAddAppendix=false;
    					$pdf = $model->getPdf(array($order));
    						
    				} else {
    					$model = Mage::getModel('auit_pdf/offer');
    					$model->bAddAppendix=false;
    					$pages = $model->getPdf(array($order));
    					$pdf->pages = array_merge ($pdf->pages, $pages->pages);
    				}
    			}
    		}
    		if ($flag) {
    			return $this->_prepareDownloadResponse(
    					'docs'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
    					$pdf->render(), 'application/pdf'
    			);
    		} else {
    			$this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
    			$this->_redirect('*/*/');
    		}
    	}
    	$this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
    	return true;
    }
}
