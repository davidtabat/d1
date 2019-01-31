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



/**
 * #### ADDED BY MIRASVIT ####.
 *
 * @method Mirasvit_Helpdesk_Model_Ticket getTicket()
 * @method $this setTicket(Mirasvit_Helpdesk_Model_Ticket $param)

 * #### END ADDED BY MIRASVIT ####
 */
class Mirasvit_Helpdesk_Block_Email_Satisfaction extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'frontend');
    }

    public function getRateUrl($rate)
    {
        $message = $this->getTicket()->getLastMessage();

        return Mage::getUrl('helpdesk/satisfaction/rate', array('rate' => $rate, 'uid' => $message->getUid()));
    }

    public function _toHtml()
    {
        if (!Mage::getSingleton('helpdesk/config')->getSatisfactionIsActive()) {
            return '';
        }

        return parent::_toHtml();
    }
}
