<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Trustbadge extends Trustedshops_Trustedshops_Block_Abstract
{
    /**
     * path to layout file
     *
     * @var string
     */
    protected $_template = 'trustedshops/trustbadge.phtml';

    /**
     * get the selected trustbadge variant
     *
     * @return string
     */
    public function getVariant()
    {
        $isHidden = $this->getDisplayTrustbadge();
        if ($isHidden === "true") {
            return Trustedshops_Trustedshops_Model_System_Config_Source_Variant::VARIANT_REVIEWS;
        }
        return $this->getConfig('variant', 'trustbadge');
    }

    /**
     * get the y-offset
     * must be between 0 and 250
     *
     * @return int|number
     */
    public function getOffset()
    {
        $offset = abs($this->getConfig('offset', 'trustbadge'));
        return ($offset > 250) ? 250 : $offset;
    }

    /**
     * check if we should display the trustbadge
     *
     * @return string
     */
    public function getDisplayTrustbadge()
    {
        return ($this->getConfig('variant', 'trustbadge') == Trustedshops_Trustedshops_Model_System_Config_Source_Variant::VARIANT_HIDE)
            ? 'true'
            : 'false';
    }

    /**
     * get the expert trustbadge code
     * and replace the variables
     *
     * @return string
     */
    public function getCode()
    {
        $expertCode = $this->getConfig('code', 'trustbadge');
        return $this->replaceVariables($expertCode);
    }
}
