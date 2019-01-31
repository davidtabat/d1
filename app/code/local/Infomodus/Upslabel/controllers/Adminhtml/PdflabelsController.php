<?php

/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

class Infomodus_Upslabel_Adminhtml_PdflabelsController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $ptype = $this->getRequest()->getParam('type');
        if ($ptype != 'lists') {
            $type = 'shipment';
            $order_ids = $this->getRequest()->getParam($ptype . '_ids');
            if ($ptype == 'creditmemo') {
                $ptype = 'shipment';
                $type = 'refund';
            }
            $resp = $this->create($order_ids, $type, $ptype);
        } else {
            $order_ids = $this->getRequest()->getParam('upslabel');
            $resp = $this->createFromLists($order_ids);
        }

        if (!$resp) {
            $this->_redirectReferer();
        }
    }

    public function onepdfAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        $type = $this->getRequest()->getParam('type');
        $img_path = Mage::getBaseDir('media') . '/upslabel/label/';
        $url_image_path = Mage::getBaseUrl('media') . 'upslabel/label/';
        $pdf = new Zend_Pdf();
        $i = 0;
        $collections = Mage::getModel('upslabel/upslabel');
        $colls = $collections->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
        foreach ($colls AS $k => $v) {
            $coll = $v['upslabel_id'];
            break;
        }
        $collection_one = Mage::getModel('upslabel/upslabel')->load($coll);
        
        $width = trim(Mage::getStoreConfig('upslabel/printing/dimensionx')) > 0 ? trim(Mage::getStoreConfig('upslabel/printing/dimensionx')) : 1400 / 2.04;
        $height = trim(Mage::getStoreConfig('upslabel/printing/dimensiony')) > 0 ? trim(Mage::getStoreConfig('upslabel/printing/dimensiony')) : 800 / 2.04;
        if ($collection_one->getOrderId() == $order_id) {
            foreach ($colls AS $collection) {
                if (file_exists($img_path . $collection->getLabelname()) && filesize($img_path . $collection->getLabelname()) > 512) {
                    if ($collection->getTypePrint() == "GIF") {
                        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                        $pdf->pages[] = $page;
                        $f_cont = file_get_contents($img_path . $collection->getLabelname());
                        $img = imagecreatefromstring($f_cont);
                        if (Mage::getStoreConfig('upslabel/printing/verticalprint') == 1) {
                            $col = imagecolorallocate($img, 125, 174, 240);
                            $IMGfuul = imagerotate($img, -90, $col);
                        } else {
                            $IMGfuul = $img;
                        }
                        $rnd = rand(10000, 999999);
                        imagejpeg($IMGfuul, $img_path . 'lbl' . $rnd . '.jpeg', 100);
                        $image = Zend_Pdf_Image::imageWithPath($img_path . 'lbl' . $rnd . '.jpeg');
                        $page->drawImage($image, 50, 800 - $height, $width + 50, 800);
                        unlink($img_path . 'lbl' . $rnd . '.jpeg');
                        $i++;
                        $collection->setRvaPrinted(1);
                        $collection->save();
                    }
                }
            }
        }
        if ($i > 0) {
            $pdfData = $pdf->render();
            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf");
            echo $pdfData;
        }
    }

    static public function create($order_ids, $type, $ptype)
    {
        $img_path = Mage::getBaseDir('media') . '/upslabel/label/';
        $pdf = new Zend_Pdf();
        $i = 0;
        //$pdf->pages = array_reverse($pdf->pages);
        if (!is_array($order_ids)) {
            $order_ids = explode(',', $order_ids);
        }

        $configModuleNode = Mage::getConfig()->getNode('default/upslabel/myoption/multistore/active');
        foreach ($order_ids as $order_id) {
            
            $width = trim(Mage::getStoreConfig('upslabel/printing/dimensionx')) > 0 ? trim(Mage::getStoreConfig('upslabel/printing/dimensionx')) : 1400 / 2.04;
            $height = trim(Mage::getStoreConfig('upslabel/printing/dimensiony')) > 0 ? trim(Mage::getStoreConfig('upslabel/printing/dimensiony')) : 800 / 2.04;
            $collections = Mage::getModel('upslabel/upslabel');
            $colls = $collections->getCollection()->addFieldToFilter($ptype . '_id', $order_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
            if (Mage::getStoreConfig('upslabel/printing/bulk_printing_all') == 1) {
                $colls->addFieldToFilter('rva_printed', 0);
            }
            if ($colls) {
                foreach ($colls AS $k => $v) {
                    $coll = $v['upslabel_id'];
                    $collection = Mage::getModel('upslabel/upslabel')->load($coll);
                    if (($collection->getOrderId() == $order_id && $ptype == "order") || ($collection->getShipmentId() == $order_id && $ptype != "order")) {
                        if (file_exists($img_path . $collection->getLabelname()) && filesize($img_path . $collection->getLabelname()) > 1024) {
                            if ($collection->getTypePrint() == "GIF") {
                                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                                $pdf->pages[] = $page;
                                $f_cont = file_get_contents($img_path . $collection->getLabelname());
                                $img = imagecreatefromstring($f_cont);
                                if (Mage::getStoreConfig('upslabel/printing/verticalprint') == 1) {
                                    $col = imagecolorallocate($img, 125, 174, 240);
                                    $IMGfuul = imagerotate($img, -90, $col);
                                } else {
                                    $IMGfuul = $img;
                                }
                                $rnd = rand(10000, 999999);
                                imagejpeg($IMGfuul, $img_path . 'lbl' . $rnd . '.jpeg', 100);
                                $image = Zend_Pdf_Image::imageWithPath($img_path . 'lbl' . $rnd . '.jpeg');
                                $page->drawImage($image, 50, 800 - $height, $width + 50, 800);
                                unlink($img_path . 'lbl' . $rnd . '.jpeg');
                                $i++;
                            } else {
                                $data = file_get_contents($img_path . $collection->getLabelname());
                                Mage::helper('upslabel/help')->sendPrint($data);
                            }
                            $collection->setRvaPrinted(1);
                            $collection->save();
                        }
                    }
                    unset($IMGfuul);
                }
            }
        }
        //$pdf->save();
        if ($i > 0) {
            $pdfData = $pdf->render();
            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf");
            echo $pdfData;
            return true;
        } else {
            return false;
        }
    }

    static public function createFromLists($order_ids)
    {
        $img_path = Mage::getBaseDir('media') . '/upslabel/label/';
        $pdf = new Zend_Pdf();
        $i = 0;
        //$pdf->pages = array_reverse($pdf->pages);
        if (!is_array($order_ids)) {
            $order_ids = explode(',', $order_ids);
        }
        $configModuleNode = Mage::getConfig()->getNode('default/upslabel/myoption/multistore/active');
        foreach ($order_ids as $order_id) {
            
            $width = trim(Mage::getStoreConfig('upslabel/printing/dimensionx')) > 0 ? trim(Mage::getStoreConfig('upslabel/printing/dimensionx')) : 1400 / 2.04;
            $height = trim(Mage::getStoreConfig('upslabel/printing/dimensiony')) > 0 ? trim(Mage::getStoreConfig('upslabel/printing/dimensiony')) : 800 / 2.04;
            $collection = Mage::getModel('upslabel/upslabel')->load($order_id);
            if ($collection && $collection->getStatus() == 0) {
                if (file_exists($img_path . $collection->getLabelname()) && filesize($img_path . $collection->getLabelname()) > 1024) {
                    if ($collection->getTypePrint() == "GIF") {
                        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                        $pdf->pages[] = $page;
                        $f_cont = file_get_contents($img_path . $collection->getLabelname());
                        $img = imagecreatefromstring($f_cont);
                        if (Mage::getStoreConfig('upslabel/printing/verticalprint') == 1) {
                            $col = imagecolorallocate($img, 125, 174, 240);
                            $IMGfuul = imagerotate($img, -90, $col);
                        } else {
                            $IMGfuul = $img;
                        }
                        $rnd = rand(10000, 999999);
                        imagejpeg($IMGfuul, $img_path . 'lbl' . $rnd . '.jpeg', 100);
                        $image = Zend_Pdf_Image::imageWithPath($img_path . 'lbl' . $rnd . '.jpeg');
                        $page->drawImage($image, 50, 800 - $height, $width + 50, 800);
                        unlink($img_path . 'lbl' . $rnd . '.jpeg');
                        $i++;
                    } else {
                        $data = file_get_contents($img_path . $collection->getLabelname());
                        Mage::helper('upslabel/help')->sendPrint($data);
                    }
                    $collection->setRvaPrinted(1);
                    $collection->save();
                }
                unset($IMGfuul);
            }
        }
        //$pdf->save();
        if ($i > 0) {
            $pdfData = $pdf->render();
            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf");
            echo $pdfData;
            return true;
        } else {
            return false;
        }
    }
}
