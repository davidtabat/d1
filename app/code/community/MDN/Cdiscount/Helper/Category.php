<?php

/**
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
class MDN_Cdiscount_Helper_Category extends Mage_Core_helper_Abstract {


    /**
     * Return categies file path
     */
    public function getFilePath()
    {
        $customPath = $this->getCustomFilePath();
        if (file_exists($customPath))
            return $customPath;
        else
            return Mage::getBaseDir('lib').DS.'cdiscount'.DS.'categories.csv';
    }

    protected function getCustomFilePath()
    {
        return Mage::getBaseDir('lib').DS.'cdiscount'.DS.'custom_categories.csv';
    }

    /**
     * Get category path
     * 
     * @param int $value
     * @return string $retour 
     */
    public function getCatName($value){
        
        $retour = "";
        $lines = file($this->getFilePath());
        
        foreach($lines as $line){
            
            $line = trim(str_replace('"','', $line));
            
            $tmp = explode(';', $line);
            
            if($tmp[0] == $value){
                
                $retour .= $tmp[1].' > '.$tmp[2].' > '.$tmp[3].' > '.$tmp[4];
                
            }
            
        }
        
        return $retour;
    }
    
    /**
     * get univers as combo
     * 
     * @return string $html
     */
    public function getUniversAsCombo(){
        
        $lines = file($this->getFilePath());
        $array = array();
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration('cdiscount');
        
        $html = '<select name="univers" id="univers" onChange="showCategoriesForUnivers(this.value);return false;">';
        $html .= '<option></option>';
        
        for($i = 1; $i < count($lines); $i++){
            
            $lines[$i] = trim(str_replace('"', '', $lines[$i]));
            
            if($lines[$i] == "")
                continue;
            
            $tmp = explode(';', $lines[$i]);
            
            if($tmp[0] == "")
                continue;           
            
            if(in_array(str_replace(' ','_',trim($tmp[1])), $config->getcategories()) && !array_key_exists($tmp[1], $array)){
                
                $array[$tmp[1]] = true;
                $html .= '<option value="'.$this->_formatValue($tmp[1]).'">'.$tmp[1].'</option>';
                
            }            
            
        }                
        
        $html .= '</select>';
        return $html;
    }
    
    /**
     * get category as combo
     * 
     * @param string $univers
     * @return string $html 
     */
    public function getCategoriesAsCombo($univers){
        
        $html = "";
        $array = array();
        $lines = file($this->getFilePath());
                
        $html .= '<select name="categories" id="categories" onChange="showSubCategoriesForCategory(this.value); return false;">';
        $html .= '<option></option>';
        
        for($i = 1; $i < count($lines); $i++){
            
            $lines[$i] = trim(str_replace('"', '', $lines[$i]));
            
            if($lines[$i] == "")
                continue;
            
            $tmp = explode(';', $lines[$i]);
            
            if($tmp[0] == "")
                continue;
            
            if($this->_formatValue($tmp[1]) == $univers && !array_key_exists($tmp[2], $array)){
                
                $array[$tmp[2]] = true;
                $html .= '<option value="'.$this->_formatValue($tmp[2]).'">'.$tmp[2].'</option>';
                
            }
            
        }
                
        $html .= '</select>';
        return $html;        
        
    }
    
    /**
     * Get sub category as combo
     * 
     * @param string $cat
     * @return string $html 
     */
    public function getSubCategoriesAsCombo($cat){
        
        $html = "";
        $array = array();
        
        $html .= '<select name="sub_categories" id="sub_categories" onChange="showSubCategorySubCategories(this.value);return false;">';
        $html .= '<option></option>';
        
        $lines = file($this->getFilePath());
        
        for($i = 1; $i < count($lines); $i++){
            
            $lines[$i] = trim(str_replace('"', '', $lines[$i]));
            
            if($lines[$i] == "")
                continue;
            
            $tmp = explode(';', $lines[$i]);
            
            if($tmp[0] == "")
                continue;
            
            if($this->_formatValue($tmp[2]) == $cat && !array_key_exists($tmp[3], $array)){
                
                $array[$tmp[3]] = true;
                $html .= '<option value="'.$this->_formatValue($tmp[3]).'">'.$tmp[3].'</option>';
                
            }
            
        }
        
        $html .= '</select>';
        return $html;
        
    }
    
    /**
     * Get sub sub categories as combo ...
     * 
     * @param string $cat
     * @return string $html 
     */
    public function getSubSubCategoriesAsCombo($cat){
        
        $html = "";
        $array = array();
        
        $lines = file($this->getFilePath());
        
        $html .= '<select name="sub_sub_categories" id="sub_sub_categories" onChange="updateReferenceCdiscount();return false;">';
        $html .= '<option></option>';
        
        for($i = 1; $i < count($lines); $i++){
            
            $lines[$i] = trim(str_replace('"','', $lines[$i]));
            
            if($lines[$i] == "")
                continue;
            
            $tmp = explode(';', $lines[$i]);
            
            if($tmp[0] == "")
                continue;
            
            if($this->_formatValue($tmp[3]) == $cat && !array_key_exists($tmp[4], $array)){
                
                $array[$tmp[4]] = true;
                $html .= '<option value="'.$tmp[0].'">'.$tmp[4].'</option>';
                
            }
            
        }
        
        $html .= '</select>';
        return $html;
        
    }
    
    /**
     * Get reference
     * 
     * @param string $cat
     * @return int $retour 
     */
    public function getReference($cat){
        
        $retour = null;
        
        $lines = file($this->getFilePath());
        
        for($i = 1; $i < count($lines); $i++){
            
            $lines[$i] = trim(str_replace('"','',$lines[$i]));
            
            if($lines[$i] == "")
                continue;
            
            $tmp = explode(';', $lines[$i]);
            
            if($tmp[0] == "")
                continue;
            
            if($cat == $tmp[0]){
                $retour = $tmp[0];
                break;
            }            
            
        }
        
        return $retour;
        
    }
    
    /**
     * Return formated value
     *  
     * @param string $v
     * @return string 
     */
    protected function _formatValue($v){
        return str_replace(array(' ', '/'), array('_', '='), $v);
    }
	
    /**
     * Get category data
     *  
     * @param object $product
     * @return array $retour 
     */
    public function getCategoryData($product){

            $retour = array();

            $data = Mage::Helper('MarketPlace/Categories')->getCategoryDataForProduct($product, Mage::Helper('Cdiscount')->getMarketPlaceName());

            if($data != ""){

                    $lines = file($this->getFilePath());

                    foreach($lines as $line){

                            $tmp = explode(';',$line);

                            if(trim($tmp[0]) == $data){

                                    $retour = array(
                                            'code' => $data,
                                            'niveau1' => trim($tmp[1]),
                                            'niveau2' => trim($tmp[2]),
                                            'niveau3' => trim($tmp[3]),
                                            'niveau4' => trim($tmp[4])
                                    );

                                    break;

                            }

                    }

            }

            return $retour;

    }
    
    /**
     * Get categories
     * 
     * @return array $retour
     */
    public function getCategories(){
        
        $retour = array();
        $lines = array();
        $tmp = array();
        
        $lines = file($this->getFilePath());

        for($i = 1; $i < count($lines); $i++){
            $tmp = explode(';',$lines[$i]);
            $key = str_replace(' ', '_', trim($tmp[1]));
            if(!array_key_exists($key, $retour)){
                $retour[$key] = $tmp[1];
            }
            
        }

        return $retour;
    }

    /**
     * Update categories list in csv file
     */
    public function generateCustomCategoryFile()
    {
        $result = Mage::Helper('Cdiscount/Services')->getAllowedCategoryTree();
        $string = $result['content'];
        $string = str_replace('xmlns=', 'ns=', $string);
        $xml = new SimpleXMLElement($string);


        $nodes = $xml->xpath('/s:Envelope/s:Body/GetAllowedCategoryTreeResponse/GetAllowedCategoryTreeResult/CategoryTree/ChildrenCategoryList/CategoryTree');

        $all = array();
        $all[] = 'Code;Niveau 1;Niveau 2;Niveau 3;Niveau 4';
        foreach($nodes as $node)
        {
            $this->recursiveCategoriesParse($node, array(), $all);
        }

        $filePath = $this->getCustomFilePath();
        $res = file_put_contents($filePath, implode("\n", $all));
        if (!$res)
            throw new Exception('Unable to write categories file to '.$filePath);

        return $filePath;
    }

    /**
     * Recursively browse XML categories file
     *
     * @param $node
     * @param $parents
     * @param $all
     */
    protected function recursiveCategoriesParse($node, $parents, &$all)
    {
        $childs = $node->xpath('ChildrenCategoryList/CategoryTree');
        $parents[] = $node->Name;
        if (count($childs) == 0)
        {
            $all[] = $node->Code.';'.implode(';', $parents);
        }
        else
        {
            foreach($childs as $child)
                $this->recursiveCategoriesParse($child, $parents, $all);
        }

    }

}
