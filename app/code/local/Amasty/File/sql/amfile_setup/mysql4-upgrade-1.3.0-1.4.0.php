<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

$this->startSetup();

$pdfIcon = Mage::getModel('amfile/icon')->load('pdf', 'type');

if (!$pdfIcon->getId()) {
    $pdfIcon
        ->setData(array(
            'type'      => 'pdf',
            'image'     => 'pdf-24_32.png',
            'active'    => 1
        ))
        ->save();
    ;
}

$this->endSetup();
