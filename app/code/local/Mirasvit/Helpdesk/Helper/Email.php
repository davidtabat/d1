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



class Mirasvit_Helpdesk_Helper_Email extends Varien_Object
{
    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Email $email
     *
     * @return bool|Mirasvit_Helpdesk_Model_Ticket
     */
    public function processEmail($email)
    {
        // $code = false;
        // if (Mage::getSingleton('helpdesk/config')->getNotificationIsShowCode()) {
            //get object by code from subject
        $code = Mage::helper('helpdesk/string')->getTicketCodeFromSubject($email->getSubject(), $this->getConfig()->getGeneralAcceptForeignTickets());
        // }

        if (!$code) {
            $code = Mage::helper('helpdesk/string')->getTicketCodeFromBody($email->getBody());
        }

        if (strpos($code, 'RMA') === 0 && $this->getConfig()->isActiveRma()) {
            return Mage::helper('rma/process')->processEmail($email, $code);
        } else {
            return Mage::helper('helpdesk/process')->processEmail($email, $code);
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $object
     * @param string                         $subject
     *
     * @return string
     */
    public function getEmailSubject($object, $subject = '')
    {
        $result = '';
        if (Mage::getSingleton('helpdesk/config')->getNotificationIsShowCode()) {
            $result = "[#{$object->getCode()}] ";
        }
        if ($subject) {
            $result .= "$subject - ";
        }
        $result .= $object->getName();

        return $result;
    }

    public function getHiddenCode($code)
    {
        return "<br><br><span style='color:#FFFFFF;font-size:5px;margin:0px;padding:0px;'>Message-Id:--#{$code}--</span>";
    }

    public function getSeparator()
    {
        return  '##- '.Mage::helper('helpdesk')->__('please type your reply above this line').' -##';
    }

    public function getHiddenSeparator()
    {
        return "<span style='color:#FFFFFF;font-size:5px;margin:0px;padding:0px;'>".$this->getSeparator().'</span>';
    }
}
