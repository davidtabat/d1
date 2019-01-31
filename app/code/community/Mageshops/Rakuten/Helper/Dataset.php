<?php

/**
 * @category    Mageshops
 * @package     Mageshops_Rakuten
 * @license     http://license.mageshops.com/  Unlimited Commercial License
 * @copyright   mageSHOPS.com 2014 - 2015
 * @author      Kristaps Rjabovs with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/**
 * Class Mageshops_Rakuten_Helper_Dataset
 */
class Mageshops_Rakuten_Helper_Dataset extends Mageshops_Market_Helper_Data
{
    /**
     * @var
     */
    public $dataSet;

    /**
     * @var
     */
    private $_keys;

    /**
     * @param $dataSet
     */
    public function __construct($dataSet)
    {
        $this->dataSet = $dataSet;
        $this->_keys = array_keys($this->dataSet);
    }

    /**
     * @param bool $allRequiredMode
     * @return array
     */
    public function getNoRepeatCombinations($allRequiredMode = true)
    {
        $combinations = array();
        if ($allRequiredMode) {
            foreach ($this->getLevel(0) as $col) {
                $combinations[] = array($col);
            }
            if (count($this->dataSet) > 1) {
                $this->_getNoRepeatCombinations(1, $combinations);
            }
        } else {
            $perm = new Mageshops_Rakuten_Helper_Permutation($this->_keys);
            $levelCombinations = $perm->permute();
            foreach ($levelCombinations as $levels) {
                $levelDataSet = array();
                foreach ($levels as $levelKey) {
                    $levelDataSet[] = $this->dataSet[$levelKey];
                }
                $ds = new Mageshops_Rakuten_Helper_Dataset($levelDataSet);
                $combinations = array_merge(
                    $combinations,
                    $ds->getNoRepeatCombinations()
                );
            }
        }
        return $combinations;
    }

    /**
     * @param $level
     * @param $combinations
     */
    private function _getNoRepeatCombinations($level, &$combinations)
    {
        $initial = $combinations;
        $combinations = array();
        foreach ($this->getChildrenOfLevel($level - 1) as $k => $child) {
            foreach ($initial as $combination) {
                $combination[] = $child;
                $combinations[] = $combination;
            }
        }
        if ($this->levelExists($level + 1)) {
            $this->_getNoRepeatCombinations($level + 1, $combinations);
        }
    }

    /**
     * @param $level
     * @return array
     */
    public function getChildrenOfLevel($level)
    {
        return $this->getLevel($level + 1);
    }

    /**
     * @param $level
     * @return bool
     */
    public function levelExists($level)
    {
        return isset($this->_keys[$level]);
    }

    /**
     * @param $level
     * @return array
     */
    public function getLevel($level)
    {
        if ($this->levelExists($level)) {
            return $this->dataSet[$this->_keys[$level]];
        }
        return array();
    }
}