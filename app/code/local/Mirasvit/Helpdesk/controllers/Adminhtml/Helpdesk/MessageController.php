<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.2.4
 * @build     2266
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Adminhtml_Helpdesk_MessageController extends Mage_Adminhtml_Controller_Action
{
    protected function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    protected function _isAllowed()
    {
        return true; Mage::getSingleton('admin/session')->isAllowed('helpdesk/ticket');
    }

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('helpdesk');

        return $this;
    }

    /**
     * init message.
     *
     * @return Mirasvit_Helpdesk_Model_Message
     */
    public function _initMesssage()
    {
        $message = Mage::getModel('helpdesk/message');
        if ($this->getRequest()->getParam('id')) {
            $message->load($this->getRequest()->getParam('id'));
        }

        Mage::register('current_message', $message);

        return $message;
    }

    /**
     * edit message
     */
    public function editAction()
    {
        if (!Mage::helper('helpdesk/permission')->isMessageEditAllowed()) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('adminhtml')
                        ->__('You don\'t have permissions to edit this message. Please, contact your administrator.')
                );
            $this->_redirect('*/helpdesk_ticket/');

            return;
        }
        $message = $this->_initMesssage();

        if ($message->getId()) {
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Messages'),
                    Mage::helper('adminhtml')->__('Messages'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Message '),
                    Mage::helper('adminhtml')->__('Edit Message '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('helpdesk/adminhtml_message_edit'))
                    ->_addLeft($this->getLayout()->createBlock('helpdesk/adminhtml_message_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The message does not exist.'));
            $this->_redirect('*/helpdesk_ticket/');

            return;
        }
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        if (!Mage::helper('helpdesk/permission')->isMessageEditAllowed()) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('adminhtml')
                        ->__('You don\'t have permissions to edit this message. Please, contact your administrator.')
                );
            $this->_redirect('*/helpdesk_ticket/');

            return;
        }
        if ($message = $this->_initMesssage()) {
            try {
                $message->setBody($this->getRequest()->getParam('reply'));
                $message->save();

                Mage::helper('helpdesk/history')
                    ->changeMessage(
                        $message,
                        Mirasvit_Helpdesk_Model_Config::USER,
                        array('user' => Mage::getSingleton('admin/session')->getUser()),
                        Mirasvit_Helpdesk_Model_Config::MESSAGE_EDIT
                    );

                $this->_redirect('*/helpdesk_ticket/edit', array('id' => $message->getTicketId()));

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());

                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find message to save'));
        $this->_redirect('*/*/');

        return;
    }
}
