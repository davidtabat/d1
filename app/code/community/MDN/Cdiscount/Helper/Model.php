<?php

/**
 * Cdiscount Package url management
 *
 * @author Nicolas Mugnier <nicolas@boostmyshop.com>
 * @version
 * @package MDN_Cdiscount
 */
class MDN_Cdiscount_Helper_Model extends Mage_Core_Helper_Abstract {

    protected $_cache = array();

    public function getModelNameForCategory($categoryCode)
    {
        $nodes = $this->getNodes($categoryCode, '/s:Envelope/s:Body/GetModelListResponse/GetModelListResult/ModelList/ProductModel');
        if ($nodes)
        {
            foreach($nodes as $node)
                return (string)$node->Name;
        }

        return false;
    }

    public function getProperties($categoryCode)
    {
        $nodes = $this->getNodes($categoryCode, '/s:Envelope/s:Body/GetModelListResponse/GetModelListResult/ModelList/ProductModel');
        if ($nodes)
        {
            foreach($nodes as $node)
            {
                return $this->extractPropertiesFromXml($node->ProductXmlStructure);
            }
        }

        return false;

    }

    protected function extractPropertiesFromXml($string)
    {
        $properties = array();

        $string = str_replace('xmlns=', 'ns=', $string);
        $string = str_replace('x:', '', $string);
        $xml = new SimpleXMLElement($string);
        $nodes = $xml->xpath('/Product/Product.ModelProperties/String');
        foreach ($nodes as $node)
        {
            $att = $node->attributes();
            $properties[] = array('type' => 'String', 'key' => (string)$att['Key']);
        }
        return $properties;
    }

    protected function getNodes($categoryCode, $path)
    {
        if (!$categoryCode)
            return false;

        if (isset($this->_cache[$categoryCode]))
            $res = $this->_cache[$categoryCode];
        else
            $res = Mage::helper('Cdiscount/Services')->getModelList($categoryCode);
        if (isset($res['content']))
        {
            $string = $res['content'];
            $string = str_replace('xmlns=', 'ns=', $string);

            $xml = new SimpleXMLElement($string);

            $nodes = $xml->xpath($path);
            return $nodes;
        }

        return false;
    }


}