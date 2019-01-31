<?php

class FME_Shipment_Block_Adminhtml_Sales_Shipment_Grid_Images extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = array();
        
        $entityId = $row->getData('order_increment_id');

        $order = Mage::getModel('sales/order')->load($entityId, 'increment_id');
        
        $images         = array();
        $productNames   = array();
        $Skus           = array();
        $visibleItems = $order->getAllVisibleItems();
        
        if (is_array($visibleItems)){
            foreach($visibleItems as $orderItem){
                $product    = $orderItem->getProduct();
                $Name       = $product->getName();
                $Sku        = $product->getSku();
                
                try{
                    if ($product && $product->getThumbnail() !== NULL && $product->getThumbnail() != 'no_selection' ){
                        try{
                            $productNames[]=$product->getName();
                            $Skus[]        = $product->getSku();
                            $images[]         ='<div style=" border-bottom: 1px solid rgb(204, 204, 204); width:100%;float: left; clear: both;">'.$product->getName(). '<br><img style="float: left; clear: both;" src="'. $product->getThumbnailUrl() .'"/><span style="float: left; clear: both;">'.$product->getSku().'</span></div>';
                            //$images[] = "<img src='". $product->getThumbnailUrl() ."'/>";
                        }  catch (Exception $e){
                            
                        }
                        
                    }
                } catch (Exception $e){
                    
                }

            }
        }
        
        if (count($images) > 0){
            $visibleSlided = 3;
            $widthImg = 75;
            $paddingImg = 2;
            $scrollerWidth = 40;
            
            $showCarousel = count($images) > $visibleSlided;
            $html[] ='
                <div class="carousel" id="carousel_'.$entityId.'" style="'.($showCarousel ? 'width: '.(($widthImg + $paddingImg)*$visibleSlided+$scrollerWidth).'px;' : '').'">
                    '.($showCarousel ? '
                        <a href="javascript:" class="carousel-control prev" rel="prev"><</a>
                        <a href="javascript:" class="carousel-control next"  rel="next">></a>
                    ' : '').'
                    <div class="am_middle" style="width: 100%; height:auto;">
                        <div class="am_inner" style="width: 100%;">
                            ' . implode('', $images) . '
                        </div>
                    </div>
                </div>
                '.($showCarousel ? '
                    <script>
                        new Carousel(
                            $(\'carousel_'.$entityId.'\').down(\'.am_middle\'), 
                            $(\'carousel_'.$entityId.'\').down(\'.am_inner\').select(\'img\'), 
                            $(\'carousel_'.$entityId.'\').select(\'a\'), {
                                    duration: 0.7,
                                    visibleSlides: '.$visibleSlided.'
                        });
                    </script>
                ' : '').'
                
            ';
        }
        
        return implode("<br/>", $html);
        
    }
}