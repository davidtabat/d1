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
 */

class MDN_Cdiscount_Block_Index_Tab_General extends Mage_Adminhtml_Block_Widget {

    public function __construct(){

        parent::__construct();
        $this->setHtmlId('general');
        $this->setTemplate('Cdiscount/Index/Tab/General.phtml');

    }

    protected function _prepareLayout()
    {

        $block = $this->getLayout()->createBlock('MarketPlace/Products');
        $block->setMp('cdiscount');
        $block->setTemplate('MarketPlace/Products.phtml');

        $this->setChild('marketplace_products',
                $block
        );

        $lastErrorsBlock = $this->getLayout()->createBlock('MarketPlace/Logs_Errors');
        $lastErrorsBlock->setMp('cdiscount');
        $lastErrorsBlock->setTemplate('MarketPlace/Logs/Errors.phtml');
        $this->setChild('marketplace_last_errors',$lastErrorsBlock);

    }

}
