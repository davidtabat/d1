<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Sync_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sync_request_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('rakuten/rakuten_request_collection');

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('rakuten');

        $this->addColumn('entity_id', array(
                'header' => $helper->__('ID'),
                'index'  => 'entity_id',
                'width'  => '20px',
                'align'  => 'center',
            ));

        $this->addColumn('element_id', array(
                'header' => $helper->__('Element ID'),
                'index'  => 'element_id',
            ));

        $this->addColumn('url', array(
                'header'       => $helper->__('URL'),
                'index'        => 'url',
            ));

        $this->addColumn('params', array(
                'header'       => $helper->__('Parameters'),
                'index'        => 'params',
                'renderer' => Mage::getBlockSingleton('rakuten/sync_request_grid_params'),

            ));

        $this->addColumn('started', array(
                'header' => $helper->__('Started'),
                'type'   => 'datetime',
                'index'  => 'started',
            ));

        $this->addColumn('finished', array(
                'header' => $helper->__('Finished'),
                'type'   => 'datetime',
                'index'  => 'finished',
            ));

        $this->addColumn('answer', array(
                'header' => $helper->__('Server Answer'),
                'index'  => 'answer',
                'renderer' => Mage::getBlockSingleton('rakuten/sync_request_grid_answer'),
            ));

        $this->addColumn('status', array(
                'header'  => $helper->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => Mage::getSingleton('rakuten/rakuten_request')->getStatuses(),
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('delete_request', array(
                'label'=> Mage::helper('rakuten')->__('Delete Records'),
                'url'  => $this->getUrl('*/*/massDeleteRequest'),
            ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
