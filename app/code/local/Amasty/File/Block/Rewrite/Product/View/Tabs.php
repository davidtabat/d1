<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Rewrite_Product_View_Tabs extends Mage_Catalog_Block_Product_View_Tabs
{
    protected $_titles = array();
    protected $_tabsCache = false;

    /**
     * Add tab to the container
     *
     * @param string $title
     * @param string $block
     * @param string $template
     * @param string $siblingName
     * @param boolean $after
     */
    function addTab($alias, $title, $block, $template, $siblingName ='', $after = true)
    {
        if (!$title || !$block || !$template) {
            return false;
        }

        $this->_tabsCache = false;

        $this->_tabs[] = array(
            'alias' => $alias,
            'title' => $title
        );

        $this->_titles[$alias] = $title;

        $block = $this->getLayout()->createBlock($block, $alias)
            ->setTemplate($template);

        $this->insert(
            $block,
            $siblingName,
            $after,
            $alias
        );
    }

    function getTabs()
    {
        if ($this->_tabsCache === false) {
            $sortedChildren = $this->getSortedChildren();
            $this->_tabsCache = array();

            foreach ($sortedChildren as $alias) {
                if (isset($this->_titles[$alias])) {
                    $this->_tabsCache[] = array(
                        'alias' => $alias,
                        'title' => $this->_titles[$alias]
                    );
                }
            }
        }

        return $this->_tabsCache;
    }
}
