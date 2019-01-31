<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Catalog_Product extends Mage_Adminhtml_Block_Catalog_Product
{
    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('rakuten/catalog_product_grid', 'product.grid'));
        $this->_removeButton('add_new');
        return Mage_Adminhtml_Block_Widget_Container::_prepareLayout();
    }
}
