<?php

// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require '../app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

umask(0);

$disabledFuncs = explode(',', ini_get('disable_functions'));
$isShellDisabled = is_array($disabledFuncs) ? in_array('shell_exec', $disabledFuncs) : true;
$isShellDisabled = (stripos(PHP_OS, 'win') === false) ? $isShellDisabled : true;

try {
    /** @var Mageshops_Rakuten_Helper_Data $helper */
    $helper = Mage::helper('rakuten');
    $startFrom = isset($_GET['continue']) ? $_GET['startFrom'] : 0;
    $continueSimple = isset($_GET['simple']) ? $_GET['simple'] : true;
    $helper->batchSynchronization(isset($_GET['continue']), $startFrom, (bool)$continueSimple);
} catch (Exception $e) {
    Mage::printException($e);
    exit(1);
}

