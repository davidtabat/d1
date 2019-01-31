<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Import_Tab_List_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('files_list_grid');
		/*$this->setDefaultSort('pos');*/
		$this->setSaveParametersInSession(false);
		//$this->setVarNameFilter('filter_orders');
		$this->setUseAjax(true);
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/gridListFiles', array('_current'=>true));
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('amfile/ftpFile')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}


	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_File_Helper_Data */
		$_helper = Mage::helper('amfile');

		/*$this->addColumn('code_set_id', array(
			'header'    => $_helper->__('ID'),
			'align'     => 'right',
			'width'     => '50px',
			'index'     => 'code_set_id',
		));*/

		$this->addColumn('file_name', array(
			'header'    => $_helper->__('File Name'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'name',
		));

		if($this->_isExport) {
			$this->addColumn('product_sku', array(
				'header'    => $_helper->__('Product SKU'),
			));

			$this->addColumn('file_title', array(
				'header'    => $_helper->__('File Title'),
			));

			$this->addColumn('sort_order', array(
				'header'    => $_helper->__('Sort Order'),
			));
		}

		if(!$this->_isExport) {
			$this->addColumn(
				'action', array(
					'header'   => $_helper->__('Action'),
					'width'    => '50px',
					'type'     => 'action',
					'getter'   => 'getName',
					//'isSystem'	=> true,
					'actions'  => array(
						array(
							'caption' => $_helper->__('Delete'),
							'url'     => array(
								'base' => '*/*/deleteFile',
							),
							'field'   => 'name',
							'confirm' => $_helper->__(
								'Are you sure?'
							),
						)
					),
					'filter'   => false,
					'sortable' => false,
					//'index'     => 'stores',
				)
			);
			//$this->getColumn('action')->setFrameCallback(array($this, 'renderAction'));
		}

		$this->addExportType('*/*/exportFilesCsv', $_helper->__('CSV'));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return "";
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('name');
		$this->getMassactionBlock()->setFormFieldName('file_names');

		$actions = array(
			'massDeleteFiles'     => 'Delete',
		);
		foreach ($actions as $code => $label){
			$this->getMassactionBlock()->addItem($code, array(
				'label'    => Mage::helper('amfile')->__($label),
				'url'      => $this->getUrl('*/*/' . $code),
				'confirm'  => ($code == 'massDeleteFiles' ? Mage::helper('amfile')->__('Are you sure?') : null),
			));
		}
		return $this;
	}
}