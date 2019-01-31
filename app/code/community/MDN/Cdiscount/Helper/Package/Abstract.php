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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

abstract class MDN_Cdiscount_Helper_Package_Abstract extends Mage_Core_Helper_Abstract {

    protected $_type = null;
    protected $_packageId = null;
    protected $_products = null;
    protected $_submitted = array();

    const kNullAttribute = '{x:Null}';

    /**
     * Setter products
     *
     * @param collection $products
     */
    protected function _setProducts($products) {
        $this->_products = $products;
    }

    /**
     * Getter products
     *
     * @return collection
     */
    protected function _getProducts() {
        return $this->_products;
    }

    /**
     * Get package directory
     *
     * @return string
     */
    public function getPackageDirectory() {
        return Mage::Helper('Cdiscount')->getExportPath() . 'package';
    }

    /**
     * Build product XML file
     */
    abstract protected function _buildXML();

    protected function _getPackageTmpPath() {
        return $this->getPackageDirectory() . '/tmp/' . $this->getPackageId();
    }

    /**
     * Build package
     *
     * @return string 
     */
    public function buildPackage($products) {

        $this->_packageId = null;

        $this->_setProducts($products);

        // create tmp directories
        foreach (array('_rels', 'Content') as $dir)
            if (!file_exists($this->_getPackageTmpPath() . '/' . $dir))
                mkdir($this->_getPackageTmpPath() . '/' . $dir, 0777, true);

        // create files
        $this->_buildRel();
        $this->_buildContentType();
        $this->_buildXML();

        // zip & move
        $this->_zip();

        // clear tmp
        $this->_clear($this->getPackageDirectory() . '/tmp');

        return array(
            'id' => $this->getPackageId(),
            'submitted' => $this->_submitted
        );
    }

    /**
     * Clear tmp directory
     *
     * @return boolean
     */
    protected function _clear($dir) {

        $handle = opendir($dir);

        Mage::helper('Cdiscount')->magentoLog('Clear temp : '.$dir);

        if (is_dir($dir))
        {
            while (false !== ($entry = readdir($handle))) {

                if ($entry == '.' || $entry == '..')
                    continue;

                if (is_dir($dir . '/' . $entry)) {
                    $this->_clear($dir . '/' . $entry);
                    rmdir($dir . '/' . $entry);
                }else
                    unlink($dir . '/' . $entry);
            }
        }

        return 0;
    }

    /**
     * Zip package
     */
    protected function _zip() {

        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration('cdiscount');
        $zipMethod = $config->getzipMethod();

		$this->log('Zip method is '.$zipMethod);
		
        switch($zipMethod){
            
            case 'zip_archive':
                
                $zip = new ZipArchive();

                if($zip->open($this->getZipFileFullPath(), ZIPARCHIVE::CREATE) !== true)
                throw new Exception('zip error');

                // add rel
                $zip->addEmptyDir('_rels');
                $zip->addFile($this->_getPackageTmpPath().'/_rels/.rel', '_rels/.rel');

                // add product / offer file
                $zip->addEmptyDir('Content');
                $zip->addFile($this->_getPackageTmpPath().'/Content/'.ucfirst($this->_type).'.xml', 'Content/'.ucfirst($this->_type).'.xml');

                // add content
                $zip->addFile($this->_getPackageTmpPath().'/[Content_Types].xml', '[Content_Types].xml');

                // save zip
                $this->_save($this->getZipFileFullPath(), $zip->filename);

                $zip->close();
                
                break;
            
            case 'zip':

                // zip 
                $cmd = 'cd ' . $this->_getPackageTmpPath() . ';zip -r ' . $this->_getPackageTmpPath() . '.zip ./';
                $result = exec($cmd);
				$this->log('Zip command is : '.$cmd.' : ');

                // save zip
                rename($this->_getPackageTmpPath() . '.zip', $this->getZipFileFullPath());
				$this->log('Rename '.$this->_getPackageTmpPath() . '.zip TO '.$this->getZipFileFullPath());
            default:
                break;
            
        }
        
        return 0;

    }

    /**
     * Getter type
     *
     * @return string
     */
    protected function _getType() {
        return $this->_type;
    }

    /**
     * Build .rel file
     */
    protected function _buildRel() {

        $xml = new DomDocument('1.0', 'utf-8');

        $relationships = $xml->createElement('Relationships', '');
        $xml->appendChild($relationships);

        $xmlns = $xml->createAttribute('xmlns');
        $xmlns->appendChild($xml->createTextNode("http://schemas.openxmlformats.org/package/2006/relationships"));
        $relationships->appendChild($xmlns);

        $relationship = $xml->createElement('Relationship', '');
        $target = $xml->createAttribute('Target');
        $target->appendChild($xml->createTextNode("/Content/" . ucfirst($this->_getType()) . ".xml"));
        $relationship->appendChild($target);

        $type = $xml->createAttribute('Type');
        $type->appendChild($xml->createTextNode("http://cdiscount.com/uri/document"));
        $relationship->appendChild($type);

        $id = $xml->createAttribute('Id');
        $id->appendChild($xml->createTextNode($this->getPackageId()));
        $relationship->appendChild($id);

        $relationships->appendChild($relationship);

        $content = $xml->saveXML();

        // save file
        $this->_save($this->_getPackageTmpPath() . '/_rels/.rel', $content);
    }

    /**
     * Get package name
     *
     * @return string
     */
    public function getPackageName() {
        return $this->getPackageId();
    }

    /**
     * Get package Id
     *
     * @return string
     */
    public function getPackageId() {

        if ($this->_packageId === null) {

            $country = Mage::registry('mp_country');
            $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
            $prefix = $account->getParam('package_prefix');

            $this->_packageId = 'BMS_'.$prefix. '_' . md5(date('YmdHis'));
        }

        return $this->_packageId;
    }

    /**
     * Get zip path
     *
     * @return string
     */
    public function getZipFileFullPath() {
        $mainDirectory = $this->getPackageDirectory() . '/' . strtolower($this->_getType());
        if (!is_dir($mainDirectory))
            mkdir($mainDirectory, 0755, true);
        return  $mainDirectory . '/' . $this->getPackageId() . '.zip';
    }

    /**
     * Save files
     *
     * @param string $name
     * @param string $content
     * @return int 0
     */
    protected function _save($name, $content) {

        $path_parts = pathinfo($name);

        if (!file_exists($path_parts['dirname']))
            mkdir($path_parts['dirname'], 0755, true);

        $handle = fopen($name, 'w+');
        fputs($handle, $content);
        fclose($handle);

        //return 0;
        // remove old files
        $handle = opendir($path_parts['dirname']);
        $files = array();
        while ($file = readdir($handle)) {
            if (!is_dir($path_parts['dirname'] . '/' . $file) && !preg_match('/^\./', $file))
                $files[$file] = filemtime($path_parts['dirname'] . '/' . $file);
        }

        arsort($files);
        $oldFiles = array_slice($files, 20, count($files));

        foreach ($oldFiles as $k => $v) {
            unlink($path_parts['dirname'] . '/' . $k);
        }

        return 0;
    }

    /**
     * Build [content_type] file
     */
    protected function _buildContentType() {

        $xml = new DomDocument('1.0', 'utf-8');

        $types = $xml->createElement('Types', '');
        $xml->appendChild($types);

        $xmlns = $xml->createAttribute('xmlns');
        $xmlns->appendChild($xml->createTextNode("http://schemas.openxmlformats.org/package/2006/content-types"));
        $types->appendChild($xmlns);

        foreach ($this->_getDefaultNodes() as $item) {

            $default = $xml->createElement('Default', '');
            $extension = $xml->createAttribute('Extension');
            $extension->appendChild($xml->createTextNode($item['Extension']));
            $default->appendChild($extension);

            $contentType = $xml->createAttribute('ContentType');
            $contentType->appendChild($xml->createTextNode($item['ContentType']));
            $default->appendChild($contentType);

            $types->appendChild($default);
        }

        $content = $xml->saveXML();

        // save
        $this->_save($this->_getPackageTmpPath() . '/[Content_Types].xml', $content);
    }

    /**
     * Get default nodes for content type
     *
     * @return array
     */
    protected function _getDefaultNodes() {

        return array(
            array(
                'Extension' => 'xml',
                'ContentType' => "text/xml"
            ),
            array(
                'Extension' => 'rels',
                'ContentType' => "application/vnd.openxmlformats-package.relationships+xml"
            )
        );
    }

}
