<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/**
 * Rakuten Category model class
 */
class Mageshops_Rakuten_Model_Category extends Mageshops_Rakuten_Model_Abstract
{
    protected $_rakuten = null;
    protected $_done    = array();

    protected function _getRakuten()
    {
        if (!$this->_rakuten) {

            $this->_rakuten = new Varien_Object();

            $file = Mage::getBaseDir('var') . '/nn_market/rakuten/rakuten-categories.xml';
            if (!file_exists($file)) {
                $this->syncAllRakutenCategories();
            }
            $rakuten = simplexml_load_file($file);

            if ($rakuten !== false) {

                $success = $rakuten->success;
                $this->_rakuten->setData('success', (int)$rakuten->success);

                if ($success) {
                    $this->_rakuten->setData('total', (int)$rakuten->categories->paging->total);
                    $this->_rakuten->setData('page', (int)$rakuten->categories->paging->page);
                    $this->_rakuten->setData('pages', (int)$rakuten->categories->paging->pages);
                    $this->_rakuten->setData('per_page', (int)$rakuten->categories->paging->per_page);

                    $collection = new Varien_Data_Collection();

                    foreach ($rakuten->categories->category as $cat) {
                        $item = new Varien_Object();
                        $item->setData('shop_category_id', (int)$cat->shop_category_id);
                        $item->setData('name', (string)$cat->name);
                        $item->setData('visible', (int)$cat->visible);
                        $item->setData('external_shop_category_id', (string)$cat->external_shop_category_id);
                        $item->setData('layout', (string) $cat->layout);
                        $collection->addItem($item);
                    }

                    $this->_rakuten->setData('category_collection', $collection);
                }
            } else {
                unlink($file);
                throw new Exception($this->__('There was an error within stored categories.'));
            }
        }

        return $this->_rakuten;
    }

    public function syncAllRakutenCategories()
    {
        $helper = Mage::helper('rakuten');
        $rakuten_request = $helper->getCategoryRequestUrl();

        $marketDir = Mage::getBaseDir('var') . '/nn_market/rakuten/';
        if (!file_exists($marketDir)) {
            if (!mkdir($marketDir, 0777, true)) {
                throw new Exception($this->__('There was an error creating dir for storage. Please check your permissions.'));
            }
        }

        $helper->setState($this->__('Fetching category list from Rakuten.'), 0);

        $page = 1;
        $pages = 1;

        $doc1 = new DOMDocument('1.0', 'UTF-8');

        do {
            $params = $helper->getRequestParams();
            $params['page'] = $page;
            $request = $helper->callAPI($rakuten_request, $params);
            $xml_answer = $request->getAnswer();

            if ($page === 1) {

                if ($doc1->loadXML($xml_answer) == false) {
                    $request->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR_NON_XML)->save();
                    throw new Exception('Answer from Rakuten can not be parsed as XML.');
                }

                if ($doc1->getElementsByTagName('success')->item(0)->nodeValue == 0) {
//                    throw new Exception($this->__('Error occurred. No more info is available'));
                } else {
                    $pages = (int)$doc1->getElementsByTagName('pages')->item(0)->nodeValue;
                }

            } else {

                $doc2 = new DOMDocument();
                if ($doc2->loadXML($xml_answer) == false) {
                    $request->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR_NON_XML)->save();
                    throw new Exception('Answer from Rakuten can not be parsed as XML.');
                }

                if ($doc2->getElementsByTagName('success')->item(0)->nodeValue == 0) {
//                    throw new Exception($this->__('Error occurred. No more info is available'));
                } else {
                    $res1 = $doc1->getElementsByTagName('categories')->item(0);

                    $items2 = $doc2->getElementsByTagName('category');

                    for ($i = 0; $i < $items2->length; $i++) {
                        $item2 = $items2->item($i);
                        $item1 = $doc1->importNode($item2, true);
                        $res1->appendChild($item1);
                    }
                }
            }

            $helper->setState($this->__('Page %s of %s was processed.', $page, $pages), $page / $pages);

            $page++;
        } while ($page <= $pages);

        $doc1->save($marketDir . 'rakuten-categories.xml');

        $helper->syncLog($this->__('Category list was fetched from your Rakuten account.'));
        $helper->setState($this->__('Category list was fetched from your Rakuten account.'), 1);

        return $this;
    }

    public function getRakuten()
    {
        return $this->_getRakuten();
    }

    /**
     * @return Varien_Data_Collection
     */
    public function getCollection()
    {
        return $this->_getRakuten()->getCategoryCollection();
    }

    /**
     * @param $id
     * @return Varien_Object
     */
    public function getCategory($id)
    {
        return $this->getCollection()->getItemByColumnValue('shop_category_id', $id);
    }

    public function getCategoryByMagentoId($categoryId)
    {
        return $this->getCollection()->getItemByColumnValue('external_shop_category_id', $categoryId);
    }

    public function updateSyncList($categoryIds)
    {
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('rakuten_sync')
            ->addAttributeToFilter('rakuten_sync', array('eq' => 1));

        foreach ($categories as $category) {
            if (!in_array($category->getId(), $categoryIds)) {
                $category->setRakutenSync(0);
                $category->save();
            }
        }

        $mark = array_diff($categoryIds, $categories->getAllIds());

        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('rakuten_sync')
            ->addAttributeToFilter('entity_id', array('in' => $mark));

        foreach ($categories as $category) {
            $category->setRakutenSync(1);
            $category->save();
        }

        return $this;
    }

    public function syncAllToRakuten()
    {
        $helper            = Mage::helper('rakuten');
        $rakutenCategories = $this->getCollection();

        $magentoCategories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('rakuten_sync', array('eq' => 1));

        $addList = array();
        $usedIds = array();

        $helper->syncLog($this->__('Categories syncing process was started.'));
        $helper->setState($this->__('Categories syncing process was started.'), 0);

        $size = $magentoCategories->getSize();
        $i = 0;
        foreach ($magentoCategories as $magentoCategory) {

            $rakutenCategory = $this->getCategoryByMagentoId($magentoCategory->getId());
            if (!$rakutenCategory) {

                $magentoCategory->setRakutenCategoryId(null);

            } else {

                $rakutenId = $rakutenCategory->getShopCategoryId();
                $magentoCategory->setRakutenCategoryId($rakutenId);
                $usedIds[] = $rakutenId;

            }
            $magentoCategory->save();

            $addList[] = $magentoCategory->getId();

            $helper->setState($this->__('Preparing categories list.'), ++$i / $size);

            if ($message = $helper->checkLowResources()) {
                $helper->setState($message, 0);
                return $this;
            }
        }

        $size = $rakutenCategories->getSize();
        $i = 0;
        foreach ($rakutenCategories as $rakutenCategory) {

            $rakutenId = $rakutenCategory->getShopCategoryId();

            if ($helper->forceCategoryRecreate() || !in_array($rakutenId, $usedIds)) {
                $this->deleteRakutenCategory($rakutenId);
            }

            $helper->setState($this->__('Cleaning categories at Rakuten.'), ++$i / $size);

            if ($message = $helper->checkLowResources()) {
                $helper->setState($message, 0);
                return $this;
            }
        }

        $this->_done = array();

        $size = $magentoCategories->getSize();
        $i = 0;
        foreach ($magentoCategories as $category) {
            $this->syncRakutenCategory($category, $addList);
            $helper->setState($this->__('Syncing categories to Rakuten.'), ++$i / $size);

            if ($message = $helper->checkLowResources()) {
                $helper->setState($message, 0);
                return $this;
            }
        }

        unset($this->_done);

        $helper->syncLog($this->__('Categories syncing process finished.'));
        $helper->setState($this->__('Categories syncing process finished.'), 1);

        return $this;
    }

    protected function _prepareDescription($category)
    {
        $helper = Mage::helper('catalog/output');

        $description = $helper->categoryAttribute($category, $category->getDescription(), 'description');
        $description = nl2br($description);
        $description = str_replace(array("\n", "\r"), '', $description);
        $description = strip_tags($description, '<a><b><blockquote><br><caption><col><colgroup><div><em><font><h1><h2><h3><h4><h5><h6><hr><i><img><label><li><link><param><object><ol><p><q><s><small><span><strike><strong><style><table><tbody><td><th><thead><title><tr><u><ul>');

        return $description;
    }

    public function _addCategory(Mage_Catalog_Model_Category $category, array $parent)
    {
        return $this->_editCategory($category, $parent, true);
    }

    protected function _editCategory(Mage_Catalog_Model_Category $category, array $parent, $forceAdd = false)
    {
        $helper = Mage::helper('rakuten');
        $helperOutput = Mage::helper('catalog/output');

        $rakutenId = false;
        if ($forceAdd == false) {
            $rakutenCategory = $this->getCategoryByMagentoId($category->getId());
            if ($rakutenCategory) {
                $rakutenId = $rakutenCategory->getShopCategoryId();
            }
        }

        $params = $helper->getRequestParams();
        if ($rakutenId) {
            $params['shop_category_id'] = $rakutenId;
        }
        $params['external_shop_category_id'] = $category->getId();
        $params['name']                      = $helperOutput->categoryAttribute($category, $category->getName(), 'name');
        if ($parent['rakutenId']) {
            $params['parent_shop_category_id']          = $parent['rakutenId'];
            $params['external_parent_shop_category_id'] = $parent['id'];
        } else {
            $params['parent_shop_category_id']          = 0;
            $params['external_parent_shop_category_id'] = 0;
        }
        if ($category->getRakutenSyncDescription()) {
            $params['description'] = $this->_prepareDescription($category);
        }

        $params['layout'] = $this->_getCategoryLayout($category);
        $params['visible'] = $category->getIsActive() ? 1 : -1;

        if ($rakutenId === false) {
            $rakutenRequest = $helper->getAddShopCategoryRequestUrl();
        } else {
            $rakutenRequest = $helper->getEditShopCategoryRequestUrl();
        }

        $request = $helper->callAPI($rakutenRequest, $params);
        $rakuten = $helper->checkAnswer($request, $params);

        if ($rakutenId === false) {
            $rakutenId = (int) $rakuten->shop_category_id;
            $category->setRakutenCategoryId($rakutenId);
        }
        $category->setRakutenSync(true);
        $category->save();

        return $rakutenId;
    }

    private function _getCategoryLayout(Mage_Catalog_Model_Category $category)
    {
        $categoryLayout = $category->getRakutenCategoryLayout();
        if ($categoryLayout === null || $categoryLayout < 0 || $categoryLayout > 2) {
            $categoryLayout = Mage::getStoreConfig('nn_market/rakuten_category/layout');
        }
        $values = Mage::getModel('rakuten/system_config_source_categoryLayout')->toArray();

        return $values[$categoryLayout];
    }

    public function syncRakutenCategory($category, array $catIds)
    {
        $helper = Mage::helper('rakuten');

        if (!$category instanceof Mage_Catalog_Model_Category) {

            $category = Mage::getModel('catalog/category')->load($category);
            if (!$category) {
                throw new Exception($this->__('Cannot load category %s', $category));
            }
        }

        if (in_array($category->getId(), $this->_done)) {
            return $category->getRakutenCategoryId();
        }

        $parentCategory = $category->getParentCategory();
        $parent['id'] = $parentCategory->getId();

        $parent['rakutenId'] = false;
        if (in_array($parent['id'], $catIds)) {
            $parent['rakutenId'] = $this->syncRakutenCategory($parentCategory, $catIds);
        }

        $rakutenId = $category->getRakutenCategoryId();
        if ($rakutenId) {
            try {
                $rakutenId = $this->_editCategory($category, $parent);
            } catch (Exception $e) {
                $helper->syncLog($helper->__('Trying to recreate category %s.', $parent['id']));
                $rakutenId = $this->_addCategory($category, $parent);
            }
        } else {
            $rakutenId = $this->_addCategory($category, $parent);
        }

        if (is_array($this->_done)) {
            $this->_done[] = $category->getId();
        }

        $helper->syncLog($this->__('Category %s was synchronized to your Rakuten account.', $category->getId()));
        return $rakutenId;
    }

    public function deleteRakutenCategory($rakutenId)
    {
        $helper = Mage::helper('rakuten');

        $params = $helper->getRequestParams();
        $params['shop_category_id'] = $rakutenId;

        $rakutenRequest = $helper->getDeleteShopCategoryRequestUrl();

        $request = $helper->callAPI($rakutenRequest, $params);
        $helper->checkAnswer($request, $params);

        $helper->syncLog($this->__('Category %s was deleted from your Rakuten account.', $rakutenId));

        return $this;
    }
}
