<?php
/**
 * @category    Mageshops
 * @package     Mageshops_Rakuten
 * @license     http://license.mageshops.com/  Unlimited Commercial License
 * @copyright   mageSHOPS.com 2014 - 2015
 * @author      Kristaps Rjabovs with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/**
 * Class Mageshops_Rakuten_Helper_Permutation
 */
class Mageshops_Rakuten_Helper_Permutation extends Mageshops_Market_Helper_Data
{

    /**
     * @var
     */
    private $_data;

    /**
     * @var int
     */
    public $minElements = 1;

    /**
     * @var int
     */
    public $maxElements;

    /**
     * @param $data
     * @param int $minElements
     * @param null $maxElements
     */
    public function __construct($data, $minElements = 1, $maxElements = null)
    {
        $this->_data = $data;
        if ((int)$minElements >= 1) {
            $this->minElements = (int)$minElements;
        }
        if (is_null($maxElements)) {
            $this->maxElements = count($this->_data);
        } elseif ((int)$maxElements >= 1) {
            $this->maxElements = (int)$maxElements;
        }
    }

    /**
     * @param $data
     * @param $pos
     * @param $combinations
     * @param array $combination
     */
    private function _permute($data, $pos, &$combinations, $combination = array())
    {
        if ($pos < count($data)) {
            $combination[] = $data[$pos];
            $count = count($combination);
            if ($count >= $this->minElements && $count <= $this->maxElements) {
                $combinations[] = $combination;
            }
            $this->_permute($data, ++$pos, $combinations, $combination);
        }
    }

    /**
     * @return array
     */
    public function permute()
    {
        $combinations = array();
        for ($pos = 0; $pos < count($this->_data); $pos++) {
            $this->_permute($this->_data, $pos, $combinations);
        }
        return $combinations;
    }
}