<?php

class Jay_Producttitles_Model_Observer {
	/**
     * Change product meta title on product view
     *
     * @pram Varien_Event_Observer $observer
     * @return Jay_Producttitles_Model_Observer
     */

	public function catalog_controller_product_view(Varien_Event_Observer $observer)
    {
    	if ($product = $observer->getEvent()->getProduct()) {
    		
    		$title = $product->getData('name');
    		$metaDescription = $product->getMetaDescription();

	    	switch (Mage::app()->getStore()->getCode()) {
	    		case 'kopiererhaus':
	    			$title = $product->getData('name') . " | Günstig bei Kopiererhaus.de";
	    			$metaDescription = $product->getData('name') . " beim Profi für gebrauchte Drucker und Kopierer: ✓ Aufbereitet inkl. 12 Monate Gewährleistung ✓ Geprüfte Qualität ✓ Sicher + Günstig";
	    			break;
	    		
	    		case 'druckerhaus24':
	    			$title = $product->getData('name') . " | Druckerhaus24";
	    			$metaDescription = $product->getData('name') . " ✓ Gratis-Kaufberatung ✓ Bürogeräte bei Druckerhaus24 ✓ Markenware zu Top-Preisen ✓ Jetzt bestellen!";
	    			break;

                case 'imprireco_gmbh':
                    $title = $product->getData('name') . " | ImpriReco";
                    $metaDescription = $product->getData('name') . " Nous vous proposons des imprimantes d´occasion révisées par des professionnels, garanties 12 mois, à prix très intéressants !";
                    break;
	    	}	
    	
            $product->setMetaTitle($title);
            $product->setMetaDescription($metaDescription);
        }
        return $this;
    }

}
