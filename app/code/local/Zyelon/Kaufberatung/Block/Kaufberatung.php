<?php
class Zyelon_Kaufberatung_Block_Kaufberatung extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getKaufberatung()     
     { 
        if (!$this->hasData('kaufberatung')) {
            $this->setData('kaufberatung', Mage::registry('kaufberatung'));
        }
        return $this->getData('kaufberatung');
        
    }
}