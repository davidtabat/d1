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
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
abstract class MDN_MarketPlace_Block_Index_Tab_Debug extends Mage_Adminhtml_Block_Widget {

    /* @var int */
    protected $_mp = null;

    /**
     * Constructor
     */
    public function __construct(){

        parent::__construct();
        $this->setHtmlId('debug');
        $this->setTemplate(ucfirst($this->getMp()).'/Index/Tab/Debug.phtml');

    }

    /**
     * Getter mp
     */
    abstract public function getMp();

    /**
     * Get url by account id
     *
     * @param string $path
     * @return string
     */
    public function getUrlByAccount($path, $params = array()){

        $params = array_merge($params, array('countryId' => Mage::registry('mp_country')->getId(), 'tab' => 'debug'));
        
        return $this->getUrl($path, $params);

    }

}

