<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_Feed extends Mage_Core_Model_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Feed');
    }

    /**
     * Delete old files
     * 
     * @param string $dir
     * @return int 0
     */
    public static function deleteOldFiles($dir) {

        $files = array();

        if (file_exists($dir)) {

            $handle = opendir($dir);
            while ($file = readdir($handle)) {
                $filemtime = filemtime($dir . '/' . $file);
                // on conserve les flux des 15 derniers jours
                if ($filemtime < strtotime(date('Y-m-d', Mage::getModel('core/date')->timestamp() - 24 * 3600 * 7)))
                    $files[] = $file;
            }

            $files = array_diff($files, array('.', '..'));

            foreach ($files as $file) {

                if (is_dir($dir . '/' . $file)) {

                    self::deleteOldFiles($dir . '/' . $file);
                } else {

                    unlink($dir . '/' . $file);
                }
            }

            // on verifie que le dossier est vide . & .. seulement
            if (count(scandir($dir)) == 2)
                rmdir($dir);
        }

        return 0;
    }

    /**
     * Remove files on disk
     */
    protected function _beforeDelete()
    {
        $dir = Mage::getBaseDir().DS.'MarketplaceFeed'.DS.$this->getmp_marketplace_id().DS.$this->getmp_type().DS.$this->getmp_feed_id();
        if (is_dir($dir))
        {
            $this->delTree($dir);
        }

    }

    public function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * Before save : move xml feeds into some directory instead of using database
     * 
     * @throws Exception 
     */
    public function _beforeSave() {

        if (!$this->getmp_type())
            $this->setmp_type(MDN_MarketPlace_Helper_Feed::kFeedTypeDivers);

        $base = Mage::app()->getConfig()->getBaseDir() . '/MarketplaceFeed/' . strtolower($this->getmp_marketplace_id()) . '/' . $this->getmp_type();

		// generation du feed id
        $feedId = (trim($this->getmp_feed_id()) == '-' || !$this->getmp_feed_id()) ? md5(rand(0, 5) . date('Y-m-d H-i-s')) : $this->getmp_feed_id();
		$this->setmp_feed_id($feedId);
		
        if (!$this->getmp_id()) {

            // creation            
			
            // generation nom du repertoire
            $dir = $base . '/' . $feedId;

            // creation du repertoire
            if (!file_exists($dir))
                if (!mkdir($dir, 0755, true))
                    throw new Exception('Can\'t create dir ' . $dir . ' in ' . __METHOD__);

            // content
            if ($this->getmp_content()) {
                $contentFilename = $dir . '/content.xml';
                $handle = fopen($contentFilename, 'w+');
                fputs($handle, $this->getmp_content());
                fclose($handle);
                $this->setmp_content($contentFilename);
            }

            // response
            if ($this->getmp_response()) {
                $responseFilename = $dir . '/response.xml';
                $handle = fopen($responseFilename, 'w+');
                fputs($handle, $this->getmp_response());
                fclose($handle);
                $this->setmp_response($responseFilename);
            }
        } else {

            // update
            // load data
            $feed = Mage::getModel('MarketPlace/Feed')->load($this->getmp_id());

            $lastContent = $feed->getmp_content();
            $lastResponse = $feed->getmp_response();
            $lastFeedId = $feed->getmp_feed_id();

			// si le feed ID à changé, on renome le répertoire attaché
            if ($lastFeedId != $this->getmp_feed_id()) {
                if (file_exists($base . '/' . $lastFeedId))
                    rename($base . '/' . $lastFeedId, $base . '/' . $this->getmp_feed_id());
            }

            $directory = $base . '/' . $this->getmp_feed_id();

            // content
            if (!preg_match('#.xml$#', $this->getmp_content())) {
                $filename = $directory . '/content.xml';
                file_put_contents($filename, $this->getmp_content());
            }

            // response
            if (!preg_match('#.xml$#', $this->getmp_response())) {
                $filename = $directory . '/response.xml';
                file_put_contents($filename, $this->getmp_response());
            }

            // update path
            $this->setmp_content($directory . '/content.xml');
            $this->setmp_response($directory . '/response.xml');
        }

        parent::_beforeSave();
    }
    
    /**
     * Get response
     * 
     * @return string $retour
     */
    public function getResponse() {

        $retour = "";

        if (file_exists($this->getmp_response()))
            $retour = file_get_contents($this->getmp_response());
        else
            $retour = 'Fichier inexistant : ' . $this->getmp_response();

        return $retour;
    }

    /**
     * Get content
     * 
     * @return string $retour 
     */
    public function getContent() {

        $retour = "";

        if (file_exists($this->getmp_content()))
            $retour = file_get_contents($this->getmp_content());
        else
            $retour = false;

        return $retour;
    }

}