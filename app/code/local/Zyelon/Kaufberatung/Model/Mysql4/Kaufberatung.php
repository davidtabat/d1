<?php

class Zyelon_Kaufberatung_Model_Mysql4_Kaufberatung extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the kaufberatung_id refers to the key field in your database table.
        $this->_init('kaufberatung/kaufberatung', 'kaufberatung_id');
    }
}