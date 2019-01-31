<?php

class MDN_MarketPlace_Block_Attributes_Switcher extends Mage_Adminhtml_Block_Abstract {
    
    public function getMarketPlaceSwitcher(){
        
        $currentMp = $this->getRequest()->getParam('mp');
        $html = '';
        
        $html .= '<select name="marketplace_switcher" id="marketplace_switcher" onchange=\'setLocation("'.$this->getUrl('MarketPlace/Attributes/Index', array()).'mp/"+this.value);\'>';
        
        $html .= '<option></option>';
        foreach(Mage::Helper('MarketPlace')->getHelpers() as $k => $v){
            
            $helper = Mage::Helper($v);
            $name = $helper->getMarketPlaceName();
            $selected = ($name == $currentMp) ? 'selected' : '';
            $html .= '<option '.$selected.' value="'.$name.'">'.$name.'</option>';
            
        }
        
        $html .= '</select>';
        
        return $html;
        
    }
    
}
