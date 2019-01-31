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



class Mirasvit_Helpdesk_Model_Observer_DesktopNotification extends Varien_Object
{
    /**
     * @var bool
     */
    protected $isTicketCreated = false;

    /**
     * @var bool
     */
    protected $isNewMessageAndAssign = false;

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     * @return void
     */
    public function onMessageCreated($message)
    {
        if (!$this->isTicketCreated) {
            $ticket = $message->getTicket();
            if ($ticket->getUserId() != $ticket->getOrigData('user_id')) {
                $this->isNewMessageAndAssign = true;
                $this->createNotification($ticket, Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_ASSIGN);
            } else {
                $this->createNotification($message, Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_MESSAGE);
            }
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @return void
     */
    public function onTicketCreated($ticket)
    {
        $this->isTicketCreated = true;
        $this->createNotification($ticket, Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_TICKET);
    }


    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @return void
     */
    public function onTicketChanged($ticket)
    {
        if ($this->isNewMessageAndAssign) { //notified earlier
            return;
        }
        if ($ticket->getUserId() != $ticket->getOrigData('user_id')) {
            $this->createNotification($ticket, Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_ASSIGN);
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message|Mirasvit_Helpdesk_Model_Ticket $object
     * @param string $type
     * @throws Exception
     * @return void
     */
    protected function createNotification($object, $type)
    {
        //remove old messages about this ticket
        $collection = Mage::getModel('helpdesk/desktopNotification')->getCollection()
                ->addFieldToFilter('ticket_id', $object->getTicketId());
        foreach ($collection as $item) {
            $item->delete();
        }

        //add new message
        /** @var Mirasvit_Helpdesk_Model_DesktopNotification $notification */
        $notification = Mage::getModel('helpdesk/desktopNotification')
            ->setTicketId($object->getTicketId())
            ->setMessageId($object->getMessageId())
            ->setNotificationType($type)
        ;
        if ($user = Mage::getSingleton('admin/session')->getUser()) {
            $notification->addReadByUserId($user->getId());
        }
        $notification->save();
    }

    /**
     * Observes event core_block_abstract_to_html_after and add Helpdesk block to the header block.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function afterOutput($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $layout = $block->getLayout();

        $transport = $observer->getEvent()->getTransport();
        if (!isset($transport)) {
            return $this;
        }

        if (get_class($block) != 'Mage_Adminhtml_Block_Page_Header') {
            return $this;
        }

        $html = $transport->getHtml();
        if ($blockBegin = strpos($html, '<p class="super">')) {
            $blockBegin = strpos($html, '</span>', $blockBegin) + 7;
            $beginning = substr($html, 0, $blockBegin);
            $ending = substr($html, $blockBegin);

            $html = $layout->createBlock('helpdesk/adminhtml_notification_indicator')
                ->setTemplate('mst_helpdesk/notification/indicator.phtml')
                ->toHtml();

            $ourHtml = $beginning.$html.$ending;
            $transport->setHtml($ourHtml);
        }

        return $this;
    }
}