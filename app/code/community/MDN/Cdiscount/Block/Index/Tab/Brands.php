<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_Laredoute
 */

class MDN_Cdiscount_Block_Index_Tab_Brands extends Mage_Adminhtml_Block_Widget {

    /**
     * Construct 
     */
     public function __construct(){

        parent::__construct();
        $this->setHtmlId('brands');
        $this->setTemplate('Cdiscount/Index/Tab/Brands.phtml');

    }

    /**
     * Prepare layout 
     */
    protected function _prepareLayout()
    {

        // brands grid
        $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_Brands_Grid');
        $block->setTemplate('Cdiscount/Index/Tab/Brands/Grid.phtml');

        $this->setChild('brands',
                $block
        );
        
        // add brand form
        $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_Brands_Add');
        $block->setTemplate('Cdiscount/Index/Tab/Brands/Add.phtml');
        
        $this->setChild('add_brand_form',$block);

    }

}
