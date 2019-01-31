<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Review extends Trustedshops_Trustedshops_Block_Abstract
{
    /**
     * path to layout file
     *
     * @var string
     */
    protected $_template = "trustedshops/review.phtml";

    /**
     * check if reviews are active
     *
     * @return bool
     */
    public function isActive()
    {
        if (!parent::isActive()) {
            return false;
        }

        if ($this->isExpert()) {
            if (!$this->getConfig('collect_orders', 'trustbadge')) {
                return false;
            }
            if (!$this->getConfig('expert_collect_reviews', 'product')) {
                return false;
            }
            return $this->getConfig('expert_review_active', 'product');
        }

        if (!$this->getConfig('collect_reviews', 'product')) {
            return false;
        }
        return $this->getConfig('review_active', 'product');
    }

    /**
     * get the border color
     *
     * @return string
     */
    public function getBorderColor()
    {
        return $this->getConfig('review_border_color', 'product');
    }

    /**
     * get the star color
     *
     * @return string
     */
    public function getStarColor()
    {
        return $this->getConfig('review_star_color', 'product');
    }

    /**
     * get the expert reviews code
     * and replace variables
     *
     * @return string
     */
    public function getCode()
    {
        $expertCode = $this->getConfig('review_code', 'product');
        return $this->replaceVariables($expertCode);
    }
}
