<?php

/**
 * @category    Fishpig
 * @package     Fishpig_AttributeSplash
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
class Fishpig_AttributeSplash_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        echo 'Hello World';        die();
        
        $this->loadLayout();
        $this->renderLayout();
    }

}
