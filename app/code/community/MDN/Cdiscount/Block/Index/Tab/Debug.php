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
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Cdiscount_Block_Index_Tab_Debug extends MDN_MarketPlace_Block_Index_Tab_Debug {

    /**
     * Construct 
     */
    public function __construct(){

        parent::__construct();
        $this->setHtmlId('debug');
        $this->setTemplate('Cdiscount/Index/Tab/Debug.phtml');

    }
    
    /**
     * Getter mp
     *
     * @return string
     */
    public function getMp(){
        if($this->_mp === null)
                $this->_mp = Mage::Helper('Cdiscount')->getMarketPlaceName();

        return $this->_mp;
    }

}
