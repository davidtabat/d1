<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Category extends Mage_Adminhtml_Block_Template
{
    public function getRakuten()
    {
        return Mage::getModel('rakuten/category')->getRakuten();
    }

    public function getCategoryCollection()
    {
        return Mage::getModel('rakuten/category')->getCollection();
    }
}
