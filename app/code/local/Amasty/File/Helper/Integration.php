<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Helper_Integration extends Mage_Core_Helper_Abstract
{
    /**
     * @param $productId - associated product ID
     * @param $fileName - file name relative to /media/amfile/files/ directory
     * @param $title - file caption on front-end
     */
    public function assignFile($productId, $fileName, $title, $position = 0)
    {
        $file = Mage::getModel('amfile/file')
            ->setData(array(
                'product_id' => $productId,
                'file_url' => $fileName,
                'file_name' => $fileName
            ))
            ->save();

        Mage::getModel('amfile/store')
            ->setData(array(
                'file_id' => $file->getId(),
                'visible' => 1,
                'label' => $title,
				'position' => $position
            ))
            ->save();
    }
}
