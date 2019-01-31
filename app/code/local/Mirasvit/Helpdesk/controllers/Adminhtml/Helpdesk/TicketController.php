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



class Mirasvit_Helpdesk_Adminhtml_Helpdesk_TicketController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return Mirasvit_Helpdesk_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('helpdesk');

        return $this;
    }

    private function saveStoreSelection()
    {
        $storeId = $this->getRequest()->getParam('store');
        $user = Mage::getSingleton('admin/session')->getUser();
        if ($storeId || $storeId == -1) { //small hack here. i don't see other way to solve this.
            if ($storeId == -1) {
                $storeId = null;
            }
            if ($helpdeskUser = Mage::helper('helpdesk')->getHelpdeskUser($user)) {
                $helpdeskUser->setStoreId($storeId);
                $helpdeskUser->save();
            }
        }
    }

    public function indexAction()
    {
        Mage::register('is_archive', (bool) $this->getRequest()->getParam('is_archive'));
        $this->saveStoreSelection();
        $this->_title($this->__('Tickets'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('helpdesk/adminhtml_ticket'));
        $this->renderLayout();
    }

    public function addAction()
    {
        $this->_title($this->__('New Ticket'));

        $ticket = $this->_initTicket();
        $user = Mage::getSingleton('admin/session')->getUser();
        $customer = false;

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $ticket->setData($data);
        } elseif ($data = Mage::getSingleton('adminhtml/session')->getTicketData()) {
            $ticket->setData($data);
            Mage::getSingleton('adminhtml/session')->unsTicketData();
        }

        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $ticket->setCustomerId($customerId);
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getId()) {
                $ticket->setCustomerEmail($customer->getEmail());
            }
        } elseif ($orderId = $this->getRequest()->getParam('order_id')) {
            $ticket->initFromOrder($orderId);
        }
        if (Mage::app()->isSingleStoreMode()) {
            $ticket->setStoreId(Mage::app()->getStore(true)->getId());
        } elseif ($customer && ($storeId = $customer->getStoreId())) {
            $ticket->setStoreId($storeId);
        } elseif ($storeId = $this->getRequest()->getParam('store_id')) {
            $ticket->setStoreId($storeId);
        } elseif ($storeId = Mage::helper('helpdesk')->getHelpdeskUser($user)->getStoreId()) {
            $ticket->setStoreId($storeId);
        }

        // Overwork to preserve all data, if store view selection is needed
        if (!$ticket->getStoreId()) {
            Mage::getSingleton('adminhtml/session')->setTicketData($ticket->getData());
        }

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Ticket  Manager'),
                Mage::helper('adminhtml')->__('Ticket Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Ticket '), Mage::helper('adminhtml')->__('Add Ticket'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit'))
                ->_addLeft($this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tabs'));
        $this->renderLayout();
    }

    public function editAction()
    {
        Mage::register('is_archive', (bool) $this->getRequest()->getParam('is_archive'));

        $ticket = $this->_initTicket();

        if ($ticket->getId()) {
            $this->_title(Mage::helper('helpdesk')->htmlEscape($ticket->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Tickets'),
                    Mage::helper('adminhtml')->__('Tickets'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Ticket '),
                    Mage::helper('adminhtml')->__('Edit Ticket '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit'))
                    ->_addLeft($this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The ticket does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (!isset($data['customer_email']) && isset($data['store_id'])) {
                $this->_redirect('*/*/add', array('store_id' => $data['store_id']));

                return;
            }

            try {
                $user = Mage::getSingleton('admin/session')->getUser();
                $ticket = Mage::helper('helpdesk/process')->createOrUpdateFromBackendPost($data, $user);

                if ($data['reply'] != '' && $data['reply_type'] != Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Message was successfully sent'));
                } else {
                    if ($ticket->getOrigData('is_archived') != $ticket->getData('is_archived')) {
                        if ($ticket->getIsArchived()) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ticket was moved to archive'));
                        } else {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ticket was moved from archive'));
                        }
                    } else {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ticket was successfully updated'));
                    }
                }
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $ticket->getId()));

                    return;
                }

                $this->_redirect('*/*/', array('is_archive' => $ticket->getOrigData('is_archived')));

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                if ($this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                } else {
                    $this->_redirect('*/*/add');
                }

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find ticket to save'));
        $this->_redirect('*/*/');
    }

    public function archiveAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $ticket = Mage::getModel('helpdesk/ticket')->load($this->getRequest()->getParam('id'));
                $ticket
                    ->setIsArchived(true)
                    ->save();

                Mage::helper('helpdesk/history')->changeTicket($ticket, Mirasvit_Helpdesk_Model_Config::USER,
                    array('user' => Mage::getSingleton('admin/session')->getUser()));

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Ticket was moved to archive'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    public function restoreAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $status = Mage::getModel('helpdesk/status')->loadByCode(Mirasvit_Helpdesk_Model_Config::STATUS_OPEN);

                $ticket = Mage::getModel('helpdesk/ticket')->load($this->getRequest()->getParam('id'));
                $ticket
                    ->setStatusId($status->getId())
                    ->setIsArchived(false)
                    ->save();

                Mage::helper('helpdesk/history')->changeTicket($ticket, Mirasvit_Helpdesk_Model_Config::USER,
                    array('user' => Mage::getSingleton('admin/session')->getUser()));

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Ticket was moved to the Tickets List'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0 && Mage::helper('helpdesk/permission')->isTicketRemoveAllowed()) {
            try {
                $ticket = Mage::getModel('helpdesk/ticket');

                $ticket->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Ticket was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    public function spamAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        try {
            $ticket = Mage::getModel('helpdesk/ticket')->load($id);
            $ticket->markAsSpam();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Ticket was successfully moved to the spam folder'));
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                ->getParam('id'), ));
        }
        $this->_redirect('*/*/');
    }

    public function massChangeAction()
    {
        $ids = $this->getRequest()->getParam('ticket_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select ticket(s)'));
        } else {
            try {
                $statusId = $this->getRequest()->getParam('status');
                $owner = $this->getRequest()->getParam('owner');
                $spam = $this->getRequest()->getParam('spam');
                $archive = $this->getRequest()->getParam('archive');
                foreach ($ids as $id) {
                    $ticket = Mage::getModel('helpdesk/ticket')
                        ->setIsMassDelete(true)
                        ->load($id);
                    if ($spam) {
                        $ticket->markAsSpam();
                        continue;
                    }
                    if ($archive) {
                        $ticket->setIsArchived(true);
                    }
                    if ($statusId) {
                        $ticket->setStatusId($statusId);
                    }
                    if ($owner) {
                        $ticket->initOwner($owner);
                    }
                    $ticket->save();
                    Mage::helper('helpdesk/history')->changeTicket($ticket, Mirasvit_Helpdesk_Model_Config::USER,
                        array('user' => Mage::getSingleton('admin/session')->getUser()));
                }
                if ($spam) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were moved to the Spam folder', count($ids)
                        )
                    );
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were successfully updated', count($ids)
                        )
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massMergeAction()
    {
        $ids = $this->getRequest()->getParam('ticket_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select ticket(s)'));
        } else {
            Mage::helper('helpdesk/process')->mergeTickets($ids);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully merged', count($ids)
                )
            );
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        if (!Mage::helper('helpdesk/permission')->isTicketRemoveAllowed()) {
            return;
        }
        $ids = $this->getRequest()->getParam('ticket_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select ticket(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $ticket = Mage::getModel('helpdesk/ticket')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $ticket->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * init ticket.
     *
     * @return Mirasvit_Helpdesk_Model_Ticket
     */
    public function _initTicket()
    {
        $ticket = Mage::getModel('helpdesk/ticket');
        if ($this->getRequest()->getParam('id')) {
            $ticket->load($this->getRequest()->getParam('id'));
            Mage::helper('helpdesk/permission')->checkReadTicketRestrictions($ticket);
        }

        Mage::register('current_ticket', $ticket);

        return $ticket;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdesk/ticket');
    }

    /************************/

    /**
     * Ajax.
     */
    public function customerfindAction()
    {
        $q = $this->getRequest()->getParam('q');
        $result = Mage::helper('helpdesk')->findCustomer($q);
        echo Mage::helper('core')->jsonEncode($result);
        die;
    }

    /**
     * Ajax.
     */
    public function attachmentAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var Mirasvit_Helpdesk_Model_Attachment $attachment */
        $attachment = Mage::getModel('helpdesk/attachment')->load($id);
        // give our picture the proper headers...otherwise our page will be confused
        $downloadName = $attachment->getName();
        header("Content-Disposition: attachment; filename=\"{$downloadName}\"");
        header("Content-length: {$attachment->getSize()}");
        header("Content-type: {$attachment->getType()}");
        echo $attachment->getBody();
        die;
    }

    /**
     * Ajax.
     */
    public function sourceAction()
    {
        $id = (int) $this->getRequest()->getParam('message_id');
        $message = Mage::getModel('helpdesk/message')->load($id);
        $ticket = $message->getTicket();
        Mage::helper('helpdesk/permission')->checkReadTicketRestrictions($ticket);
        $body = $message->getBody();
        if ($emailId = $message->getEmailId()) {
            $email = Mage::getModel('helpdesk/email')->load($emailId);
            $body = $email->getHeaders();
        }

        echo '<pre style="width: 1200px;">'.htmlentities($body).'</pre>';
        die;
    }

    public function deleteMessageAction()
    {
        if (!Mage::helper('helpdesk/permission')->isMessageRemoveAllowed()) {
            echo Mage::helper('core')->jsonEncode(Mage::helper('adminhtml')
                ->__('You don\'t have permissions to remove this message. Please, contact your administrator.'));
            die;
        }
        $response = '';
        if ($messageId = (int) $this->getRequest()->getParam('message_id')) {
            $message = Mage::getModel('helpdesk/message')->load($messageId);
            $message->setIsDeleted(1);
            $message->save();

            Mage::helper('helpdesk/history')
                ->changeMessage(
                    $message,
                    Mirasvit_Helpdesk_Model_Config::USER,
                    array('user' => Mage::getSingleton('admin/session')->getUser()),
                    Mirasvit_Helpdesk_Model_Config::MESSAGE_REMOVED
                );
            $response = 'success';
        }

        echo Mage::helper('core')->jsonEncode($response);
        die;
    }

    /**
     * Ajax.
     */
    public function checkNotificationAction()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if ($this->getConfig()->getDesktopNotificationIsActive() && $user->getId()) {
            $messages = Mage::helper('helpdesk/desktopNotification')->getUnreadMeassagesForUser($user);

            $return = array(
                'messages' => $messages,
            );

            if ($messages) {
                $return['new_tickets_cnt'] = Mage::helper('helpdesk/desktopNotification')->getNewTicketsAmount();
                $return['new_messages_cnt'] = Mage::helper('helpdesk/desktopNotification')->getTicketMessagesAmount($user);
            }
        } else {
            $return = array(
                'messages' => array(),

            );
        }

        echo Mage::helper('core')->jsonEncode($return);
        die;
    }
}
