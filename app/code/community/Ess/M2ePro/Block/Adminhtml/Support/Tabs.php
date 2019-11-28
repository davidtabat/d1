<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Support_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('support');
        $this->setDestElementId('support_tab_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        $isFromError = $this->getIsFromError();

        $this->addTab(
            'results', array(
            'label'     => Mage::helper('M2ePro')->__('Search Results'),
            'content'   => '',
            'active'    => !$isFromError,
            )
        );

        $params = array();

        if ($this->getRequest()->getParam('referrer') !== null) {
            $params['referrer'] = $this->getRequest()->getParam('referrer');
        }

        $this->addTab(
            'documentation', array(
            'label'     => Mage::helper('M2ePro')->__('Documentation'),
            'url'       => $this->getUrl('*/adminhtml_support/documentation', $params),
            'active'    => false,
            'class'     => 'ajax',
            )
        );

        $this->addTab(
            'articles', array(
            'label'     => Mage::helper('M2ePro')->__('Knowledge Base'),
            'url'       => $this->getUrl('*/adminhtml_support/knowledgeBase'),
            'active'    => false,
            'class'     => 'ajax',
            )
        );

        $this->addTab(
            'support_form', array(
            'label'     => Mage::helper('M2ePro')->__('Contact Support'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_support_contactForm')->toHtml(),
            'active'    => $isFromError,
            )
        );

        return parent::_prepareLayout();
    }

    //########################################
}
