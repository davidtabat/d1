<?php

class Hackathon_MageMonitoring_Model_Widget_HealthCheck_Mediasize
    extends Hackathon_MageMonitoring_Model_Widget_Abstract
    implements Hackathon_MageMonitoring_Model_Widget
{
    // config key
    const CONFIG_ALLOW_SLOW_MODE = 'allow_slow_mode';
    // defaults
    protected $_DEF_ALLOW_SLOW_MODE = false;

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::getName()
     */
    public function getName()
    {
        return 'Media Size Check';
    }

    /**
     * (non-PHPdoc)
     * @see Hackathon_MageMonitoring_Model_Widget::initConfig()
     */
    public function initConfig()
    {
        $helper = Mage::helper('magemonitoring');

        parent::initConfig();
        // add config for slow mode
        $this->addConfig(self::CONFIG_ALLOW_SLOW_MODE,
                $helper->__('Enable slower php methods to collect dir size?'),
                $this->_DEF_ALLOW_SLOW_MODE,
                'widget',
                'checkbox',
                false,
                $helper->__('Warning! Depending on the size of your media directory this might take a very long time.')
        );
        return $this->_config;
    }

    protected function _getDirectorySize($path)
    {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        if ($handle = opendir ($path))
        {
            while (false !== ($file = readdir($handle)))
            {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link ($nextpath))
                {
                    if (is_dir ($nextpath))
                    {
                        $dircount++;
                        $result = $this->_getDirectorySize($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    }
                    elseif (is_file ($nextpath))
                    {
                        $totalsize += filesize ($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir ($handle);
        $total['size'] = $totalsize;
        $total['count'] = $totalcount;
        $total['dircount'] = $dircount;
        return $total;
    }

    public function getOutput()
    {
        $block = $this->newMultiBlock();
        /** @var Hackathon_MageMonitoring_Block_Widget_Multi_Renderer_Table $renderer */
        $renderer = $block->newContentRenderer('table');
        $helper = Mage::helper('magemonitoring');

        $header = array(
                $helper->__('Size'),
                $helper->__('Number Directories'),
                $helper->__('Number Files')
        );
        $renderer->setHeaderRow($header);

        $path = Mage::getBaseDir() . "/media";
        $dirSize = $helper->getTotalSize($path);

        if (!$dirSize && !$this->getConfig(self::CONFIG_ALLOW_SLOW_MODE)) {
            $block = $this->newMonitoringBlock();
            $os = strtoupper(substr(PHP_OS, 0, 3));
            $block->addRow('warning', $helper->__('This PHP instance only allows slow methods for collecting the size of a directory. Solutions:'));
            if ($os !== 'WIN') {
                $block->addRow('info', $helper->__('1. Remove popen from disable_functions in your php.ini or..'));
            } else {
                $block->addRow('info', $helper->__('1. Enable com_dotnet extension in your PHP instance or..'));
            }
            $block->addRow('info', $helper->__('2. Enable the much slower php way of collecting directory size by clicking on the gear icon of this widget.'));
            $block->addRow('warning', $helper->__('Solution 2 may take a very long time depending on your catalog size.'));
        } elseif (!$dirSize && $this->getConfig(self::CONFIG_ALLOW_SLOW_MODE)) {
            $dirSize = $this->_getDirectorySize($path);
            $row = array($helper->formatByteSize($dirSize['size']), $dirSize['count'], $dirSize['dircount']);
            $renderer->addRow($row);
        } else {
            $row = array($helper->formatByteSize($dirSize), '-', '-');
            $renderer->addRow($row);
        }

        $this->_output[] = $block;

        return $this->_output;
    }
}
