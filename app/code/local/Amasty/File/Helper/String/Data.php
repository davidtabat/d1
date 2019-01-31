<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Helper_String_Data extends Mage_Core_Helper_String
{

    /**
     * Generate File Title
     * Example file_file_file.txt -> File File File
     * @param string $fileName
     * @return string
     */
    public function initTitleFromFileName($fileName)
    {
        $fileName = preg_replace('/[-_]/', ' ', $fileName);
        $fileName = preg_replace('/([.]([a-z0-9])+)$/', '', $fileName);
        $fileName = ucwords($fileName);

        return $fileName;

    }
}