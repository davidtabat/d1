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
 * @copyright  Copyright 2010 ? free-magentoextensions.com All right reserved
 */
 
class FME_Productattachments_Block_Adminhtml_Productattachments extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_productattachments';
    $this->_blockGroup = 'productattachments';
    $this->_headerText = Mage::helper('productattachments')->__('Attachments Manager');
    $this->_addButtonLabel = Mage::helper('productattachments')->__('Add File');
    parent::__construct();
  }
}