<?php
/**
 * @category Hackathon
 * @package Hackathon_LocaleFallback
 * @author Bastian Ike <b-ike@b-ike.de>
 * @developer 
 * @version 0.1.0
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 */
class Hackathon_LocaleFallback_Model_System_Config_Source_Locale
{
    public function toOptionArray()
    {
        return array_merge(
            array(array('value' => '', 'label' => 'Disable')),
            Mage::app()->getLocale()->getOptionLocales()
        );
    }
}
