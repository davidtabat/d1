<?php

require_once('app/Mage.php');

umask(0);

Mage::app();
//$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('mst_helpdesk');

echo "Host is checked in"; die;
