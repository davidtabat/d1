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
 * @package MDn_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select{

    /**
     * get options
     * 
     * @return array $retour 
     */
    public function _getOptions(){

        $retour = array();
        $tmp = mage::helper('MarketPlace/ProductCreation')->getStatus();
        
        $retour[] = array('label'=>'', 'value'=>'');
        foreach($tmp as $k => $v){
            
            $retour[] = array('label'=>$this->__($k), 'value'=>$v);

        }
        
        return $retour;

    }

    /**
     * get condition
     * 
     * @return array 
     */
    public function getCondition(){

        if($this->getValue() == 'notCreated'){
            return array(
                    'null' => 0
                );
        }
        else{
            return array(
                    'eq' => $this->getValue()
                    );
        }

    }

}
