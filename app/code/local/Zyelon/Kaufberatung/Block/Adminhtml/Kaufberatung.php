<?php
class Zyelon_Kaufberatung_Block_Adminhtml_Kaufberatung extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_kaufberatung';
    $this->_blockGroup = 'kaufberatung';
    $this->_headerText = Mage::helper('kaufberatung')->__('User Guide Manager');
    $this->_addButtonLabel = Mage::helper('kaufberatung')->__('Add User');
    parent::__construct();
  }
}