<?php

class Zyelon_Kaufberatung_Block_Adminhtml_Kaufberatung_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('kaufberatung_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('kaufberatung')->__('User Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('kaufberatung')->__('User Information'),
          'title'     => Mage::helper('kaufberatung')->__('User Information'),
          'content'   => $this->getLayout()->createBlock('kaufberatung/adminhtml_kaufberatung_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}