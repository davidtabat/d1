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



class Mirasvit_Helpdesk_Helper_Output extends Mage_Core_Helper_Abstract
{
    /**
     * @return string
     */
    public function getMessageAuthor($message)
    {
        $str = '';
        if ($message->getTriggeredBy() == Mirasvit_Helpdesk_Model_Config::CUSTOMER) {
            if ($message->getCustomerName() != '') {
                $str = $message->getCustomerName() . ', ';
            }
            $str .= $message->getCustomerEmail();
        }
        elseif ($message->getTriggeredBy() == Mirasvit_Helpdesk_Model_Config::USER) {
            $str = $message->getUserName();
            if ($message->isThirdParty()) {
                $str .= $this->__('to %s (third party)', $message->getThirdPartyEmail());
            }
        }
        elseif ($message->getTriggeredBy() == Mirasvit_Helpdesk_Model_Config::THIRD) {
            $str = $this->__('%s (third party)', $message->getThirdPartyName());
        }

        return $str;
    }
}