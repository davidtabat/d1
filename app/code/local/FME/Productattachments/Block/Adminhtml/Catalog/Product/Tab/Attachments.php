<?php
/**
 * Productattachments extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Productattachments
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */
 
 
class FME_Productattachments_Block_Adminhtml_Catalog_Product_Tab_Attachments 
	extends Mage_Adminhtml_Block_Template
	implements Mage_Adminhtml_Block_Widget_Tab_Interface {//Mage_Adminhtml_Block_Widget_Grid {
 
	public function __construct() {
		parent::__construct();
		/*$this->setId('product_attachments_grid');
		$this->setDefaultSort('productattachments_id');
		$this->setUseAjax(true);	
		$this->setDefaultFilter(array('in_attachments'=>1));*/
		//$collection = Mage::getModel('productattachments/productattachments')->getCollection();
		//$collection->getSelect()
			//->join(array('cat' => $this->_fetchTable('productattachments/productcats'), 'main_table.cats_id = cat.category_id'));
			/*$log = (string) $collection->getSelect();
			Mage::log($log);*/
		//$this->setCollection($collection);
		$this->setTemplate('productattachments/attachments.phtml');
		//$this->setUseAjax(true);
	}
	protected function _fetchTable($moduleTable) {
		
		return Mage::getSingleton('core/resource')->getTableName($moduleTable);
	}
	
	public function getProductcatsCollection() {
		
		$collection = Mage::getModel('productattachments/productcats')
						->getCollection();
			//print_r($collection->getData());			
		return $collection;				
	}
	
	
	public function getProductAttachmentsCollection($categoryId = null) {
		
		$collection = Mage::getModel('productattachments/productattachments')->getCollection();
		
		if ($categoryId != null) {
			
			$collection->addFieldToFilter('cat_id', $categoryId);
		}
		//echo (string) $collection->getSelect();
		return $collection;
	}
	/**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Attachments');
    }
     
    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Click here to view your attachments tab content');
    }
     
    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
     
    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
 
     
    /**
     * AJAX TAB's
     * If you want to use an AJAX tab, uncomment the following functions
     * Please note that you will need to setup a controller to recieve
     * the tab content request
     *
     */
    /**
     * Retrieve the class name of the tab
     * Return 'ajax' here if you want the tab to be loaded via Ajax
     *
     * return string
     */
#   public function getTabClass()
#   {
#       return 'my-custom-tab';
#   }
 
    /**
     * Determine whether to generate content on load or via AJAX
     * If true, the tab's content won't be loaded until the tab is clicked
     * You will need to setup a controller to handle the tab request
     *
     * @return bool
     */
#   public function getSkipGenerateContent()
#   {
#       return false;
#   }
 
    /**
     * Retrieve the URL used to load the tab content
     * Return the URL here used to load the content by Ajax
     * see self::getSkipGenerateContent & self::getTabClass
     *
     * @return string
     */
#   public function getTabUrl()
#   {
#       return null;
#   }

	/**
	 * Retirve currently edited product model
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct()
	{
		return Mage::registry('current_product');
	}


	/**
     * Add filter
     *
     * @param object $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Related
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_attachments') {
            $attachmentIds = $this->_getSelectedAttachments();
            if (empty($attachmentIds)) {
                $attachmentIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('productattachments_id', array('in'=>$attachmentIds));
            } else {
                if($attachmentIds) {
                    $this->getCollection()->addFieldToFilter('productattachments_id', array('nin'=>$attachmentIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

	public function getBlockTitle() {
		
		$_res = Mage::getSingleton('core/resource');
		
		$table = $_res->getTableName('productattachments_products');
		$_read = $_res->getConnection('core_read');
		
		$q = $_read->select()
				->from(array('prod' => $table), 'block_name_product')
				->where('prod.product_id = (?)', $this->getProduct()->getId());
		
		return $_read->fetchOne($q);
	}
 
/*
   protected function _prepareColumns()
  {
	  
	  $this->addColumn('in_attachments', array(
			'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_attachments',
			'values'            => $this->_getSelectedAttachments(),
			'align'             => 'center',
			'index'             => 'productattachments_id'
		));
	  
      $this->addColumn('productattachments_id', array(
          'header'    => Mage::helper('productattachments')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'productattachments_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('productattachments')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('productattachments')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
      return parent::_prepareColumns();
  }
*/
  public function getGridUrl() {
    	return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/attachmentsGrid', array('_current' => true));
  }
  
  	public function _getSelectedAttachments()
    {
        $products = $this->getProductRelatedAttachments();	
        if (!is_array($products)) {
            $products = array_keys($this->getProductAttachments());
        }
        return $products;
    }
  
  	/**
     * Retrieve related products
     *
     * @return array
     */
    public function getProductAttachments()
    {
		$_res = Mage::getSingleton('core/resource');
		$table = $_res->getTableName('productattachments_products');
		$_read = $_res->getConnection('core_read');
		$q = $_read->select()
				->from(array('p' => $table))
				->where('p.product_id = (?)', $this->getProduct()->getId());
				
		$result = $_read->fetchAll($q); //echo '<pre>';print_r($result);
		$id = array();
		if (count($result) > 0) {
			
			foreach($result as $k => $v) {
				
				$id[] = $v['productattachments_id'];
			}
		}//echo '<pre>';print_r($id);
		
		return array_unique($id);
    }
	
}

?>
