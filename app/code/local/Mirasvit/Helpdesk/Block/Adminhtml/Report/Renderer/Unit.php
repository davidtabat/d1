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
 * @version   1.1.2
 * @build     1423
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Helpdesk_Block_Adminhtml_Report_Renderer_Unit
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    public function render(Varien_Object $row)
    {
    	$result = parent::render($row);
        if ($result == '') {
        	return '-';
    	} else {
    		return $this->__($this->getColumn()->getUnit(), $result);
    	}
    }
}