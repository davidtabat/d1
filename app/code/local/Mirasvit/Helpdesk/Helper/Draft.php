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



class Mirasvit_Helpdesk_Helper_Draft extends Mage_Core_Helper_Abstract
{
    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @return void
     */
    public function clearDraft($ticket)
    {
        $ticketId = $ticket->getId();
        $collection = Mage::getModel('helpdesk/draft')->getCollection();
        $collection->addFieldToFilter('ticket_id', $ticketId);
        foreach ($collection as $item) {
            $item->delete();
        }
    }

    /**
     * @param int $ticketId
     * @return bool|Mirasvit_Helpdesk_Model_Draft
     */
    public function getSavedDraft($ticketId)
    {
        $collection = Mage::getModel('helpdesk/draft')->getCollection()
                ->addFieldToFilter('ticket_id', $ticketId);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }

        return false;
    }

    /**
     * @param int $ticketId
     * @param int $userId
     * @param bool|string $text
     * @return Mirasvit_Helpdesk_Model_Draft
     * @throws Exception
     */
    public function getCurrentDraft($ticketId, $userId, $text = false)
    {
        $collection = Mage::getModel('helpdesk/draft')->getCollection()
                ->addFieldToFilter('ticket_id', $ticketId);
        if ($collection->count()) {
            $draft = $collection->getFirstItem();
        } else {
            $draft = Mage::getModel('helpdesk/draft');
            $draft->setTicketId($ticketId);
        }
        $usersOnline = $draft->getUsersOnline();
        $timeNow = Mage::getSingleton('core/date')->gmtTimestamp();
        $usersOnline[$userId] = $timeNow;
        foreach ($usersOnline as $uId => $timestamp) {
            if ($uId == $userId) {
                continue;
            }
            if ($timestamp + 20 < $timeNow) { //other user went offline from this page
                unset($usersOnline[$uId]);
                continue;
            }
        }
        $draft->setUsersOnline($usersOnline);
        if ($text !== false) {
            $draft->setBody($text);
            $draft->setUpdatedBy($userId);
            $draft->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $draft->save();

        return $draft;
    }

    /**
     * @param int $ticketId
     * @param int $userId
     * @param bool|string $text
     * @return string
     */
    public function getNoticeMessage($ticketId, $userId, $text = false)
    {
        $draft = $this->getCurrentDraft($ticketId, $userId, $text);
        $ids = $draft->getUsersOnline();
        unset($ids[$userId]);
        $ids = array_keys($ids);
        if (!count($ids)) {
            return '';
        }
        $users = Mage::getModel('admin/user')->getCollection()
                    ->addFieldToFilter('user_id', $ids);
        $userNames = array();
        foreach ($users as $user) {
            if ($draft->getUser()) {
                if ($user->getId() == $draft->getUser()->getId()) {
                    continue;
                }
            }
            $userNames[] = $user->getName();
        }

        $editNotice = '';
        $time = Mage::helper('helpdesk/string')->nicetime(strtotime($draft->getUpdatedAt()));
        if ($draft->getUser()) { // if somebody editing it now
            if ($userId != $draft->getUser()->getId()) {
                $editNotice = Mage::helper('helpdesk')->__(
                    '%s is editing now', $draft->getUser()->getName(), $time
                );
            }
        }
        if (count($userNames) == 1) {
            return $this->__('%s has opened this ticket %s', implode(', ', $userNames), '<br>'.$editNotice);
        } elseif (count($users) > 1) {
            return $this->__('%s have opened this ticket %s', implode(', ', $userNames), '<br>'.$editNotice);
        } else {
            return $editNotice;
        }
    }
}
