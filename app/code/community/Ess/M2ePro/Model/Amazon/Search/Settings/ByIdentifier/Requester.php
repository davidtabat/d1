<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Settings_ByIdentifier_Requester
    extends Ess_M2ePro_Model_Amazon_Connector_Search_ByIdentifier_ItemsRequester
{
    // ########################################

    protected function getResponserRunnerModelName()
    {
        return 'Amazon_Search_Settings_ProcessingRunner';
    }

    protected function getResponserParams()
    {
        return array_merge(
            parent::getResponserParams(),
            array('type' => $this->getQueryType(), 'value' => $this->getQuery())
        );
    }

    // ########################################

    protected function getQuery()
    {
        return $this->_params['query'];
    }

    protected function getQueryType()
    {
        return $this->_params['query_type'];
    }

    protected function getVariationBadParentModifyChildToSimple()
    {
        return $this->_params['variation_bad_parent_modify_child_to_simple'];
    }

    // ########################################
}