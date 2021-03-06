<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Flags
 */
class Amasty_Flags_Adminhtml_AmflagsassignController extends Mage_Adminhtml_Controller_Action
{
    public function setFlagAction()
    {
        $orderId  = $this->getRequest()->getParam('orderId');
        $flagId   = $this->getRequest()->getParam('flagId');
        $columnId = $this->getRequest()->getParam('columnId');
        $comment  = $this->getRequest()->getParam('comment');
        $title     =$this->getRequest()->getParam('title');
        if ($orderId)
        {
            try
            {
                $orderFlags = Mage::getModel('amflags/order_flag')->getCollection();
                $orderFlags->getSelect()->where('order_id = ?', $orderId)->where('column_id = ?', $columnId);
                if ($orderFlags->getSize() > 0)
                {
                    foreach ($orderFlags as $orderFlag)
                        if (0 == $flagId)
                        {
                            // removing flag
                            $orderFlag->delete();
                        } else
                        {
                            $orderFlag->setOrderId($orderId);
                            $orderFlag->setFlagId($flagId);
                            $orderFlag->setColumnId($columnId);
                            $orderFlag->setComment($comment);
                            $orderFlag->save();
                        }
                } else
                {
                    $data['order_id']  = $orderId;
                    $data['flag_id']   = $flagId;
                    $data['column_id'] = $columnId;
                    $data['comment']   = $comment;
                    Mage::getModel('amflags/order_flag')->setData($data)->save();
                }

                /*Add comment*/
        $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $comment = 'Column: '.$title.' : '. $comment;
               $order->addStatusHistoryComment($comment);
                $order->save();
                } 
                  

            } catch (Exception $e)
            {
                $this->_getSession()->addException($e, Mage::helper('amflags')->__('An error occurred while setting order flag.'));
            }
        }
        return true;
    }

    public function massApplyAction()
    {
        $orderIds = Mage::app()->getRequest()->getPost('order_ids'); // sometimes 'order_ids' = Mage::app()->getRequest()->getPost('massaction_prepare_key')
        
        /* Custom Code By Ahmed */
            $shipmentFlag = 0;
            if(!$orderIds) {
                $shipmentIds = Mage::app()->getRequest()->getPost('shipment_ids');

                if(!empty($shipmentIds)) {
                    $shipmentFlag = 1;
                    $shipment = Mage::getModel('sales/order_shipment')->getCollection();
                    $shipment->addFieldToFilter('entity_id',$shipmentIds);

                    foreach ($shipment as $key => $value) {
                        $orderIds[] = $value->getOrderId();
                    }
                }
            }
        /* End of Custom Code By Ahmed */

        if (is_array($orderIds) && !empty($orderIds))
        {
            $columnId = Mage::app()->getRequest()->getParam('column');
            $flagId = Mage::app()->getRequest()->getPost('flags_' . $columnId);

            if (strlen($columnId))
            {
                if ('0' === $columnId)
                {
                    // remove flags
                    $all = false;
                    if ('0' === $flagId)
                    {
                        $all = true;
                        $columnCollection = Mage::getModel('amflags/column')->getCollection();
                    }
                    foreach ($orderIds as $orderId)
                    {
                        try
                        {
                            if ($all)
                            {
                                // remove flags from all columns
                                foreach ($columnCollection as $column)
                                {
                                    $orderFlag = Mage::getModel('amflags/order_flag')->loadByColumnIdAndOrderId($orderId, $column->getEntityId());
                                    $orderFlag->delete();
                                }
                            } else
                            {
                                $orderFlag = Mage::getModel('amflags/order_flag')->loadByColumnIdAndOrderId($orderId, $flagId);
                                $orderFlag->delete();
                            }
                        } catch (Exception $e)
                        {
                            Mage::getSingleton('adminhtml/session')->addException($e, Mage::helper('amflags')->__('An error occurred while removing flags.'));
                            
                            /* Custom Code By Ahmed */
                            if($shipmentFlag) {
                                $this->_redirect('adminhtml/sales_shipment');
                                return;
                            } else {
                                $this->_redirect('adminhtml/sales_order');
                                return;
                            }
                            /* End of Custom Code By Ahmed */
                            /* Old code was */
                                //$this->_redirect('adminhtml/sales_order');
                                //return;
                            /* End of Old */
                           
                        }
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amflags')->__('The flags have been removed.'));
                } else
                {
                    foreach ($orderIds as $orderId)
                    {
                        try
                        {
                            $orderFlag = Mage::getModel('amflags/order_flag')->loadByColumnIdAndOrderId($orderId, $columnId);
                            $orderFlag->setOrderId($orderId);
                            $orderFlag->setFlagId($flagId);
                            $orderFlag->setColumnId($columnId);
                            $orderFlag->save();
                        } catch (Exception $e)
                        {
                            Mage::getSingleton('adminhtml/session')->addException($e, Mage::helper('amflags')->__('An error occurred while setting order flags.'));
                            
                            /* Custom Code By Ahmed */
                            if($shipmentFlag) {
                                $this->_redirect('adminhtml/sales_shipment');
                                return;
                            } else {
                                $this->_redirect('adminhtml/sales_order');
                                return;
                            }
                            /* End of Custom Code By Ahmed */
                            /* Old code was */
                                //$this->_redirect('adminhtml/sales_order');
                                //return;
                            /* End of Old */
                        }
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amflags')->__('The flags have been applied.'));
                }
                
                /* Custom Code By Ahmed */
                if($shipmentFlag) {
                    $this->_redirect('adminhtml/sales_shipment');
                    return;
                } else {
                    $this->_redirect('adminhtml/sales_order');
                    return;
                }
                /* End of Custom Code By Ahmed */
                /* Old code was */
                    //$this->_redirect('adminhtml/sales_order');
                    //return;
                /* End of Old */
            } else
            {
                Mage::getSingleton('adminhtml/session')->addError($this->__('No action specified.'));
                
                /* Custom Code By Ahmed */
                if($shipmentFlag) {
                    $this->_redirect('adminhtml/sales_shipment');
                    return;
                } else {
                    $this->_redirect('adminhtml/sales_order');
                    return;
                }
                /* End of Custom Code By Ahmed */
                /* Old code was */
                    //$this->_redirect('adminhtml/sales_order');
                    //return;
                /* End of Old */
            }
        } else
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No orders selected.'));

            /* Custom Code By Ahmed */
            if($shipmentFlag) {
                $this->_redirect('adminhtml/sales_shipment');
                return;
            } else {
                $this->_redirect('adminhtml/sales_order');
                return;
            }
            /* End of Custom Code By Ahmed */
            /* Old code was */
                //$this->_redirect('adminhtml/sales_order');
                //return;
            /* End of Old */
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/assign_flags');
    }
}