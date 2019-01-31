<?php

class Zyelon_Kaufberatung_Model_Mysql4_Kaufberatung_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('kaufberatung/kaufberatung');
    }
}