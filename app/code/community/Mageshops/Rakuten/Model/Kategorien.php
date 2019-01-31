<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_Kategorien extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options = null;


    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(array(
                    'label' => '',
                    'value' => 0,
            ));

            try {
                $kategorien = Mage::helper('rakuten')->getDefaultRakutenCategories();
                foreach ($kategorien as $kategorie) {
                    $this->_options[] = array(
                        'value' => $kategorie->getId(),
                        'label' => implode(' > ', $kategorie->getKategorien()),
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        return $this->_options;
    }
}
