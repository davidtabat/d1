<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_Cdiscount
 * @version 2.0
 */
class MDN_Cdiscount_Block_Index_Tab_ProductsToAdd extends Mage_Adminhtml_Block_Widget {
    
    /**
     * Construct 
     */
    public function __construct(){
        parent::__construct();
        $this->setHtmlId('products_to_add');
        $this->setTemplate('Cdiscount/Index/Tab/ProductsToAdd.phtml');
    }
    
    /**
     * Prepare Layout 
     */
    protected function _prepareLayout()
    {

        // product grid
        $block = $this->getLayout()->createBlock('Cdiscount/Grids_ProductsToAdd');
        $block->setTemplate('MarketPlace/Products.phtml');

        $this->setChild('products_to_add_grid',$block);

    }
    
}

