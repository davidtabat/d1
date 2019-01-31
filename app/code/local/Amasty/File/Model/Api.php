<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * @param int $productId
     * @param int $storeId
     * @param int $attachmentId
     * @return array
     */
    public function getAttachments($productId, $storeId = 0, $attachmentId = 0, $readFile = false)
    {
        $files = Mage::getResourceModel('amfile/file_collection')->getFilesApi($productId, $storeId, $attachmentId);
        if($readFile == true) {
            $files = $files->readFiles();
        }
        $result = $files->toArray();
        $result = $result['items'];

        return $result;
    }

    /**
     * @param int $productId product's id
     * @param array $attachments - collection attachments
     * @return boolean
     */
    public function addAttachments($attachments)
    {
        $result = true;
        foreach($attachments as $attachment)
        {
           $result = $this->addAttachment((array)$attachment) == true
            ? $result : false;
        }
        return $result;
    }

    /**
     * @param array $attachmentData - attachment [description]
     *  @option int file_id if is 0 add new attachments, if not null update exists attachment
     *  @option int product_id
     *  @option string title
     *  @option string file_name path to file
     *  @option string file_name file name uploadable file
     *  @option string file_link url to uploadable file
     *  @option string file_path file path to uploadable file
     *  @option int position
     *  @option boolean visible
     *
     * @return boolean
     */
    public function addAttachment(array $attachmentData)
    {
        try
        {
            $file = Mage::getModel('amfile/file');

            if((int)$attachmentData['file_id'] > 0) {
                $file->load($attachmentData['file_id']);
            }
            else
            {
                unset($attachmentData['file_id']);
            }

            $file->removeOldFile();
            $file->addData($attachmentData);

            if( ($file->getFilePath() && $file->getFilePath() != $file->getUploadDir() )
                ||$attachmentData['file_content'])
            {
                $newFileName = $file->newFileName($file->getFileName());

                if($attachmentData['file_content'])
                {
                    $fileContent = base64_decode($attachmentData['file_content'], true);
                    if(!$fileContent) {
                        $this->_fault('incorrect_base64');
                    }
                    $result = file_put_contents($file->getUploadDir().$newFileName, $fileContent);
                }
                else
                {
                    if(!file_exists($file->getFilePath().DS.$file->getFileName())) {
                        $this->_fault('file_not_found'
                            , Mage::helper('amfile')->__("Can't find file %s in '%s'. Check the path to file and the permissions", $file->getFileName(), $file->getBasePath())
                        );
                    }
                    $result = copy($file->getFilePath().DS.$file->getFileName(), $file->getUploadDir().$newFileName);
                }

                if(!$result) {
                    throw new Exception(Mage::helper('amfile')->__("Can't upload file %s to '%s'. Check the permissions and directory exists", $file->getFileName(), $file->getUploadDir()));
                }
                $file->setFileUrl($newFileName);
            }
            $file->save();

            $storeData = array(
                'file_id' => +$file->getId(),
                'store_id' => $file->getOrigData() === null ? 0 : (int)$file->getStoreId(), // New object. Can't use isObjectNew
                'label' => $file->getTitle(),
                'position' => +$file->getPosition(),
                'visible' => +$file->getVisible(),
                'use_default_label' => +$file->getDefaultTitle(),
                'use_default_visible' => +$file->getDefaultVisible()
            );

            Mage::getSingleton('core/resource')
                ->getConnection('core/write')
                ->insertOnDuplicate(
                    Mage::getSingleton('core/resource')->getTableName('amfile/store'),
                    $storeData
                );
        }
        catch (Exception $e)
        {
            $result = $this->_fault('file_not_uploaded', $e->getMessage());
        }
        return isset($result) ? $result : false;
    }

    public function deleteAttachments($productId, $attachmentId = 0)
    {
        $files = Mage::getModel('amfile/file')->getCollection()->addFilter('product_id', $productId);
        if($attachmentId != 0) {
            $files->addFilter('file_id', $attachmentId);
        }
        return $files->deleteAttachments();
    }

}