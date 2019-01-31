<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Model_File extends Mage_Core_Model_Abstract {

    public function _construct() 
    {
        $this->_init('amfile/file', 'file_id');
    }

    public function getUploadDir()
    {
        return Mage::getBaseDir() . DS . 'media' . DS . 'amfile' . DS . 'files' . DS;
    }

    public function getFullName($name = null)
    {
        if (!$name)
            $name = $this->getFileUrl();

        return $this->getUploadDir() . $name;
    }

    public function delete()
    {
        $this->removeOldFile();

        return parent::delete();
    }

    public function saveFile($fileName, $tempFileName, $iisUploadFile = true)
    {
        $this->removeOldFile();
        $fileName = strtolower($fileName);

        $fileName = preg_replace('/[^\w\.-]/', '', $fileName);

        $fileName = $this->newFileName($fileName);
        $uploadFile = $this->getFullName($fileName);

        if (($iisUploadFile && move_uploaded_file($tempFileName, $uploadFile)) || copy($tempFileName, $uploadFile))
            $this->setFileUrl($fileName);
        else
            throw new Exception(Mage::helper('amfile')->__("Can't upload file to '%s'. Check the permissions and directory exists", $this->getUploadDir()));
    }

    public function newFileName($fileName) 
    {
        $i = 0;
        $newFileName = $fileName;

        while (file_exists($this->getFullName($newFileName)))
            $newFileName = "(" . ++$i . ")" . $fileName;

        return $newFileName;
    }

    public function removeOldFile()
    {
        $linksCount = Mage::getResourceModel('amfile/file_collection')
            ->addFieldToFilter('file_url', $this->getOrigData('file_url'))
            ->getSize();

        $this->setFileUrl('');

        if ($linksCount > 1) {
            $fullName = $this->getFullName($this->getOrigData('file_url'));
            if (is_file($fullName)) {
                unlink($fullName);
            }
        }
        return;

    }

    public function title()
    {
        if ($this->getLabel())
            return $this->getLabel();
        else if ($this->getFileName())
            return $this->getFileName();
        else
            return $this->getFileLink();
    }

    public function update()
    {

    }

    public function getIcon()
    {
        $url = $this->getFileUrl() ? $this->getFileUrl() : $this->getFileLink();

        return Mage::getModel('amfile/icon')->getIcon($url);
    }

    /**
     * Read File content and set property in base 64
     * @return mixed
     */
    public function readFile()
    {
        $file = new Varien_Io_File();
        $fileContent = $file->read($this->getFullName($this->getOrigData('file_url')));

        $fileContent = base64_encode($fileContent);

        $this->setData('file_content', $fileContent);
        return $this;
    }

    public function getFileSize()
    {
        if ($this->getFileUrl()) {
            $fileSize = filesize($this->getFullName());
        } elseif ($this->getFileLink()) {
            $file = fopen($this->getFileLink(), 'r');
            $fileInfo = stream_get_meta_data($file);
            fclose($file);

            foreach ($fileInfo['wrapper_data'] as $headerData) {
                if (stristr($headerData, 'content-length')) {
                    $contentLength = explode(': ', $headerData);
                    $fileSize = $contentLength[1];
                    break;
                }
            }
        }
        return $fileSize;
    }

    public function getFullFileSize()
    {
        $fileSize = $this->getFileSize();
        if ($fileSize >= pow(2, 20)) {
            return sprintf('%.2f MB', $fileSize/pow(2, 20));
        } elseif($fileSize >= pow(2, 10)) {
            return sprintf('%.2f KB', $fileSize/pow(2, 10));
        } else {
            return sprintf('%.2f B', $fileSize/pow(2, 20));
        }
    }

    public function getCustomerGroups()
    {
        $customerGroups = $this->getData('customer_groups');
        return $customerGroups !== null ? explode(',',$customerGroups)
            : Mage::helper('amfile')->getSetCustomerGroups();

    }
}
