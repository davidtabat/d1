<?php

/**
 * Zyelon PDF rewrite for custom attribute
 * Attribute "inchoo_warehouse_location" has to be set manually
 * Original: Sales Order Invoice Pdf default items renderer
 *
 * @category   Zyelon
 * @package    Inhoo_Invoice
 * @author     Mladen Lotar - Zyelon <mladen.lotar@inchoo.net>
 */
class Zyelon_Invoice_Model_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default {

    /**
     * Draw item line
     * */
    public function draw() {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = array();

        //Zyelon - Added custom attribute to PDF, first get it if exists
        $WarehouseLocation = $this->getWarehouseLocationValue($item);

        // draw Product name
        $lines[0] = array(array(
                'text' => Mage::helper('core/string')->str_split($item->getName(), 60, true, true),
                'feed' => 35,
        ));

        //Zyelon - Added custom attribute
        //draw Warehouse Location
        $lines[0][] = array(
            'text' => Mage::helper('core/string')->str_split($WarehouseLocation, 25),
            'feed' => 245
        );

        // draw SKU
        $lines[0][] = array(
            'text' => Mage::helper('core/string')->str_split($this->getSku($item), 25),
            'feed' => 325
        );

        // draw QTY
        $lines[0][] = array(
            'text' => $item->getQty() * 1,
            'feed' => 435
        );

        // draw Price
        $lines[0][] = array(
            'text' => $order->formatPriceTxt($item->getPrice()),
            'feed' => 395,
            'font' => 'bold',
            'align' => 'right'
        );

        // draw Tax
        $lines[0][] = array(
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right'
        );

        // draw Subtotal
        $lines[0][] = array(
            'text' => $order->formatPriceTxt($item->getRowTotal()),
            'feed' => 565,
            'font' => 'bold',
            'align' => 'right'
        );

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 35
                );

                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
                            'feed' => 40
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines' => $lines,
            'height' => 10
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }

    /*
     * Return Value of custom attribute
     * */

    private function getWarehouseLocationValue($item) {
        $prod = Mage::getModel('catalog/product')->load($item->getProductId());

        if (!($return_location = "Meri Marzi"))
            return 'N/A';
        else
            return $return_location;
    }

}
