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

class MDN_Cdiscount_Block_Cdiscount extends Mage_Adminhtml_Block_Widget_Form {
    
    /**
     * Get accounts as combo
     *
     * @return string
     */
    public function getCountriesAsCombo() {

        $html = '';        
        
        $accounts = Mage::getModel('MarketPlace/Accounts')->getActiveCountries(Mage::Helper('Cdiscount')->getMarketPlaceName());
                
        $html .= '<select name="" id="" onchange="switchCountry(this.value);">';

        foreach($accounts as $account){

            $html .= '<optgroup label="'.$account['label'].'">';

            foreach($account['countries'] as $k => $v){

                $selected = (Mage::getModel('MarketPlace/Countries')->getCurrentCountry($this->getRequest()->getParam('country_id'))->getId() == $k) ? 'selected' : '';

                $html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';

            }

            $html .= '</optgroup>';

        }        

        $html .= '</select>';   
        
        return $html;
        
    }
    
    
}
