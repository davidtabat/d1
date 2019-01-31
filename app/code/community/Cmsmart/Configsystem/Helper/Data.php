<?php
class Cmsmart_Configsystem_Helper_Data extends Mage_Core_Helper_Abstract{

    /**
     *  Function check extension is turn on/off
     * @return bool
     */
    public function isEnabled(){
        return Mage::getStoreConfigFlag('configsystem/general/enabled');
    }


}