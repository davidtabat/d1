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
 * @package   Advanced SEO Suite
 * @version   1.3.5
 * @build     1248
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Seo_Block_Adminhtml_CheckDuplicate_Check extends Mage_Adminhtml_Block_Widget_Grid_Container//Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'seo';
        $this->_controller = 'adminhtml_checkDuplicate_check';
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('back');
        $this->_removeButton('add');
    }

    public function getHeaderText()
    {
        return Mage::helper('adminhtml')->__('Check Duplicate Urls for "Remove Parent Category Path for Category URLs" option.');
    }
}