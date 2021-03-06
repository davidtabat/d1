<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

Class Ess_M2ePro_Model_Magento_Product_Rule extends Ess_M2ePro_Model_Abstract
{
    protected $_conditions = null;

    protected $_form;

    protected $_productIds = array();

    protected $_collectedAttributes = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Magento_Product_Rule');
    }

    //########################################

    /**
     * Create rule instance from serialized array
     *
     * @param string $serialized
     * @throws Ess_M2ePro_Model_Exception
     *
     */
    public function loadFromSerialized($serialized)
    {
        $prefix = $this->getPrefix();
        if ($prefix === null) {
            throw new Ess_M2ePro_Model_Exception('Prefix must be specified before.');
        }

        $this->_conditions = $this->getConditionInstance($prefix);

        if (empty($serialized)) {
            return;
        }

        $conditions = Mage::helper('M2ePro')->unserialize($serialized);
        $this->_conditions->loadArray($conditions, $prefix);
    }

    /**
     * Create rule instance form post array
     *
     * @param array $post
     * @throws Ess_M2ePro_Model_Exception
     *
     */
    public function loadFromPost(array $post)
    {
        $prefix = $this->getPrefix();
        if ($prefix === null) {
            throw new Ess_M2ePro_Model_Exception('Prefix must be specified before.');
        }

        $this->loadFromSerialized($this->getSerializedFromPost($post, $prefix));
    }

    //########################################

    /**
     * Get serialized array from post array
     *
     * @param array $post
     * @return string
     * @throws Ess_M2ePro_Model_Exception
     *
     */
    public function getSerializedFromPost(array $post)
    {
        $prefix = $this->getPrefix();
        if ($prefix === null) {
            throw new Ess_M2ePro_Model_Exception('Prefix must be specified before.');
        }

        $conditionsArray = $this->_convertFlatToRecursive($post['rule'][$prefix], $prefix);

        return Mage::helper('M2ePro')->serialize($conditionsArray[$prefix][1]);
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getPrefix()
    {
        return $this->getData('prefix');
    }

    public function getStoreId()
    {
        if ($this->getData('store_id') === null) {
            return 0;
        }

        return $this->getData('store_id');
    }

    public function getConditionsSerialized()
    {
        return $this->getData('conditions_serialized');
    }

    public function getAttributeSets()
    {
        return $this->getData('attribute_sets');
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getCollectedAttributes()
    {
        return $this->_collectedAttributes;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setCollectedAttributes(array $attributes)
    {
        $this->_collectedAttributes = $attributes;
        return $this;
    }

    // ---------------------------------------

    public function getCustomOptionsFlag()
    {
        return $this->getData('use_custom_options');
    }

    // ---------------------------------------

    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
        }

        return $this->_form;
    }

    // ---------------------------------------

    /**
     * Get condition instance
     *
     * @return Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine
     * @throws Ess_M2ePro_Model_Exception
     *
     */
    public function getConditions()
    {
        $prefix = $this->getPrefix();
        if ($prefix === null) {
            throw new Ess_M2ePro_Model_Exception('Prefix must be specified before.');
        }

        if ($this->_conditions !== null) {
            return $this->_conditions->setJsFormObject($prefix)->setStoreId($this->getStoreId());
        }

        if ($this->getConditionsSerialized() !== null) {
            $this->loadFromSerialized($this->getConditionsSerialized());
        } else {
            $this->_conditions = $this->getConditionInstance($prefix);
        }

        return $this->_conditions->setJsFormObject($prefix)->setStoreId($this->getStoreId());
    }

    //########################################

    /**
     * @return bool
     */
    public function isEmpty()
    {
        if ($this->_conditions === null) {
            return true;
        }

        $conditionProductsCount = 0;
        foreach ($this->_conditions->getConditionModels() as $model) {
            if ($model instanceof Ess_M2ePro_Model_Magento_Product_Rule_Condition_Product) {
                ++$conditionProductsCount;
            }
        }

        return $conditionProductsCount == 0;
    }

    /**
     * Validate magento product with rule
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        return $this->getConditions()->validate($object);
    }

    /**
     * Add filters to magento product collection
     *
     * @param Varien_Data_Collection_Db
     */
    public function setAttributesFilterToCollection(Varien_Data_Collection_Db $collection)
    {
        if (empty($this->getConditions()->getData($this->getPrefix()))) {
            return;
        }

        $this->_productIds = array();
        $this->getConditions()->collectValidatedAttributes($collection);

        $idFieldName = $collection->getIdFieldName();
        if (empty($idFieldName)) {
            $idFieldName = Mage::getModel('catalog/product')->getIdFieldName();
        }

        Mage::getSingleton('core/resource_iterator')->walk(
            $collection->getSelect(),
            array(array($this, 'callbackValidateProduct')),
            array(
                'attributes' => $this->getCollectedAttributes(),
                'product' => Mage::getModel('catalog/product'),
                'store_id' => $collection->getStoreId(),
                'id_field_name' => $idFieldName
            )
        );

        $collection->addFieldToFilter($idFieldName, array('in' => $this->_productIds));
    }

    //########################################

    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $args['row']['store_id'] = $args['store_id'];
        $product->setData($args['row']);

        if ($this->validate($product)) {
            $this->_productIds[] = $product->getData($args['id_field_name']);
        }
    }

    /**
     * @return string
     */
    public function getConditionClassName()
    {
        return 'M2ePro/Magento_Product_Rule_Condition_Combine';
    }

    protected function getConditionInstance($prefix)
    {
        $conditionInstance = Mage::getModel($this->getConditionClassName())
            ->setRule($this)
            ->setPrefix($prefix)
            ->setValue(true)
            ->setId(1)
            ->setData($prefix, array());

        if ($this->getCustomOptionsFlag() !== null) {
            $conditionInstance->setCustomOptionsFlag($this->getCustomOptionsFlag());
        }

        return $conditionInstance;
    }

    protected function _convertFlatToRecursive(array $data, $prefix)
    {
        $arr = array();
        foreach ($data as $id=>$value) {
            $path = explode('--', $id);
            $node =& $arr;
            for ($i=0, $l=sizeof($path); $i<$l; $i++) {
                if (!isset($node[$prefix][$path[$i]])) {
                    $node[$prefix][$path[$i]] = array();
                }

                $node =& $node[$prefix][$path[$i]];
            }

            foreach ($value as $k => $v) {
                $node[$k] = $v;
            }
        }

        return $arr;
    }

    //########################################

    protected function _beforeSave()
    {
        $serialized = Mage::helper('M2ePro')->serialize($this->getConditions()->asArray());
        $this->setData('conditions_serialized', $serialized);

        return parent::_beforeSave();
    }

    //########################################

    /**
     * Using model from controller
     *
     *      get serialized data for saving to database ($serializedData):
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix')->setStoreId(0);
     *          $serializedData = $ruleModel->getSerializedFromPost($post);
     *
     *      set model to block for view rules from database ($serializedData):
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix')->setStoreId(0);
     *          $ruleModel->loadFromSerialized($serializedData);
     *
     *          $ruleBlock = $this->getLayout()
     *                            ->createBlock('M2ePro/adminhtml_magento_product_rule')
     *                            ->setData('rule_model', $ruleModel);
     *
     * Using model for check magento product with rule
     *
     *      using serialized data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix')->setStoreId(0);
     *          $ruleModel->loadFromSerialized($serializedData);
     *          $checkingResult = $ruleModel->validate($magentoProductInstance);
     *
     *      using post array data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix')->setStoreId(0);
     *          $ruleModel->loadFromPost($post);
     *          $checkingResult = $ruleModel->validate($magentoProductInstance);
     *
     * Using model for filter magento product collection with rule
     *
     *      using serialized data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix')->setStoreId(0);
     *          $ruleModel->loadFromSerialized($serializedData);
     *          $ruleModel->setAttributesFilterToCollection($magentoProductCollection);
     *
     *      using post array data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix')->setStoreId(0);
     *          $ruleModel->loadFromPost($post);
     *          $ruleModel->setAttributesFilterToCollection($magentoProductCollection);
     *
     */
}
