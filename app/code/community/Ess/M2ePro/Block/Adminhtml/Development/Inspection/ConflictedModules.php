<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_ConflictedModules
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentInspectionConflictedModules');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/inspection/conflictedModules.phtml');
    }

    //########################################

    protected function isShown()
    {
        return !empty(Mage::helper('M2ePro/Magento')->getConflictedModules());
    }

    //########################################
}
