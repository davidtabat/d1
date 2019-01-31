<?php

class Zyelon_Kaufberatung_Block_Adminhtml_Kaufberatung_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('kaufberatungGrid');
      $this->setDefaultSort('kaufberatung_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('kaufberatung/kaufberatung')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('kaufberatung_id', array(
          'header'    => Mage::helper('kaufberatung')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'kaufberatung_id',
      ));

      $this->addColumn('comments', array(
          'header'    => Mage::helper('kaufberatung')->__('Comments'),
          'align'     =>'left',
          'index'     => 'comments',
      ));
	  
	  $this->addColumn('name', array(
          'header'    => Mage::helper('kaufberatung')->__('Ihr Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
	  
	  $this->addColumn('company', array(
          'header'    => Mage::helper('kaufberatung')->__('Firma (optional)'),
          'align'     =>'left',
          'index'     => 'company',
      ));
	  
	  $this->addColumn('phone', array(
          'header'    => Mage::helper('kaufberatung')->__('Telefon'),
          'align'     =>'left',
          'index'     => 'phone',
      ));
	  
	  $this->addColumn('email', array(
          'header'    => Mage::helper('kaufberatung')->__('E-mail'),
          'align'     =>'left',
          'index'     => 'email',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('kaufberatung')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('kaufberatung')->__('Status'),
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
                'header'    =>  Mage::helper('kaufberatung')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('kaufberatung')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('kaufberatung')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('kaufberatung')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('kaufberatung_id');
        $this->getMassactionBlock()->setFormFieldName('kaufberatung');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('kaufberatung')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('kaufberatung')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('kaufberatung/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('kaufberatung')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('kaufberatung')->__('Status'),
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