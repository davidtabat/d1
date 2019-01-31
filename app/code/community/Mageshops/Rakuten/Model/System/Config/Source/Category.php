<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_System_Config_Source_Category
{
    private $_optionArrayResult = array();


    public function toOptionArray($addEmpty = true)
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name');

        $catsArr = array();

        foreach ($collection as $category) {
            $catsArr[] = $category->toArray(array('entity_id', 'parent_id', 'name', 'level'));
        }

        $new = array();
        foreach ($catsArr as $cat) {
            $new[$cat['parent_id']][] = $cat;
        }

        $tree = $this->createTree($new, array($catsArr[0]));
        $this->parseTree($tree);

        return $this->_optionArrayResult;
    }

    private function createTree(&$list, $parent)
    {
        $tree = array();
        foreach ($parent as $k => $l) {
            if (isset($list[$l['entity_id']])) {
                $l['children'] = $this->createTree($list, $list[$l['entity_id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }

    private function parseTree($item, $parentName = '')
    {
        foreach ($item as $cat) {
            $this->addToOptionArray($cat['entity_id'], $cat['name'], $cat['level'], $parentName);
            if (@array_key_exists('children', $cat)) {
                $parentNameNew = $parentName . $cat['name'] . ' / ';
                $this->parseTree($cat['children'], $parentNameNew);
            }
        }
    }

    private function getPrefix($level)
    {
        $prefix = '';
        for ($i = 0; $i < $level; $i++) {
            $prefix .= '......';
        }
        $prefix .= '|---';

        return $prefix;
    }

    public function addToOptionArray($id, $name, $level, $parentName)
    {
        $this->_optionArrayResult[] = array(
            'value' => $id,
            'title' => $parentName . Mage::helper('rakuten')->__($name),
            'label' => sprintf('%s%s', '', Mage::helper('rakuten')->__($name))
        );
    }
}
