<?php
/**
 * Advance FAQ Management Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Advance FAQ Management
 * @author     Kamran Rafiq Malik <support@fmeextensions.com>
 *             
 * 	       Asif Hussain <support@fmeextensions.com>
 * 	       
 * @copyright  Copyright 2012 ? www.fmeextensions.com All right reserved
 */

class FME_Faqs_Block_Adminhtml_Faqs_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('faqs_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('faqs')->__('Faqs Management'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('faqs')->__('Faq Information'),
          'title'     => Mage::helper('faqs')->__('Faq Information'),
          'content'   => $this->getLayout()->createBlock('faqs/adminhtml_faqs_edit_tab_form')->toHtml(),
          'active'    => true
      ));
	  
      return parent::_beforeToHtml();
  }
}