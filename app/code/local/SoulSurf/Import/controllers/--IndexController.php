<?php
class SoulSurf_Import_IndexController extends Mage_Core_Controller_Front_Action {

    public $xml;
    public $username;
    public $password;
    public $post_username;
    public $post_password;
    public $return_xml;
    public $pages_action;
    public $pages_type = array();
    public $pages_title = array();
    public $pages_text = array();
    public $pages_html = array();
    public $pages_pdf = array();
	public $pdf_md5;
    public $pages_language = array();
    

    protected function indexAction() {
         
        header('Content-type: application/xml; charset=utf-8');

        $this -> username = strtolower(trim(Mage::getStoreConfig('csvimport/general/username')));
        $this -> password = md5(strtolower(Mage::getStoreConfig('csvimport/general/password')));
        
       /* XML einlesen */
        $this -> parseXML();

        /* falls XML ok weitermachen*/
        if($this->checkXML()){            
            if ($this -> authentificate()) {    
                $this -> createStaticPage();
            } else {
                die($this -> return_xml);
            }
        }
        else{
             die($this -> return_xml);
        }
    }

    protected function authentificate() {
		$mage = new Mage;
		$version = $mage->getVersion();
		$versionXML = '<meta_shopversion>' . $version . '</meta_shopversion><meta_modulversion>1.0</meta_modulversion>';	
	
	
        if ($this -> username == $this -> post_username && $this -> password == $this -> post_password) {
            $this -> return_xml = '<?xml version="1.0" encoding="UTF-8" ?> <response>
                <status>success</status>' . $versionXML  . '</response>';
            return 1;
        } else {$this -> return_xml = '<?xml version="1.0" encoding="UTF-8" ?> <response>
                <status>error</status>
                <error>99</error>' . $versionXML . '</response>';
            return 0;
        }

    }

    protected function parseXML() {
        
		$mage = new Mage;
		$version = $mage->getVersion();
		$versionXML = '<meta_shopversion>' . $version . '</meta_shopversion><meta_modulversion>1.0</meta_modulversion>';			
		
        $xml = Mage::app()->getFrontController()->getRequest()->getParam('xml', false);
         
        $xml = simplexml_load_string($xml);
        
        if(empty($xml)){            
           die('<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>12</error>' . $versionXML . '</response>');
        }
     
        $this -> post_username = (string)$xml -> user_username;
        $this -> post_password = strtolower($xml -> user_password);
        $this -> pages_action = strtolower($xml -> action);
		$this->pdf_md5 = (string) $xml -> rechtstext_pdf_md5hash;
        array_push($this -> pages_type, (string)$xml -> rechtstext_type);
        array_push($this -> pages_title, strtoupper((string)$xml -> rechtstext_type));
        array_push($this -> pages_html, (string)$xml -> rechtstext_html);
        array_push($this -> pages_text, (string)$xml -> rechtstext_text);
        array_push($this -> pages_pdf, (string) $xml -> rechtstext_pdf_url);
        array_push($this -> pages_language, (string)$xml -> rechtstext_language);
       

    }
    
    protected function checkXML(){
        $error_message = '';
       
		$mage = new Mage;
		$version = $mage->getVersion();
		$versionXML = '<meta_shopversion>' . $version . '</meta_shopversion><meta_modulversion>1.0</meta_modulversion>';
	   
        if(empty($this->post_password) || empty($this->post_username)){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>3</error>' . $versionXML . '</response>';
        }
        else if($this->post_password != $this -> password || $this->post_username != $this -> username){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>3</error>' . $versionXML . '</response>';
        }
        else if(empty($this->pages_type[0]) || !in_array($this->pages_type[0], array('agb', 'datenschutz','widerruf','impressum'))){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>4</error>' . $versionXML . '</response>';
        }
        else if(empty($this->pages_text[0]) || strlen($this->pages_text[0]) < 50){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>5</error>' . $versionXML . '</response>';
        }        
        else if(empty($this->pages_html[0]) || strlen($this->pages_html[0]) < 50){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>6</error>' . $versionXML . '</response>';
        } 
         else if(empty($this->pages_language[0]) || $this->pages_language[0] != 'de'){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>9</error>' . $versionXML . '</response>';
        }      		
        else if(!$this->remote_file_exists($this->pages_pdf[0]) && in_array($this->pages_type[0], array('agb'))) {       	    	
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>7</error>' . $versionXML . '</response>';
        } 
        else if(empty($this->pages_action) || $this->pages_action != 'push'){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>10</error>' . $versionXML . '</response>';
        }  
        else if(empty($this->pdf_md5)){
            $error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>8</error>' . $versionXML . '</response>';
        } 	
		else if(!$this->is_pdf($this->pages_pdf[0])){		
			$error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>7</error>' . $versionXML . '</response>';
		} else if($this->pdf_md5 != md5_file($this->pages_pdf[0])){	
			$error_message = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>error</status><error>8</error>' . $versionXML . '</response>';		
		}
		
              
        $this->return_xml = $error_message;
        return (empty($this->return_xml)) ? 1 : 0;
        
    }

	function is_pdf($url) {
		$contents 	= file_get_contents($url);
		$pos 		= stripos($contents, '%PDF');
		if ($pos !== false) {
			return true;
		}
		return false;
	}
	
	
    function remote_file_exists($url){   	
       return (bool)preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
    }  

    protected function copyRemotePDF(){
        $remote_file_contents = file_get_contents($this->pages_pdf[0]);
		
		Mage::Log($this->pages_pdf[0]);
        $local_file_path = Mage::getBaseDir('media'). '/AGB.pdf';
        file_put_contents($local_file_path, $remote_file_contents);  
    }
    protected function createStaticPage() {
	
		$mage = new Mage;
		$version = $mage->getVersion();
		$versionXML = '<meta_shopversion>' . $version . '</meta_shopversion><meta_modulversion>1.0</meta_modulversion>';	
	
        /* Kopiere PDF */    
        $this->copyRemotePDF();
        
        $count = count($this -> pages_type);
        Mage::app() -> setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

       for ($i = 0; $i < $count; $i++) {
            $pageTitle =  Mage::getModel('cms/page')->setStore(Mage::app()->getStore()->getId())->load( $this -> pages_type[$i], 'identifier');
            if(!isset( $pageTitle['page_id'])){              
                $cmsPage = array('title' => $this -> pages_title[$i], 'identifier' => $this -> pages_type[$i], 'content' => $this -> pages_html[$i], 'is_active' => 1, 'sort_order' => 0, 'root_template' => 'one_column');
                try {
                    Mage::getModel('cms/page') -> setData($cmsPage) -> save();
                    die($this -> return_xml);
                } catch(Exception $e) {
                    echo $e;
                }
            }
            else{
                /* Update der vorhandenen Seite */
                 $cms_info = Mage::getModel('cms/page') ->setStoreId(Mage::app()->getStore()->getStoreId())->getCollection(); 
                 foreach($cms_info as $cms) { 
                    if ($cms->getIdentifier() == $this -> pages_type[0]) { 
                        $cms->setContent($this -> pages_html[0]); 
                        $cms->setIsActive(true);
                        $cms->setStoreId(Mage::app()->getStore()->getStoreId());
                        $cms->save();                            
                        Mage::app()->cleanCache();
                        $this->return_xml = '<?xml version="1.0" encoding="UTF-8" ?> <response><status>success</status>' . $versionXML . '</response>';
                        die($this->return_xml);
                    }
                 }           
                
            }
        }
    }

}
?>