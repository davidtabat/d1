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



class Mirasvit_Helpdesk_Adminhtml_Helpdesk_ArchiveController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_redirect('*/helpdesk_ticket/index', array('is_archive' => 1));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdesk/archive');
    }
}
