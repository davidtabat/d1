<?php class SoulSurf_Import_Model_Category_Api
extends Mage_Catalog_Model_Category_Api {

/**
 * Returns an array of data...
 *
 * @param integer $categoryId
 * @return array
 */
public function getMyData($categoryId) {
    $category = $this->_initCategory($categoryId, null);
// do something

    return $myData->toArray();
  }

}

?>