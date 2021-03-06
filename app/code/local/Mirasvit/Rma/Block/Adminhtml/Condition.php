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
 * @package   RMA
 * @version   2.0.7
 * @build     1267
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Condition extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_condition';
        $this->_blockGroup = 'rma';
        $this->_headerText = Mage::helper('rma')->__('Conditions');
        $this->_addButtonLabel = Mage::helper('rma')->__('Add New Condition');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /************************/
}
