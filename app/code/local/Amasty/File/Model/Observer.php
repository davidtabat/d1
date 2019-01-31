<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Model_Observer 
{
    public function saveProductTabData(Varien_Event_Observer $observer)
    {
        $result = $this->updateFileData();
        if ($result['errors'])
            foreach ($result['errors'] as $error)
                Mage::getSingleton('adminhtml/session')->addError($error);
    }

    public function updateFileData($fromGrid = false)
    {
        $files = Mage::app()->getRequest()->getPost('files');

        $result = array(
            'errors' => array(),
            'updated' => array(),
        );

        if ($files) {
            foreach ($files as $id => $fileData)
            {
                $fileData['product_id'] = Mage::app()->getRequest()->getParam($fromGrid === false ? 'id' : 'amfile_product');
                if($fromGrid === true)
                {
                    /**
                     * @var Amasty_File_Helper_String_Data
                     */
                    $strHelper = Mage::helper('amstring');

                    $fileData['title'] = $strHelper->initTitleFromFileName($_FILES['files']['name'][-1]['file']);
                }

                $file = Mage::getModel("amfile/file");


                $result = $this->loadFile($id, $fileData, $file);
            }
        }

        return $result;
    }

    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        $massactionClass  = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $productGridClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_grid');
        if ($massactionClass == get_class($block) && $productGridClass == get_class($block->getParentBlock()))
        {
            $hlp = Mage::helper('amfile');

            if (Mage::getSingleton('admin/session')->isAllowed('catalog/amfile/amfile_clear')) {
                $block->addItem('amfile_clear', array(
                    'label'      => $hlp->__('Remove All Attachments'),
                    'url'        => $block->getParentBlock()->getUrl('adminhtml/amfile_actions/clear/'),
                ));
            }

            if (Mage::getSingleton('admin/session')->isAllowed('catalog/amfile/amfile_copy')) {
                $additional = array('source' => array(
                    'name'  => 'source',
                    'type'  => 'text',
                    'class' => 'required-entry validate-greater-than-zero',
                    'label' => $hlp->__('Source Product Id'),
                ));
                $block->addItem('amfile_copy', array(
                    'label'      => $hlp->__('Copy Attachments'),
                    'url'        => $block->getParentBlock()->getUrl('adminhtml/amfile_actions/copy/'),
                    'additional' => $additional,
                ));
            }
        }

        return $this;
    }

    public function onProductDeleteBefore($observer)
    {
        $product = $observer->getProduct();

        $files = Mage::getResourceModel('amfile/file_collection')
            ->addFieldToFilter('product_id', $product->getId());

        foreach ($files as $file)
            $file->delete();
    }

    /**
     * Handler for event `controller_action_layout_render_before_adminhtml_customer_index`.
     * @param Varien_Event_Observer $observer
     */
    public function forIndexCustomerGrid($observer)
    {

        if(Mage::getStoreConfig('amfile/additional/grid_upload') == 1)
        {

            /**
             *@var Mage_Adminhtml_Block_Catalog_Product_Grid $block
             */
            $block = $observer->getBlock();
            $hlp = Mage::helper('amfile');
            $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_grid');
            if ($blockClass == get_class($block)) {
                $column = array(
                    'header'   => $hlp->__('Product Attachments'),
                    'width'    => '150px',
                    'index'    => 'attach_file',
                    'type'     => 'file',
                    'filter'   => false,
                    'sortable' => false,
                    'renderer' => 'amfile/adminhtml_renderer_file',
                );
                $block->addColumnAfter('attach_file', $column, 'websites');
                $column = array(
                    'header'   => $hlp->__('Attached Files'),
                    'width'    => '150px',
                    'index'    => 'attached_files',
                    'renderer' => 'amfile/adminhtml_renderer_attachments',
                    'filter'   => false,
                    'sortable' => false,
                );
                $block->addColumnAfter('attached_files', $column, 'attach_file');
            }
        }
    }

    /**
     * @param int $fileId
     * @param array $fileData
     * @param Amasty_File_Model_File $file
     * @return array
     */
    protected function loadFile($fileId, $fileData, $file)
    {
        $result = array(
            'errors' => array(),
            'updated' => array(),
        );

        try
        {

            if ($fileId > 0)
            {
                $file->load($fileId);
            }

            $file->addData($fileData);

            if ($file->getDelete() == 1)
            {
                if (!$file->isObjectNew())
                {
                    $file->delete();
                }
                return null;
            }

            if ($file->getUse() == 'file')
            {
                $file->setFileLink('');

                if (isset($_FILES['files']['error'][$fileId]['file']))
                {
                    $code = $_FILES['files']['error'][$fileId]['file'];
                    if (0 == $code)
                    {
                        $file->saveFile($_FILES['files']['name'][$fileId]['file'], $_FILES['files']['tmp_name'][$fileId]['file']);
                        if ($fileData['file_name'])
                            $file->setFileName($fileData['file_name']);
                        else
                            $file->setFileName($_FILES['files']['name'][$fileId]['file']);
                    }
                    else
                    {
                        if ($code !== UPLOAD_ERR_NO_FILE) // Not error
                        {
                            $result['errors'][$fileId] = Mage::helper('amfile')->getUploadErrorMessage($code);
                            return null;
                        }
                    }
                }
            }
            elseif ($file->getUse() == 'url') {
                $file->removeOldFile();
            }

            if ($file->getFileUrl() || $file->getFileLink())
            {

                $storeId = $file->getOrigData() === null ? 0 : +Mage::app()->getRequest()->getParam('store', 0); // New object. Can't use isObjectNew
                $file->save();
                $result['updated'][]= $file->getId();

                $storeData = array(
                    'file_id' => +$file->getId(),
                    'store_id' => $storeId,
                    'label' => (string)$file->getTitle(),
                    'position' => +$file->getPosition(),
                    'visible' => +$file->getVisible(),
                    'use_default_label' => +$file->getDefaultTitle(),
                    'use_default_visible' => +$file->getDefaultVisible(),
                    'show_ordered' => +$file->getShowOrdered(),
                    'use_default_show_ordered' => +$file->getDefaultShowOrdered(),
                    'use_default_customer_group' => +$file->getDefaultCustomerGroup(),
                );

                $writeConnection = Mage::getSingleton('core/resource')
                                       ->getConnection('core/write');

                $writeConnection->insertOnDuplicate(
                    Mage::getSingleton('core/resource')->getTableName('amfile/store'),
                    $storeData
                );

                $customerGroups = $file->getFormCustomerGroups()
                    ? $file->getFormCustomerGroups()
                    : Mage::getStoreConfig('amfile/block/customer_group');

                if (count($customerGroups) == 1 && strpos($customerGroups[0], ',')) {
                    $customerGroups = explode(',', $customerGroups[0]);
                } elseif (is_string($customerGroups)) {
                    $customerGroups = explode(',', $customerGroups);
                }

                $writeConnection->delete(
                    Mage::getSingleton('core/resource')->getTableName('amfile/store_customer_group'),
                    new Zend_Db_Expr(sprintf("file_id = %d AND store_id = %d", $file->getId(), $storeId))
                );
                foreach ($customerGroups as $group) {

                    $customerGroupData = array(
                        'file_id' => +$file->getId(),
                        'store_id' => $storeId,
                        'customer_group_id' => $group,
                    );
                    $writeConnection->insertOnDuplicate(
                        Mage::getSingleton('core/resource')->getTableName('amfile/store_customer_group'),
                        $customerGroupData
                    );
                }
            }
            else
            {
                if ($file->getId()) // skip new empty entries
                    throw new Exception(Mage::helper('amfile')->__("File or file url must be specified"));
            }
        }
        catch (Exception $e)
        {
            $result['errors'][$fileId] = $e->getMessage();
        }

        return $result;

    }

    public function onLayoutRenderBefore($observer)
    {
        if (!Mage::getStoreConfigFlag('amfile/block/enabled'))
            return;

        $layout = Mage::app()->getLayout();

        $productBlock = $layout->getBlock('product.info');
        if (!$productBlock)
            return;

        $attachmentsBlock = $layout->createBlock('amfile/file', 'amfile.files');

        $location = Mage::getStoreConfig('amfile/block/location');
        $sibling = Mage::getStoreConfig('amfile/block/sibling');
        $position = Mage::getStoreConfig('amfile/block/position');

        $insertAfter = ($position == Amasty_File_Model_Config_Source_Position::POSITION_AFTER);

        switch ($location) {

            case Amasty_File_Model_Config_Source_Location::LOCATION_TEXT_LIST:
                $parentName = Mage::getStoreConfig('amfile/block/parent_name');

                if (!$parentName)
                    return;

                $parentBlock = $layout->getBlock($parentName);

                if ($parentBlock) {
                    $parentBlock->insert(
                        $attachmentsBlock,
                        $sibling,
                        $insertAfter,
                        'amfile.files'
                    );
                }
                break;

            case Amasty_File_Model_Config_Source_Location::LOCATION_MAGENTO_TABS:
                if ($tabsBlock = $layout->getBlock('product.info.tabs')) {
                    $title = $attachmentsBlock->getBlockTitle();
                    $attachmentsBlock->setBlockTitle(false);

                    if ($tabsBlock instanceof Amasty_File_Block_Rewrite_Product_View_Tabs) {
                        $tabsBlock->addTab(
                            'amfile.files',
                            $title,
                            $attachmentsBlock,
                            $attachmentsBlock->getTemplate(),
                            $sibling,
                            $insertAfter
                        );
                    }
                    else {
                        $tabsBlock->addTab(
                            'amfile.files',
                            $title,
                            $attachmentsBlock,
                            $attachmentsBlock->getTemplate()
                        );
                    }
                }
                break;

            case Amasty_File_Model_Config_Source_Location::LOCATION_PRODUCT_INFO:
                $productBlock->insert($attachmentsBlock, $sibling, $insertAfter);

                $title = $attachmentsBlock->getBlockTitle();
                $attachmentsBlock->setBlockTitle(false);

                $attachmentsBlock
                    ->addToParentGroup('detailed_info')
                    ->setTitle($title)
                ;
                break;

            default:
        }
    }
}
