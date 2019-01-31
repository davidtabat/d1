<?php
class Cmsmart_CalculateShipping_Model_Observer{

    /**
     * Return config object
     */
    public function getConfig(){
        return Mage::getSingleton('calculateshipping/system_config');
    }


    /**
     *  Add form to layout before load
     * @param Varien_Event_Observer $observer
     */
    public function controllerActionLayoutLoadBefore(Varien_Event_Observer $observer){

        $config = $this->getConfig();

        // All handles define in config.xml
        $handles = $config->getHandles();

        // Get current controller object
        $currentAction = $observer->getEvent()->getAction();

        // Get current full action name (handles)
        $currentActionName = $currentAction->getFullActionName();

        // Check extension enabled
        if($config->isEnabled()){

            if($currentActionName === $handles[0]) {

                if ($config->getPosition() === Cmsmart_CalculateShipping_Model_System_Config::DISPLAY_POSITION_LEFT) {

                    $currentAction->getLayout()->getUpdate()->addHandle(Cmsmart_CalculateShipping_Model_System_Config::LAYOUT_HANDLE_LEFT);

                } elseif ($config->getPosition() === Cmsmart_CalculateShipping_Model_System_Config::DISPLAY_POSITION_RIGHT) {

                    $currentAction->getLayout()->getUpdate()->addHandle(Cmsmart_CalculateShipping_Model_System_Config::LAYOUT_HANDLE_RIGHT);

                }else{

                    $currentAction->getLayout()->getUpdate()->addHandle(Cmsmart_CalculateShipping_Model_System_Config::LAYOUT_HANDLE_POPUP);

                }

            }else if($currentActionName === $handles[1] && $config->isApplyCategory()){

                $currentAction->getLayout()->getUpdate()->addHandle(Cmsmart_CalculateShipping_Model_System_Config::LAYOUT_HANDLE_CATEGORY);

            }
        }
    }
}