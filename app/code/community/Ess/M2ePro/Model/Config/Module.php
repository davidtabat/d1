<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Config_Module extends Ess_M2ePro_Model_Config_Abstract
{
    //########################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        $params['orm'] = 'M2ePro/Config_Module';

        parent::__construct($params);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Config_Module');
    }

    //########################################
}
