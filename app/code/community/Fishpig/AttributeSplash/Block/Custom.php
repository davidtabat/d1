<?php

class Fishpig_AttributeSplash_Block_Group_View extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();

        $param = $this->getRequest()->getParam('attribute');

        echo "Parameter Catched :: ".$param;
        
        $collection = Mage::getModel('attributeSplash/group')->getGroupProductsCollection();
        $this->setCollection($collection);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5 => 5, 10 => 10, 20 => 20, 'all' => 'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

}
