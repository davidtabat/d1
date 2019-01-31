<?php

class MDN_MarketPlace_Block_Attributes_Index extends Mage_Adminhtml_Block_Widget_Form {    
    
    public function getAttributesBlock(){
        
        $mp = $this->getRequest()->getParam('mp');
        $html = '';
        
        if($mp != ''){
        
            $html =  $this->getLayout()->createBlock(ucfirst($mp).'/Attributes')->setTemplate(ucfirst($mp).'/Attributes.phtml')->toHtml();
            
        }
        
        return $html;
        
    }
    
}
