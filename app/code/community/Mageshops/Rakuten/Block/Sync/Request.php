<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Sync_Request extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $helper = Mage::helper('rakuten');

        $this->_controller = 'sync_request';
        $this->_blockGroup = 'rakuten';
        $this->_headerText = $helper->__('Requests to Rakuten.de');

        parent::__construct();
        $this->_removeButton('add');
    }
}
