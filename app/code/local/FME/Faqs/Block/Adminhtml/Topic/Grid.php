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
 * 	       1- Order/position - 09-04-2012
 * 	       2- Show on main - 09-04-2012
 * 	       
 * @copyright  Copyright 2012 ? www.fmeextensions.com All right reserved
 */

class FME_Faqs_Block_Adminhtml_Topic_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('topicGrid');
      $this->setDefaultSort('topic_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('faqs/topic')->getCollection();
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('topic_id', array(
          'header'    => Mage::helper('faqs')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'topic_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('faqs')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
      
      $this->addColumn('identifier', array(
          'header'    => Mage::helper('faqs')->__('Identifier'),
          'align'     =>'left',
          'index'     => 'identifier',
      ));
      
      $this->addColumn('show_on_main', array(
          'header'    => Mage::helper('faqs')->__('Show on main page'),
          'align'     =>'left',
          'index'     => 'show_on_main',
	  'type'	=> 'options',
	  'options'	=> array(
		0 => 'No',
		1 => 'Yes',
	  ),
      ));
      
      $this->addColumn('topic_order', array(
          'header'    => Mage::helper('faqs')->__('Order / Position'),
          'align'     =>'left',
          'index'     => 'topic_order',
	  //'type'      => 'text',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('faqs')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('faqs')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('faqs')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('faqs')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('faqs')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('faqs_id');
        $this->getMassactionBlock()->setFormFieldName('faqs');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faqs')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('faqs')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('faqs/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('faqs')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('faqs')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}