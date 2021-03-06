<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Debug extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentDebug');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/tabs/debug.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->isMagentoDevelopmentModeEnabled = Mage::helper('M2ePro/Magento')->isDeveloper();
        $this->isDevelopmentModeEnabled        = Mage::helper('M2ePro/Module')->isDevelopmentMode();

        $this->commands = Mage::helper('M2ePro/View_Development_Command')
                    ->parseDebugCommandsData(Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_DEBUG);

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_development/enableDevelopmentMode/');
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Enable'),
            'onclick' => 'setLocation(\'' . $url . '\');',
            'class'   => 'enable_development_mode'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('enable_development_mode', $buttonBlock);

        $url = $this->getUrl('*/adminhtml_development/disableDevelopmentMode/');
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Disable'),
            'onclick' => 'setLocation(\'' . $url . '\');',
            'class'   => 'disable_development_mode'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('disable_development_mode', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
