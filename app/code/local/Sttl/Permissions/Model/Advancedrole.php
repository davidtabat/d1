<?php
class Sttl_Permissions_Model_Advancedrole extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
		parent::_construct();
        $this->_init('permissions/advancedrole');
    }
}