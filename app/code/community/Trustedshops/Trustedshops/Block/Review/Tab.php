<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Review_Tab extends Trustedshops_Trustedshops_Block_Review
{
    /**
     * path to layout file
     *
     * @var string
     */
    protected $_template = 'trustedshops/review_tab.phtml';

    /**
     * only add the additional product tab if reviews are active
     *
     * @return bool|string
     */
    protected function _toHtml()
    {
        if (!$this->isActive()) {
            return false;
        }
        return parent::_toHtml();
    }
}
