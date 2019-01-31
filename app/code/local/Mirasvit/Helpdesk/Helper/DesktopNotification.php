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



class Mirasvit_Helpdesk_Helper_DesktopNotification
{
    /**
     * @return Mirasvit_Helpdesk_Model_Config
     */
    protected function getConfig() {
        return Mage::getSingleton('helpdesk/config');
    }

    /**
     * @return int
     */
    public function getNewTicketsAmount()
    {
        $resource = Mage::getModel('helpdesk/ticket')->getResource();
        $db = $resource->getReadConnection();
        $sql = 'SELECT COUNT(*) as cnt FROM '.$resource->getTable('helpdesk/ticket').'
                WHERE reply_cnt=1 AND is_archived=0  AND is_spam=0 AND user_id = 0';
        $result = $db->fetchOne($sql);

        return $result;
    }


    /**
     * @param Mage_Admin_Model_User $user
     * @return int
     */
    public function getTicketMessagesAmount($user)
    {
        $resource = Mage::getModel('helpdesk/ticket')->getResource();
        $db = $resource->getReadConnection();
        $sql = 'SELECT COUNT(*) as cnt
        FROM '.$resource->getTable('helpdesk/ticket').' AS `main_table`
        WHERE main_table.user_id = '.$user->getId().'
        AND main_table.is_archived = 0 AND main_table.is_spam = 0';

        $result = $db->fetchOne($sql);
        return $result;
    }


    /**
     * @param Mage_Admin_Model_User $user
     * @return object
     */
    protected function getMessagesCollection($user) {
        $resource = Mage::getModel('helpdesk/ticket')->getResource();
        $collection = Mage::getModel('helpdesk/desktopNotification')->getCollection();
        $select = $collection->getSelect();
        $select->joinInner(
            array('ticket' => $resource->getTable('helpdesk/ticket')),
            'main_table.ticket_id = ticket.ticket_id'
        );


        if (!$permission = Mage::helper('helpdesk/permission')->getPermission()) {
            $select->where('ticket.department_id', -1);
        }
        $departmentIds = $permission->getDepartmentIds();

        if (!in_array(0, $departmentIds)) {
            $select->where('ticket.department_id IN ('.implode(',', $departmentIds).')');
        }

        // add 1 hour to current gmt date
        $date = Mage::getSingleton('core/date')->gmtDate();
        $date = strtotime($date.'+ 1 hour');
        $date = date('Y-m-d H:i:s', $date);

        $select->where('main_table.created_at < ?', $date);

        $select->where(
            'read_by_user_ids NOT LIKE "%,'.$user->getId().',%" '. //user has not read this notification before
            'AND (
                    (
                    notification_type = "'.Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_MESSAGE.'"
                    AND (ticket.user_id = '.$user->getId().' ) '. //notification is about new message of ticket of this user
            ') '.
            //or notification about something else
            'OR
                    notification_type <> "'.Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_MESSAGE.'"'.
            ')'

        );
        return $collection;
    }

    /**
     * @param Mage_Admin_Model_User $user
     * @return array
     * @throws Exception
     */
    public function getUnreadMeassagesForUser($user)
    {
        $collection = $this->getMessagesCollection($user);
        $messages = array();
        /** @var Mirasvit_Helpdesk_Model_DesktopNotification $notification */
        foreach ($collection as $notification) {
            $message = array(
                'ticket_id' => $notification->getTicketId(),
            );
            $message['url'] = Mage::helper('adminhtml')->getUrl('adminhtml/helpdesk_ticket/edit', array(
                'id' => $notification->getTicketId(),
                'is_archive' => $notification->getIsArchived()
            ));
            $ticket = $notification->getTicket();
            $store = $ticket->getStore();
            $message['title'] = Mage::helper('helpdesk')->__('%s Help Desk', $store->getName());

            switch ($notification->getNotificationType()) {
                case Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_TICKET:
                    $userId = $notification->getTicket()->getUserId();
                    //is allowed
                    if (in_array(Mirasvit_Helpdesk_Model_Config_Source_Notification_Users::ALL_USERS,
                            $this->getConfig()->getDesktopNotificationAboutTicketUserIds()) || //allowed for all
                        in_array($user->getId(), $this->getConfig()->getDesktopNotificationAboutTicketUserIds())) {

                        if ($userId == 0 || $userId == $user->getId()) { //ticket is not assigned yet
                            $message['message'] = Mage::helper('helpdesk')
                                ->__('Ticket "%s" was created.', $notification->getName());
                        }
                    }
                    break;
                case Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_MESSAGE:
                    if ($this->getConfig()->getDesktopNotificationAboutMessage()) {
                        $message['message'] = Mage::helper('helpdesk')
                            ->__('New message was added to the ticket "%s"', $notification->getName());
                    }
                    break;
                case Mirasvit_Helpdesk_Model_Config::NOTIFICATION_TYPE_NEW_ASSIGN:
                    $userId = $notification->getTicket()->getUserId();
                    if ($this->getConfig()->getDesktopNotificationAboutAssign() && $userId == $user->getId()) {
                            $message['message'] = Mage::helper('helpdesk')
                                ->__('Ticket "%s" was assigned to you.', $notification->getName());
                    }
                    break;
            }

            $notification->addReadByUserId($user->getId());
            $notification->save();

            if (!empty($message['message'])) {
                $messages[] = $message;
            }
        }

        if (($count = count($messages)) > 3) {
            $messages = array_slice($messages, -3);
            $messages[0]['message'] = Mage::helper('helpdesk')
                ->__('You have %s unread notifications.', $count);
        }

        return $messages;
    }
}