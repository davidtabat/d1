<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Flags
 */
class Amasty_Flags_Block_Rewrite_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _toHtml()
    {
        $html  = parent::_toHtml();
        $html .= Mage::app()->getLayout()->createBlock('amflags/rewrite_adminhtml_order_grid_modifyJs')->toHtml();
        return $html;
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        $columnCollection = Mage::getModel('amflags/column')->getCollection();
        $columnCollection->getSelect()->order('pos DESC');
        $flagCollection = Mage::getModel('amflags/flag')->getCollection();
        
        if ($columnCollection->getSize() > 0)
        {
            foreach ($columnCollection as $column)
            {
                if (($column->getApplyFlag()) && ($flagCollection->getSize() > 0))
                {
                    $flagFilterOptions = array();
                    $columnFlags = array();
                    $columnFlags = explode(',', $column->getApplyFlag());
                    
                    foreach ($flagCollection as $flag)
                    {
                        if (in_array($flag->getEntityId(), $columnFlags))
                        {
                            $flagFilterOptions[$flag->getPriority()] = $flag->getAlias();
                        }
                    }
                    
                    $flagColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
                        ->setData(array(
                                        'header'   => Mage::helper('amflags')->__($column->getAlias()),
                                        'index'    => 'priority'.$column->getEntityId(),
                                        'filter_index' => 'f'.$column->getEntityId().'.priority',
                                        'width'    => '80px',
                                        'align'    => 'center',
                                        'renderer' => 'amflags/adminhtml_renderer_flag',
                                        'type'     => 'options',
                                        'options'  => $flagFilterOptions,
                                        )
                                 )
                        ->setGrid($this)
                        ->setId('flag_column_id'.$column->getEntityId());
                        
                    // adding flag column to the beginning of the columns array
                    $flagColumnArray = array('flag_column_id'.$column->getEntityId() => $flagColumn);
                    $this->_columns = $flagColumnArray + $this->_columns;
                }
            }
                
            $this->setDefaultSort('created_at');
            $this->setDefaultDir('DESC');
            $this->sortColumnsByOrder();
        }
        
        return $this;
    }
}