<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.2.4
 * @build     2266
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_Satisfaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid');
        $this->setDefaultSort('satisfaction_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdesk/satisfaction')
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('satisfaction_id', array(
            'header' => Mage::helper('helpdesk')->__('ID'),
            'index' => 'satisfaction_id',
            'filter_index' => 'main_table.satisfaction_id',
            )
        );
        $this->addColumn('user_id', array(
            'header' => Mage::helper('helpdesk')->__('User'),
            'index' => 'user_id',
            'filter_index' => 'main_table.user_id',
            'type' => 'options',
            'options' => Mage::helper('helpdesk')->getAdminUserOptionArray(),
            )
        );
        $this->addColumn('customer_name', array(
            'header' => Mage::helper('helpdesk')->__('Customer'),
            'index' => 'customer_name',
            'filter_index' => 'ticket.customer_name',
            )
        );
        $this->addColumn('rate', array(
            'header' => Mage::helper('helpdesk')->__('Rate'),
            'index' => 'rate',
            'filter_index' => 'main_table.rate',
            'type' => 'options',
            'options' => Mage::getSingleton('helpdesk/config_source_rate')->toArray(),
            )
        );
        $this->addColumn('comment', array(
            'header' => Mage::helper('helpdesk')->__('Comment'),
            'index' => 'comment',
            'filter_index' => 'main_table.comment',
            )
        );
        $this->addColumn('created_at', array(
            'header' => Mage::helper('helpdesk')->__('Created At'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            )
        );

        $this->addColumn('action',
            array(
                // 'header'    =>  Mage::helper('customer')->__('View Rated Message'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getTicketId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('customer')->__('View Ticket'),
                        'url' => array('base' => '*/helpdesk_ticket/edit'),
                        'field' => 'id',
                        'target' => '_blank',
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('satisfaction_id');
        $this->getMassactionBlock()->setFormFieldName('satisfaction_id');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('helpdesk')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('helpdesk')->__('Are you sure?'),
        ));

        return $this;
    }

    /************************/
}
