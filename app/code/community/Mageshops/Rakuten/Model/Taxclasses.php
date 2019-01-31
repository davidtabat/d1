<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_Taxclasses
{
    protected $_classes = null;

    public function toOptionArray($isMultiselect = false)
    {
        if (is_null($this->_classes)) {
            $this->_classes = array(array(
                    'label' => 'Please, select corresponding tax class',
                    'value' => 0,
            ));

            $taxes = Mage::getModel('tax/class')->getCollection();
            foreach ($taxes as $tax) {
                $this->_classes[] = array(
                    'value' => $tax->getId(),
                    'label' => $tax->getClassName(),
                );
            }
        }
        return $this->_classes;
    }
}
