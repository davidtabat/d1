<?php
class Cmsmart_CalculateShipping_Model_Session extends Mage_Core_Model_Session_Abstract{

    const NAME_SPACE = 'calculateshipping';

    public function __construct(){
        $this->init(self::NAME_SPACE);
    }

}