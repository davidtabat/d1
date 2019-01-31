<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_FileController extends Mage_Core_Controller_Front_Action
{
    public function downloadAction()
    {
        $fileId = $this->getRequest()->getParam('file_id');

        Mage::helper('amfile')->giveFile($fileId);
    }
}
