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
 *             1- Created - 10-10-2010
 *
 * 	       Asif Hussain <support@fmeextensions.com>
 * 	       1 - Order/position - 09-04-2012
 * 	       2 - Show on main page - 09-04-2012
 * 	       3 - Open in Accordion - 09-04-2012
 * 	       4 - wysiwyg, add image - 09-04-2012
 * 	       
 * @copyright  Copyright 2012 © www.fmeextensions.com All right reserved
 */

class FME_Faqs_Block_Adminhtml_Faqs_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
	$form = new Varien_Data_Form();
	$this->setForm($form);
	$fieldset = $form->addFieldset('faqs_form', array('legend'=>Mage::helper('faqs')->__('Faq information')));
      
	$resource = Mage::getSingleton('core/resource');
	$read= $resource->getConnection('core_read');
	$topicTable = $resource->getTableName('faqs_topics');
	
	$select = $read->select()
	->from($topicTable,array('topic_id as value','title as label'))
	->order('topic_id ASC') ;
	$topics = $read->fetchAll($select);
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('faqs')->__('Question'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
    
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('faqs')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('faqs')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('faqs')->__('Disabled'),
              ),
          ),
      ));
	    
     $fieldset->addField('topic_id', 'select', array(
          'label'     => Mage::helper('faqs')->__('Add in Topic'),
          'name'      => 'topic_id',
          'values'    => $topics,
      ));
     
     
     
     $fieldset->addField('show_on_main', 'select', array(
          'label'     => Mage::helper('faqs')->__('Show on main page'),
          'name'      => 'show_on_main',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('faqs')->__('Yes'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('faqs')->__('No'),
              ),
          ),
	  'after_element_html'	=> '<p class="note">show on main page under category</p>',
      ));
     
     
     $fieldset->addField('faq_order', 'text', array(
	    'label'	=> Mage::helper('faqs')->__('Order / Position'),
	    'name'	=> 'faq_order',
	    'class'	=> 'validate-number',
	    'after_element_html'	=> '<p class="note">order / postion of faq (0 for first)</p>',      
      ));
     
     
     
     $fieldset->addField('accordion_opened', 'select', array(
	    'label'	=> Mage::helper('faqs')->__('Open In Accordion'),
	    'name'	=> 'accordion_opened',
	    'values'    => array(
		array(
		    'value'     => 1,
		    'label'     => Mage::helper('faqs')->__('Yes'),
		),
  
		array(
		    'value'     => 0,
		    'label'     => Mage::helper('faqs')->__('No'),
		),
	    ),
	    'after_element_html'	=> '<p class="note">open by default in Accordion, when accordian is enabled from configuration.</p>',      
      ));
     
       
	  try{
			$config = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
				array(
						'add_widgets' => false,
						'add_variables' => false,
						'files_browser_window_url'=> $this->getBaseUrl().'admin/cms_wysiwyg_images/index/',
				      )
				);
			
		}
		catch (Exception $ex){
			$config = null;
		}
	  
	  $fieldset->addField('faq_answar', 'editor', array(
		  'name'      => 'faq_answar',
		  'label'     => Mage::helper('faqs')->__('Answer'),
		  'title'     => Mage::helper('faqs')->__('Answer'),
		  'style'     => 'width:500px; height:300px;',
		  'wysiwyg'   => true,
		  'config'    => $config	  
		));
     
      if ( Mage::getSingleton('adminhtml/session')->getFaqsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFaqsData());
          Mage::getSingleton('adminhtml/session')->setFaqsData(null);
      } elseif ( Mage::registry('faqs_data') ) {
          $form->setValues(Mage::registry('faqs_data')->getData());
      }
      return parent::_prepareForm();
  }
}