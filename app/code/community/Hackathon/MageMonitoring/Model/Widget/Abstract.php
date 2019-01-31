<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Hackathon
 * @package     Hackathon_MageMonitoring
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Hackathon_MageMonitoring_Model_Widget_Abstract
{
    // define config keys
    const CONFIG_START_COLLAPSED = 'collapsed';
    const CONFIG_DISPLAY_PRIO = 'display_prio';

    // watch dog config keys, only added if widget implements watchdog interface
    const CONFIG_WATCHDOG_ACTIVE = 'cron/enabled';
    const CONFIG_WATCHDOG_BARKON = 'cron/barkon';
    const CONFIG_WATCHDOG_CRON = 'cron/schedule';
    const CONFIG_WATCHDOG_MAILTO = 'cron/mail_to';

    // global watch dog config keys
    const CONFIG_DOGS_DISABLED = 'dogs/disabled';
    const CONFIG_DOGS_MAILTO = 'dogs/mail_to';

    // global default values
    protected $_DEF_START_COLLAPSED = false;
    protected $_DEF_DISPLAY_PRIO = 10;

    // watch dog defaults
    protected $_DEF_WATCHDOG_ACTIVE = true;
    protected $_DEF_WATCHDOG_BARKON = 'warning';
    protected $_DEF_WATCHDOG_CRON = '*/5 * * * *';
    protected $_DEF_WATCHDOG_MAILTO = null;

    // global watch dog defaults
    protected $_DEF_DOGS_DISABLED = 1;
    protected $_DEF_DOGS_MAILTO = 'general';

    // base node for all config keys
    const CONFIG_PRE_KEY = 'widgets';

    // callback marker
    const CALLBACK = 'cb:';

    protected $_dbConfigKey = null;
    protected $_tabId = null;
    protected $_output = array();
    protected $_config = array();
    protected $_report = array();

    /**
     * Returns unique widget id. You really don't want to override is. ;)
     *
     * @return string
     */
    public function getId()
    {
        return get_called_class();
    }

    /**
     * Returns db config key, returns last 2 parts of classname with appended random string as default.
     *
     * @return string
     */
    public function getConfigId()
    {
        if (!$this->_dbConfigKey) {
            $regOut = array();
            if (preg_match("/.*_(.*_.*)/", $this->getId(), $regOut)) {
                $this->_dbConfigKey = strtolower($regOut[1] .'_'. substr(md5(rand()), 0, 6));
            }
        }
        return $this->_dbConfigKey;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::isActive()
     */
    public function isActive()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::displayCollapsed()
     */
    public function displayCollapsed()
    {
        return $this->getConfig(self::CONFIG_START_COLLAPSED);
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::displayCollapsed()
     */
    public function getDisplayPrio()
    {
        return $this->getConfig(self::CONFIG_DISPLAY_PRIO);
    }

    /**
     * @return Hackathon_MageMonitoring_Block_Widget_Monitoring
     */
    public function newMonitoringBlock() {
        $b = Mage::app()->getLayout()->createBlock('magemonitoring/widget_monitoring');
        $b->setTabId($this->getTabId());
        $b->setWidgetId($this->getConfigId());
        return $b;
    }

    /**
     * @return Hackathon_MageMonitoring_Block_Widget_Multi
     */
    public function newMultiBlock() {
        $b = Mage::app()->getLayout()->createBlock('magemonitoring/widget_multi');
        $b->setTabId($this->getTabId());
        $b->setWidgetId($this->getConfigId());
        return $b;
    }

    /**
     * Adds $string to output.
     *
     * @param string $string
     * @return Hackathon_MageMonitoring_Model_Widget_Abstract
     */
    public function dump($string) {
        $this->_output[] = Mage::app()->getLayout()->createBlock('magemonitoring/widget_dump')->setOutput($string);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::initConfig()
     */
    public function initConfig()
    {
        $this->addConfigHeader('Widget Configuration');

        $this->addConfig(
            self::CONFIG_START_COLLAPSED,
            'Do not render widget on pageload?',
            $this->_DEF_START_COLLAPSED,
            'widget',
            'checkbox',
            false
        );

        $this->addConfig(
            self::CONFIG_DISPLAY_PRIO,
            'Display priority (0=top):',
            $this->_DEF_DISPLAY_PRIO,
            'widget',
            'text',
            false
        );

        if ($this instanceof Hackathon_MageMonitoring_Model_WatchDog) {
            // override watch dog default mail_to if global config is found
            $id = 'Hackathon_MageMonitoring_Model_Widget_System_Watchdog';
            $confKey = Mage::helper('magemonitoring')->getConfigKeyById(self::CONFIG_DOGS_MAILTO, $id);
            $defMail = Mage::getStoreConfig($confKey);
            if (!$defMail) {
                $defMail = $this->_DEF_DOGS_MAILTO;
            }
            $this->_DEF_WATCHDOG_MAILTO = $defMail;

            $this->addConfigHeader('Watch Dog Settings');
            $this->addConfig(
                    self::CONFIG_WATCHDOG_ACTIVE,
                    'Dog is on duty:',
                    $this->_DEF_WATCHDOG_ACTIVE,
                    'global',
                    'checkbox',
                    false
            );
            $this->addConfig(
                    self::CONFIG_WATCHDOG_CRON,
                    'Schedule:',
                    $this->_DEF_WATCHDOG_CRON,
                    'global',
                    'text',
                    false
            );
            $this->addConfig(
                    self::CONFIG_WATCHDOG_BARKON,
                    'Minimum bark level (warning|error):',
                    $this->_DEF_WATCHDOG_BARKON,
                    'global',
                    'text',
                    false
            );
            $this->addConfig(
                    self::CONFIG_WATCHDOG_MAILTO,
                    'Barks at:',
                    $this->_DEF_WATCHDOG_MAILTO,
                    'global',
                    'text',
                    false,
                    Mage::helper('magemonitoring')->__('Magento mail id (general, sales, etc) or valid email address.')
            );
        }

        return $this->_config;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::getConfig()
     */
    public function getConfig($config_key = null, $valueOnly = true)
    {
        if (empty($this->_config)) {
            $this->_config = $this->initConfig();
        }
        if ($config_key && array_key_exists($config_key, $this->_config)) {
            if ($valueOnly) {
                return $this->_config[$config_key]['value'];
            } else {
                return $this->_config[$config_key];
            }
        } else {
            if ($config_key) {
                return false;
            }
        }

        return $this->_config;
    }

    /**
     * Add empty or header row to config modal output.
     *
     * @param string $header
     * @return Hackathon_MageMonitoring_Model_Widget
     */
    public function addConfigHeader($header=null) {
        $this->_config[] = array('label' => $header);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::addConfig()
     */
    public function addConfig(
        $config_key,
        $label,
        $value,
        $scope = 'global',
        $inputType = 'text',
        $required = false,
        $tooltip = null
    ) {
        $this->_config[$config_key] = array(
            'scope' => $scope,
            'label' => $label,
            'value' => $value,
            'type' => $inputType,
            'required' => $required,
            'tooltip' => $tooltip
        );

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::loadConfig()
     */
    public function loadConfig($configKey = null, $tabId = null, $widgetDbId = null)
    {
        $config = array();
        $this->_tabId = $tabId;
        if ($widgetDbId !== null) {
            $this->_dbConfigKey = $widgetDbId;
        }
        if ($configKey) {
            $config[$configKey] = array('value' => null);
        } else {
            $config = $this->getConfig();
        }

        foreach ($config as $key => $conf) {
            $ck = Mage::helper('magemonitoring')->getConfigKey($key, $this);
            $value = Mage::getStoreConfig($ck);
            if ($value != null)
            {
                $this->_config[$key]['value'] = $value;
            }
        }
        return $this->_config;
    }

    /**
     * Save config in $post to core_config_data, can handle raw $_POST
     * or widget config arrays if $postOnly is true.
     *
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::saveConfig()
     */
    public function saveConfig($post, $postOnly = false)
    {
        $config = null;
        if (array_key_exists('widget_id', $post)) {
            $this->_dbConfigKey = $post['widget_id'];
        }
        if ($postOnly) {
            $config = $post;
        } else {
            $c = Mage::getModel('core/config');
            if (array_key_exists('class_name', $post)) {
                $c->saveConfig(
                        Mage::helper('magemonitoring')->getConfigKeyById('impl', $this->_dbConfigKey, 'tabs/'.$this->getTabId()),
                        $post['class_name'],
                        'default',
                        0
                );
            }
            $config = $this->getConfig();
        }
        foreach ($config as $key => $conf) {
            if (is_numeric($key)) continue; // skip header entries
            // handle checkbox states
            if (array_key_exists('type', $conf) && $conf['type'] == 'checkbox') {
                if (!array_key_exists($key, $post)) {
                    $post[$key] = 0;
                } else {
                    $post[$key] = 1;
                }
            }
            $value = null;
            if (array_key_exists($key, $post)) {
                if (!$postOnly) {
                    $value = $post[$key];
                } else {
                    $value = $post[$key]['value'];
                }
            }
            # @todo: batch save
            $c = Mage::getModel('core/config');
            $c->saveConfig(
                Mage::helper('magemonitoring')->getConfigKey($key, $this),
                $value,
                'default',
                0
            );
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::deleteConfig()
     */
    public function deleteConfig($tabId = null)
    {
        $this->_tabId = $tabId;
        foreach ($this->getConfig() as $key => $conf) {
            $c = Mage::getModel('core/config');
            $c->deleteConfig(
                Mage::helper('magemonitoring')->getConfigKey($key, $this),
                'default',
                0
            );
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_WatchDog::getDogId()
     */
    public function getDogId()
    {
        return $this->getId();
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_WatchDog::getDogName()
     */
    public function getDogName()
    {
        return $this->getName();
    }

    /**
     * @see Hackathon_MageMonitoring_Model_WatchDog::getSchedule()
     * @return string
     */
    public function getSchedule()
    {
        return $this->getConfig(self::CONFIG_WATCHDOG_CRON, true);
    }

    /**
     * @see Hackathon_MageMonitoring_Model_WatchDog::onDuty()
     * @return string
     */
    public function onDuty()
    {
        return $this->getConfig(self::CONFIG_WATCHDOG_ACTIVE, true);
    }

    /**
     * Adds another row to watch dog report output.
     *
     * Format of $attachments array:
     * array(array('filename' => $name, 'content' => $content), ...)
     *
     * @param string $css_id
     * @param string $label
     * @param string $value
     * @param array $attachments
     * @return void
     */
    public function addReportRow($css_id, $label, $value, $attachments=null)
    {
        $this->_report[] = array(
                'css_id' => $css_id,
                'label' => $label,
                'value' => $value,
                'attachments' => $attachments
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getTabId()
    {
        return $this->_tabId;
    }

    public function getVersion()
    {
        return '0.0.1';
    }

    /**
     * @see Hackathon_MageMonitoring_Model_Widget::getSupportedMagentoVersions()
     * @return string
     */
    public function getSupportedMagentoVersions() {
        return '*';
    }

    /**
     * @return bool
     */
    protected function _checkVersions()
    {
        if ($this->getSupportedMagentoVersions() === '*') {
            return true;
        }
        #TODO: do proper merge, things will go probably south for code below.
        $mageVersion = Mage::getVersion();

        /** @var Hackathon_MageMonitoring_Helper_Data $helper */
        $helper = Mage::helper('magemonitoring');

        // retrieve supported versions from config.xml
        $versions = $helper->extractVersions($this->getSupportedMagentoVersions());

        // iterate on versions to find a fitting one
        foreach ($versions as $_version) {
            $quotedVersion = preg_quote($_version);
            // build regular expression with wildcard to check magento version
            $pregExpr = '#\A' . str_replace('\*', '.*', $quotedVersion) . '\z#ims';

            if (preg_match($pregExpr, $mageVersion)) {
                return true;
            }
        }
        return false;
    }

}
