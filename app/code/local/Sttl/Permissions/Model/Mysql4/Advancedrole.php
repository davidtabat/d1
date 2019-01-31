<?php
class Sttl_Permissions_Model_Mysql4_Advancedrole extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('permissions/advancedrole', 'advancedrole_id');
    }
}