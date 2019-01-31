<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rakuten_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rakuten/rakuten_order')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('rakuten');

        $this->addColumn('order_no', array(
            'header'=> $helper->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'order_no',
        ));

        $this->addColumn('magento_increment_id', array(
            'header'=> $helper->__('Magento Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'magento_increment_id',
        ));

        $this->addColumn('created', array(
            'header' => $helper->__('Purchased On'),
            'index' => 'created',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('customer_name', array(
            'header'    => $helper->__('Customer Name'),
            'renderer'  => 'Mageshops_Rakuten_Block_Order_Grid_BillingName',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('email', array(
            'header' => $helper->__('Customer E-mail'),
            'index' => 'email',
            'type' => 'email',
            'width' => '100px',
        ));

        $this->addColumn('delivery_name', array(
            'header'    => $helper->__('Delivery Name'),
            'renderer'  => 'Mageshops_Rakuten_Block_Order_Grid_DeliveryName',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('total', array(
            'header' => $helper->__('Total'),
            'index' => 'total',
            'type'  => 'currency',
            'currency_code' => 'EUR',
        ));

        $this->addColumn('shipping', array(
            'header' => $helper->__('Shipping'),
            'index' => 'shipping',
            'type'  => 'currency',
            'currency_code' => 'EUR',
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'index' => 'status',
            'type'  => 'text',
            'width' => '70px',
        ));

        $this->addColumn('invoice_no', array(
            'header' => $helper->__('Invoice No'),
            'index' => 'invoice_no',
            'type'  => 'text',
            'width' => '70px',
        ));

//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('sync_action',
                array(
                    'header'    => $helper->__('Sync Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => $helper->__('Import to Magento'),
                            'url'     => array('base'=>'*/*/syncFromRakuten'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
//        }

//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('view_action',
                array(
                    'header'    => $helper->__('View Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => $helper->__('View Magento Order'),
                            'url'     => array('base'=>'*/*/viewSyncedOrder'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
//        }

//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('export_action',
                array(
                    'header'    => $helper->__('Export'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => $helper->__('Sync State to Rakuten'),
                            'url'     => array('base'=>'*/*/syncToRakuten'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
//        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rakuten_order_id');
        $this->getMassactionBlock()->setFormFieldName('rakuten_order_ids');

//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('sync_order', array(
                 'label'=> Mage::helper('rakuten')->__('Synchronize orders to magento'),
                 'url'  => $this->getUrl('*/*/massSyncFromRakuten'),
            ));
//        }

//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('export_order', array(
                 'label'=> Mage::helper('rakuten')->__('Export order status to Rakuten'),
                 'url'  => $this->getUrl('*/*/massSyncToRakuten'),
            ));
//        }

        return $this;
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
