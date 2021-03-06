<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Log extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonLog');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / Logs', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Logs');
        }

        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // ---------------------------------------
        $this->setTemplate('M2ePro/amazon/log/log.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        $css = <<<HTML

<style type="text/css">
    #listing_switcher_add_new_drop_down ul li {
        padding: 2px 5px 2px 10px !important;
    }
    #listing-profile-title_drop_down ul li {
        font-size: 12px !important;
    }
</style>

HTML;

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        CommonHandlerObj = new CommonHandler();
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        $activeTab = $this->getData('active_tab') !== null ? $this->getData('active_tab')
            : Ess_M2ePro_Block_Adminhtml_Amazon_Log_Tabs::TAB_ID_LISTING;
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_log_tabs', '', array('active_tab' => $activeTab)
        );

        return $css . $javascript .
            parent::_toHtml() .
            $tabsBlock->toHtml() .
            '<div id="tabs_container"></div>';
    }

    //########################################
}
