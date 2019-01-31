<?php

ini_set('max_execution_time', 900); //900 seconds = 15 minutes

apc_clear_cache(); usleep(100);
apc_clear_cache('user'); usleep(100);
apc_clear_cache('opcode');usleep(100);

require 'app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);

echo "Clean cache 1 cleanCache():\n";
Mage::app()->cleanCache();usleep(100);
echo "Clean cache 2 getCacheInstance()->flush():\n";
Mage::app()->getCacheInstance()->flush();usleep(100);

echo "\n";

Mage::getConfig()->init();
$types = Mage::app()->getCacheInstance()->getTypes();

try {
    echo "Cleaning data cache... \n";
    flush();
    foreach ($types as $type => $data) {
        echo "Removing $type ... ";
        echo Mage::app()->getCacheInstance()->clean($data["tags"]) ? "[OK]" : "[ERROR]";
        echo "\n";
    }
} catch (exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}
 
echo "\n";
 
try {
    echo "Cleaning stored cache... ";
    flush();
    echo Mage::app()->getCacheInstance()->clean() ? "[OK]" : "[ERROR]";
    echo "\n\n";
} catch (exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}
 
try {
    echo "Cleaning merged JS/CSS...";
    flush();
    Mage::getModel('core/design_package')->cleanMergedJsCss();
    Mage::dispatchEvent('clean_media_cache_after');
    echo "[OK]\n\n";
} catch (Exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}
 
/*try {
    echo "Cleaning image cache... ";
    flush();
    echo Mage::getModel('catalog/product_image')->clearCache();
    echo "[OK]\n";
} catch (exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}*/
?>
