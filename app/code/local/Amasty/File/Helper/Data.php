<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getUploadDir()
    {
         return Mage::getBaseDir('media') . DS . 'amfile' . DS ;
    }

    public function getUploadErrorMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $this->__($message);
    }

    public function giveFile($id)
    {
        $file = Mage::getModel("amfile/file")->load($id);
        if (!$file->getId())
            die($this->__('Invalid link'));

        if (!Mage::app()->getStore()->isAdmin())
            Mage::getSingleton("amfile/stat")->saveStat($file->getData());

        $path = $file->getFullName();

        $mimeType = $this->getMimeType($path);

        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header("Content-Type: $mimeType");
            header('Content-Disposition: inline; filename="' . $file->getFileName() . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            ob_start();
            readfile($path);
            ob_end_flush();
            exit;
        }
    }

    public function getMimeType($path)
    {
        $type = 'application/octet-stream';

        if (Mage::getStoreConfig('amfile/additional/detect_mime'))
        {
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $finfoType = finfo_file($finfo, $path);
                finfo_close($finfo);

                if ($finfoType !== false) {
                    $type = $finfoType;
                }
            }
            else if (function_exists('mime_content_type')) {
                $type = mime_content_type($path);
            }
        }

        return $type;
    }

	public function getFtpImportDir()
	{
		$dir = Mage::getStoreConfig('amfile/import/ftp_dir');
		if(substr($dir,0,-1) != "/") {
			$dir .= "/";
		}
		//var_dump(substr($dir,0,1));
		if(substr($dir,0,1) != "/") {
			$dir = "/".$dir;
		}
		$dir = Mage::getBaseDir().$dir;

		return $dir;
	}

    public function getSetCustomerGroups()
    {
        $groups = Mage::getStoreConfig('amfile/block/customer_group');
        return $groups !== null ? explode(',',$groups) : array();
    }
}
