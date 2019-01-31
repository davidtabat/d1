<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $helper = Mage::helper('rakuten');

        $this->_blockGroup = 'rakuten';
        $this->_controller = 'order';
        $this->_headerText = $helper->__('Rakuten Orders');
        $this->_addButtonLabel = $helper->__('Synchronize New Orders');
        parent::__construct();

        $this->_removeButton('add');

        $this->_addButton('update', array(
            'label' => $helper->__('Update orders list from Rakuten'),
            'onclick' => "setLocation('" . $this->getUrl('*/*/update') . "')",
            'class' => 'update'
        ));
    }
}
