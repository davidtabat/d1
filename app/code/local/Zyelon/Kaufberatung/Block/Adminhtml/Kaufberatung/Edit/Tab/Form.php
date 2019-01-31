<?php

class Zyelon_Kaufberatung_Block_Adminhtml_Kaufberatung_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('kaufberatung_form', array('legend'=>Mage::helper('kaufberatung')->__('User information')));
     
	  
	  $fieldset->addField('color', 'radios', array(
          'label'     => Mage::helper('kaufberatung')->__('Möchten Sie nur Schwarz/Weiß oder auch in Farbe drucken?'),
          'name'      => 'color',
          'onclick' => "",
          'onchange' => "",
          'value'  => '2',
          'values' => array(
                            array('value'=>'Schwarz / Weiß','label'=>'Schwarz / Weiß'),
                            array('value'=>'Farbe und S/W','label'=>'Farbe und S/W'),
                       ),
          'disabled' => false,
          'readonly' => false,
          'tabindex' => 1
        ));
	  
	  $fieldset->addField('print', 'checkboxes', array(
          'label'     => Mage::helper('kaufberatung')->__('Welche Formate möchten Sie drucken?'),
          'name'      => 'print[]',
          'values' => array(
                            array('value'=>'A4','label'=>'A4'),
                            array('value'=>'A3','label'=>'A3'),
                            array('value'=>'Etiketten','label'=>'Etiketten'),
							array('value'=>'Broschüren','label'=>'Broschüren'),
                       ),
          'onclick' => "",
          'onchange' => "",
          'value'  => '1',
          'disabled' => false,
          'tabindex' => 1
        )); 
	  
	  /* $fieldset->addField('print', 'checkboxes', array(
                'label' => $this->__('Welche Formate möchten Sie drucken?'),
                'name' => 'print[]',
                'required' => true,
                "checked" => $city,
                'values' => array(
                    array('value' => '0', 'label' => 'aaaaa'),
                    array('value' => '1', 'label' => 'bbbbbbb'),
                    array('value' => '2', 'label' => 'ccccccc'),
                    array('value' => '3', 'label' => 'dddddddd'),
                    array('value' => '4', 'label' => 'eeeeeeee')
                ),
                'onclick' => "",
                'onchange' => "",
                'disabled' => false,
                'value'  => '1',
                'tabindex' => 1
            )); 
			$city = $post_data['print'] = implode(',', $post_data['print']); */
			
	  $fieldset->addField('other_print', 'text', array(
          'label'     => Mage::helper('kaufberatung')->__('Other Print'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'other_print',
      ));
	  
	  $fieldset->addField('features', 'checkboxes', array(
          'label'     => Mage::helper('kaufberatung')->__('Welche Funktionen soll der Kopierer haben?'),
          'name'      => 'features[]',
          'values' => array(
                            array('value'=>'Kopieren','label'=>'Kopieren'),
                            array('value'=>'Drucken','label'=>'Drucken'),
                            array('value'=>'Faxen','label'=>'Faxen'),
							array('value'=>'Scannen','label'=>'Scannen'),
							array('value'=>'Duplex-Druck/-Scan (Beidseitig)','label'=>'Duplex-Druck/-Scan (Beidseitig)'),
							array('value'=>'Lochen und heften (Finisher)','label'=>'Lochen und heften (Finisher)'),
							array('value'=>'Stapeln und sortieren (Sorter)','label'=>'Stapeln und sortieren (Sorter)'),
                       ),
          'onclick' => "",
          'onchange' => "",
          'value'  => '1',
          'disabled' => false,
          'tabindex' => 1
        ));
	  
	  $fieldset->addField('paper_trays', 'radios', array(
          'label'     => Mage::helper('kaufberatung')->__('Wie viele Papierfächer benötigen Sie?'),
          'name'      => 'paper_trays',
          'onclick' => "",
          'onchange' => "",
          'value'  => '2',
          'values' => array(
                            array('value'=>'2','label'=>'2'),
                            array('value'=>'4','label'=>'4'),
							array('value'=>'mehr','label'=>'mehr'),
							array('value'=>'weiß ich nicht','label'=>'weiß ich nicht'),
                       ),
          'disabled' => false,
          'readonly' => false,
          'tabindex' => 1
        ));
	  
	  $fieldset->addField('connect', 'radios', array(
          'label'     => Mage::helper('kaufberatung')->__('Wie möchten Sie den Kopierer anschließen?'),
          'name'      => 'connect',
          'onclick' => "",
          'onchange' => "",
          'value'  => '2',
          'values' => array(
                            array('value'=>'Netzwerk','label'=>'Netzwerk'),
                            array('value'=>'Lokal','label'=>'Lokal'),
							array('value'=>'Standalone','label'=>'Standalone'),
							array('value'=>'weiß ich nicht','label'=>'weiß ich nicht'),
                       ),
          'disabled' => false,
          'readonly' => false,
          'tabindex' => 1
        ));
	  
	  $fieldset->addField('volumes', 'radios', array(
          'label'     => Mage::helper('kaufberatung')->__('Wie hoch ist Ihr monatliches Kopiervolumen?'),
          'name'      => 'volumes',
          'onclick' => "",
          'onchange' => "",
          'value'  => '2',
          'values' => array(
                            array('value'=>'bis zu 2.500','label'=>'bis zu 2.500'),
                            array('value'=>'2.500 - 5.000','label'=>'2.500 - 5.000'),
							array('value'=>'5.000 - 10.000','label'=>'5.000 - 10.000'),
							array('value'=>'10.000 - 25.000','label'=>'10.000 - 25.000'),
							array('value'=>'mehr als 25.000','label'=>'mehr als 25.000'),
                       ),
          'disabled' => false,
          'readonly' => false,
          'tabindex' => 1
        ));
	  
	  $fieldset->addField('desktop', 'radios', array(
          'label'     => Mage::helper('kaufberatung')->__('Bevorzugen Sie ein Tischgerät oder lieber einen Standkopierer?'),
          'name'      => 'desktop',
          'onclick' => "",
          'onchange' => "",
          'value'  => '2',
          'values' => array(
                            array('value'=>'Tischgerät','label'=>'Tischgerät'),
                            array('value'=>'Standkopierer','label'=>'Standkopierer'),
							array('value'=>'weiß ich nicht','label'=>'weiß ich nicht'),
                       ),
          'disabled' => false,
          'readonly' => false,
          'tabindex' => 1
        ));
	  
	  /* $fieldset->addField('budget', 'text', array(
          'label'     => Mage::helper('kaufberatung')->__('Budget'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'budget',
      )); */
	  
	  $fieldset->addField('budget', 'radios', array(
          'label'     => Mage::helper('kaufberatung')->__('Ihr Budget in Euro'),
          'name'      => 'budget',
          'onclick' => "",
          'onchange' => "",
          'value'  => '2',
          'values' => array(
                            array('value'=>'Budget ca.','label'=>'Budget ca.'),
                            array('value'=>'Keine Budgetvorgaben','label'=>'Keine Budgetvorgaben'),
                       ),
          'disabled' => false,
          'readonly' => false,
          'tabindex' => 1
        ));
	  
	  $fieldset->addField('Salutation', 'text', array(
          'label'     => Mage::helper('kaufberatung')->__('Anrede'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'Salutation',
      ));
	  
	  $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('kaufberatung')->__('Ihr Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));
	  
	  $fieldset->addField('company', 'text', array(
          'label'     => Mage::helper('kaufberatung')->__('Firma (optional)'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'company',
      ));
	  
	  $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('kaufberatung')->__('Telefon'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'phone',
      ));
	  
	  $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('kaufberatung')->__('E-Mail-Adresse'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'email',
      ));

      /* $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('kaufberatung')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));  */
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('kaufberatung')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('kaufberatung')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('kaufberatung')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('comments', 'editor', array(
          'name'      => 'comments',
          'label'     => Mage::helper('kaufberatung')->__('Comments'),
          'title'     => Mage::helper('kaufberatung')->__('Comments'),
          'style'     => 'width:275px; height:100px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getKaufberatungData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getKaufberatungData());
          Mage::getSingleton('adminhtml/session')->setKaufberatungData(null);
      } elseif ( Mage::registry('kaufberatung_data') ) {
          $form->setValues(Mage::registry('kaufberatung_data')->getData());
      }
      return parent::_prepareForm();
  }
}