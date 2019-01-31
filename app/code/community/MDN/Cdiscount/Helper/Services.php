<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Cdiscount_Helper_Services extends Mage_Core_Helper_Abstract {

    private $_productionUrl = 'https://wsvc.cdiscount.com/MarketplaceAPIService.svc';
    private $_sandboxUrl = 'https://wsvc.preprod-cdiscount.com/MarketplaceAPIService.svc';

    const kGetProductList = 'GetProductList';
    const kSubmitProductPackage = 'SubmitProductPackage';
    const kGetProductPackageSubmissionResult = 'GetProductPackageSubmissionResult';
    const kGetAllowedCategoryTree = 'GetAllowedCategoryTree';
    const kGetAllAllowedCategoryTree = 'GetAllAllowedCategoryTree';
    const kGetModelList = 'GetModelList';
    const kGetAllModelList = 'GetAllModelList';
	const kGetBrandList = 'GetBrandList';
    const kSubmitOfferFeed = 'SubmitOfferFeed';
    const kSubmitOfferPackage = 'SubmitOfferPackage';
    const kGetOfferPackageSubmissionResult = 'GetOfferPackageSubmissionResult';
    const kGetOfferList = 'GetOfferList';
    const kGetOfferListPaginated = 'GetOfferListPaginated';
    const kGetSellerInformation = 'GetSellerInformation';
    const kValidateOrderList = 'ValidateOrderList';
    const kGetOrderList = 'GetOrderList';

    const kResponseTypeFile = 'file';
    const kResponseTypeMessage = 'message';

    protected $_params = array();
    private $_requestType = '';

    public function setRequestType($value){
        $this->_requestType = $value;
    }

    public function getRequestType(){
        return $this->_requestType;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getSandboxUrl(){
        return $this->_sandboxUrl;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getProductionUrl(){
        return $this->_productionUrl;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl(){

        $url = '';

        $country = Mage::registry('mp_country');
        if (!$country)
            $country = Mage::helper('Cdiscount')->getDefaultCountry();

        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        if($account->getParam('use_sandbox') == 1)
            $url = $this->getSandboxUrl();
        else
            $url = $this->getProductionUrl();

        return $url;

    }

    /**
     * Get operations which required feed
     *
     * @return array
     */
    public function getOperationWithFeed(){
        return array(
            self::kSubmitOfferFeed => self::kSubmitOfferFeed
        );
    }

    /**
     * API call
     *
     * @param string $operation
     * @param string $feed
     * @return string
     */
    protected function _call($operation, $feed = null){

        Mage::helper('Cdiscount')->magentoLog('BEGIN service '.$this->getUrl().' for operation '.$operation);

        $client = new Zend_Http_Client($this->getUrl());
        $client->setHeaders(array(
            'Content-Type:text/xml;charset=UTF-8',
            'SOAPAction:http://www.cdiscount.com/IMarketplaceAPIService/'.$operation
        ));

        if($feed !== null)
            $client->setRawData($feed);

        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $db->closeConnection();

        $response = $client->request('POST');

       // echo $client->getLastRequest();
        Mage::helper('Cdiscount')->magentoLog('RESPONSE service '.$this->getUrl().' for operation '.$operation);

        $db->getConnection();


        // ADD log and save feed
        $log = Mage::getModel('MarketPlace/Feed')
                        ->setmp_feed_id('-')
                        ->setmp_date(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()))
                        ->setmp_type($this->getRequestType())
                        ->setmp_marketplace_id(Mage::helper('Cdiscount')->getMarketPlaceName())
                        ->setmp_content($feed)
                        ->setmp_response($response->getBody())
                        ->setmp_status(MDN_MarketPlace_Helper_Feed::kFeedSubmitted)
                        ->setmp_country(Mage::helper('Cdiscount')->getDefaultCountry()->getId())
                        ->save();

        //check response
        $xml = new DomDocument();
        $xml->loadXML($response->getBody());

        if($xml->getElementsByTagName('faultcode')->item(0)){
            $log->setmp_status(MDN_MarketPlace_Helper_Feed::kFeedError)
                ->save();
            throw new Exception($xml->getElementsByTagName('faultstring')->item(0)->nodeValue);
        }

        if($xml->getElementsByTagName('OperationSuccess')->item(0)){
            $result = $xml->getElementsByTagName('OperationSuccess')->item(0)->nodeValue;

            if($result == 'false'){

                $log->setmp_status(MDN_MarketPlace_Helper_Feed::kFeedError)
                        ->save();

                $message = $xml->getElementsByTagName('Message')->item(0)->nodeValue;

            }
        }

        if($xml->getElementsByTagName('PackageId')->item(0)){

            $log->setmp_feed_id($xml->getElementsByTagName('PackageId')->item(0)->nodeValue)
                    ->save();

        }

        Mage::helper('Cdiscount')->magentoLog('END service '.$this->getUrl().' for operation '.$operation);

        return $response->getBody();

    }

    /**
     * Get Feed type by operation
     *
     * @param string $operation
     * @return string
     */
   /* protected function _getTypeByOperation($operation){

         $mp_types = array(
            self::kGetProductList => MDN_Cdiscount_Helper_Feed::kFeedTypeMatchingProducts,
            self::kSubmitProductPackage => MDN_Cdiscount_Helper_Feed::kFeedTypeProductCreation,
            self::kGetProductPackageSubmissionResult => MDN_Cdiscount_Helper_Feed::kFeedTypeSubmissionResultProductCreation,
            self::kSubmitOfferPackage => MDN_Cdiscount_Helper_Feed::kFeedTypeProductCreation,
            self::kGetOfferPackageSubmissionResult => MDN_Cdiscount_Helper_Feed::kFeedTypeSubmissionResultProductCreation,
            self::kGetOfferList => MDN_Cdiscount_Helper_Feed::kFeedTypeMatchingProducts,
            self::kGetOfferListPaginated => MDN_Cdiscount_Helper_Feed::kFeedTypeMatchingProducts,
            self::kValidateOrderList => MDN_Cdiscount_Helper_Feed::kFeedTypeAcceptOrders,
            self::kGetOrderList => MDN_Cdiscount_Helper_Feed::kFeedTypeImportOrders,
            self::kGetAllowedCategoryTree => MDN_Cdiscount_Helper_Feed::kFeedTypeGetAllowedCategoryTree,
            self::kGetAllAllowedCategoryTree => MDN_Cdiscount_Helper_Feed::kFeedTypeGetAllowedCategoryTree,
            self::kGetModelList => MDN_Cdiscount_Helper_Feed::kFeedTypeGetModelList,
            self::kGetAllModelList => MDN_Cdiscount_Helper_Feed::kFeedTypeGetAllModelList,
            self::kGetSellerInformation => MDN_Cdiscount_Helper_Feed::kFeedTypeGetSellerInformation
         );

         return (array_key_exists($operation, $mp_types) ? $mp_types[$operation] : 'unknow');
    }*/

    //-----------------------------
    // Operations sur les produits
    //-----------------------------

    /**
     * Get product list
     *
     * @param string $filter
     * @return string
     */
    public function getProductList($filter = null){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetProductList xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                    <productFilter/>
                </GetProductList>
            </s:Body>
        </s:Envelope>';

        $response = $this->_call(self::kGetProductList,$feed);

        return array('type'=>self::kResponseTypeFile, 'content' => $response);

    }

    /**
     * submit product package
     */
    public function submitProductPackage($package_url){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
                    <s:Body>
                        <SubmitProductPackage xmlns="http://www.cdiscount.com">
                            <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <a:Context>
                                    <a:CatalogID>1</a:CatalogID>
                                    <a:CustomerPoolID>1</a:CustomerPoolID>
                                    <a:SiteID>100</a:SiteID>
                                </a:Context>
                                <a:Localization>
                                    <a:Country>Fr</a:Country>
                                    <a:Currency>Eur</a:Currency>
                                    <a:DecimalPosition>2</a:DecimalPosition>
                                    <a:Language>Fr</a:Language>
                                </a:Localization>
                                <a:Security>
                                    <a:DomainRightsList i:nil="true" />
                                    <a:IssuerID i:nil="true" />
                                    <a:SessionID i:nil="true" />
                                    <a:SubjectLocality i:nil="true" />
                                    <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                                    <a:UserName i:nil="true" />
                                </a:Security>
                                <a:Version>1.0</a:Version>
                            </headerMessage>
                            <productPackageRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <ZipFileFullPath>'.$package_url.'file.zip</ZipFileFullPath>
                            </productPackageRequest>
                        </SubmitProductPackage>
                    </s:Body>
                </s:Envelope>';

        $response = $this->_call(self::kSubmitProductPackage, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => '');

    }

    /**
     * Get product package submission result
     */
    public function getProductPackageSubmissionResult($packageId){


        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
                    <s:Body>
                        <GetProductPackageSubmissionResult xmlns="http://www.cdiscount.com">
                            <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <a:Context>
                                    <a:CatalogID>1</a:CatalogID>
                                    <a:CustomerPoolID>1</a:CustomerPoolID>
                                    <a:SiteID>100</a:SiteID>
                                </a:Context>
                                <a:Localization>
                                    <a:Country>Fr</a:Country>
                                    <a:Currency>Eur</a:Currency>
                                    <a:DecimalPosition>2</a:DecimalPosition>
                                    <a:Language>Fr</a:Language>
                                </a:Localization>
                                <a:Security>
                                    <a:DomainRightsList i:nil="true" />
                                    <a:IssuerID i:nil="true" />
                                    <a:SessionID i:nil="true" />
                                    <a:SubjectLocality i:nil="true" />
                                    <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                                    <a:UserName i:nil="true" />
                                </a:Security>
                                <a:Version>1.0</a:Version>
                            </headerMessage>
                            <productPackageFilter xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <PackageID>'.$packageId.'</PackageID>
                            </productPackageFilter>
                        </GetProductPackageSubmissionResult>
                    </s:Body>
                </s:Envelope>';

        $response = $this->_call(self::kGetProductPackageSubmissionResult, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);
    }

    /**
     * get allowed category tree
     */
    public function getAllowedCategoryTree(){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetAllowedCategoryTree xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                </GetAllowedCategoryTree>
            </s:Body>
        </s:Envelope>';

        $response = $this->_call(self::kGetAllowedCategoryTree, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);

    }

    /**
     * get all allowed category tree
     */
    public function getAllAllowedCategoryTree(){

         $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetAllAllowedCategoryTree xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                </GetAllAllowedCategoryTree>
            </s:Body>
        </s:Envelope>';

        $response = $this->_call(self::kGetAllAllowedCategoryTree, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);

    }

    /**
     * get model list
     */
    public function getModelList($categoryId = null){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetModelList xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                    <modelFilter xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <CategoryCodeList xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
                            <a:string>'.$categoryId.'</a:string>
                        </CategoryCodeList>
                    </modelFilter>
                </GetModelList>
            </s:Body>
        </s:Envelope>';

        $response = $this->_call(self::kGetModelList,$feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);

    }

    /**
     * get all model list
     */
    public function getAllModelList(){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetAllModelList xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                </GetAllModelList>
            </s:Body>
        </s:Envelope>';
        
        $response = $this->_call(self::kGetAllModelList,$feed);

        return array('type'=>self::kResponseTypeFile, 'content' => $response);

    }

    //--------------------------
    // operations sur les offres
    //--------------------------

    /**
     * submit offer : product update + product creation (up to 100, after use package)
     * pas encore implémenté côté cdiscount
     */
    public function submitOfferFeed($feed){

        //$response = $this->_call(self::kSubmitOfferFeed, $feed);

        //return array('type'=>self::kResponseTypeMessage, 'message' => $response);

    }

    /**
     * submit offer package
     */
    public function submitOfferPackage($package_url){

        $token = Mage::Helper('Cdiscount/Auth')->getToken();
        
        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <SubmitOfferPackage xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.$token.'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                    <offerPackageRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <ZipFileFullPath>' . $package_url . '</ZipFileFullPath>
                    </offerPackageRequest>
                </SubmitOfferPackage>
            </s:Body>
        </s:Envelope>';

        // log submission
        Mage::getModel('Cdiscount/TokenHistory')->add(
                $token,
                $package_url
        );
        
        $response = $this->_call(self::kSubmitOfferPackage, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => '');

    }

    /**
     * get offer package submission result
     */
    public function getOfferPackageSubmissionResult($package_id){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
                    <s:Body>
                        <GetOfferPackageSubmissionResult xmlns="http://www.cdiscount.com">
                            <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <a:Context>
                                    <a:CatalogID>1</a:CatalogID>
                                    <a:CustomerPoolID>1</a:CustomerPoolID>
                                    <a:SiteID>100</a:SiteID>
                                </a:Context>
                                <a:Localization>
                                    <a:Country>Fr</a:Country>
                                    <a:Currency>Eur</a:Currency>
                                    <a:DecimalPosition>2</a:DecimalPosition>
                                    <a:Language>Fr</a:Language>
                                </a:Localization>
                                <a:Security>
                                    <a:DomainRightsList i:nil="true" />
                                    <a:IssuerID i:nil="true" />
                                    <a:SessionID i:nil="true" />
                                    <a:SubjectLocality i:nil="true" />
                                    <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                                    <a:UserName i:nil="true" />
                                </a:Security>
                                <a:Version>1.0</a:Version>
                            </headerMessage>
                            <offerPackageFilter xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <PackageID>'.$package_id.'</PackageID>
                            </offerPackageFilter>
                        </GetOfferPackageSubmissionResult>
                    </s:Body>
                </s:Envelope>';

        $response = $this->_call(self::kGetOfferPackageSubmissionResult, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);


    }

    /**
     * get offer list
     */
    public function getOfferList(){

        $feed = '<s:Envelope xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Header/>
            <s:Body>
                <GetOfferList xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                    <offerFilter/>
                </GetOfferList>
            </s:Body>
        </s:Envelope>';
        
        $response = $this->_call(self::kGetOfferList, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);

    }

    /**
     * get offer list paginated
     */
    public function getOfferListPaginated($page, $filters = array()){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetOfferListPaginated xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                    <offerFilter>
                        <!-- Optionnel : Valeurs possibles: NewOffersOnly, UsedOffersOnly. Si non précisé, toutes les offres sont remontées -->
                        <!--<OfferFilterCriterion>NewOffersOnly</OfferFilterCriterion>-->
                        <!-- Optionnel : Valeurs possibles: BySoldQuantityDescending, ByPriceAscending, ByPriceDescending, ByCreationDateDescending -->
                        <!--<OfferSortOrder>BySoldQuantityDescending</OfferSortOrder>-->
                        <!-- Obligatoire -->
                        <PageNumber>'.$page.'</PageNumber>
                    </offerFilter>
                </GetOfferListPaginated>
            </s:Body>
        </s:Envelope>';

        $response = $this->_call(self::kGetOfferListPaginated, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);

    }

    //-----------------------------
    // operation sur les vendeurs
    //-----------------------------

    /**
     * get seller information
     */
    public function getSellerInformation(){

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetSellerInformation xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                </GetSellerInformation>
            </s:Body>
        </s:Envelope>';

        $response = $this->_call(self::kGetSellerInformation, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);

    }

    //------------------------------
    // operation sur les commandes
    //------------------------------

    /**
     * validate order list
     */
    public function validateOrderList($orders){        

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
                    <s:Body>
                        <ValidateOrderList xmlns="http://www.cdiscount.com">
                            <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <a:Context>
                                    <a:CatalogID>1</a:CatalogID>
                                    <a:CustomerPoolID>1</a:CustomerPoolID>
                                    <a:SiteID>100</a:SiteID>
                                </a:Context>
                                <a:Localization>
                                    <a:Country>Fr</a:Country>
                                    <a:Currency>Eur</a:Currency>
                                    <a:DecimalPosition>2</a:DecimalPosition>
                                    <a:Language>Fr</a:Language>
                                </a:Localization>
                                <a:Security>
                                    <a:DomainRightsList i:nil="true" />
                                    <a:IssuerID i:nil="true" />
                                    <a:SessionID i:nil="true" />
                                    <a:SubjectLocality i:nil="true" />
                                    <a:TokenId>'.Mage::helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                                    <a:UserName i:nil="true" />
                                </a:Security>
                                <a:Version>1.0</a:Version>
                            </headerMessage>
                            <validateOrderListMessage xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <OrderList>';
        foreach($orders as $order){

            $feed .= '<ValidateOrder>';

            if($order['tracking'] !== null)
                $feed .= '<CarrierName>'.$order['tracking']['carrierName'].'</CarrierName>';            
            
            if(array_key_exists('items', $order)){

                $feed .= '<OrderLineList>';

                foreach($order['items'] as $item){

                    $feed .= '
                            <ValidateOrderLine>
                                <AcceptationState>'.$item['status'].'</AcceptationState>
                                <ProductCondition>New</ProductCondition>
                                <SellerProductId>'.$item['sellerProductId'].'</SellerProductId>
                           </ValidateOrderLine>';

                }

                $feed .= '</OrderLineList>';
            }

            $feed .= '
                                        <OrderNumber>'.$order['orderNumber'].'</OrderNumber>
                                        <OrderState>'.$order['status'].'</OrderState>';

            if($order['tracking'] !== null){
                $feed .= '
                                        <CarrierName>'.$order['tracking']['carrierName'].'</CarrierName>';
                                        //<TrackingNumber>'.$order['tracking']['number'].'</TrackingNumber>';
                                        //<TrackingUrl>'.$order['tracking']['url'].'</TrackingUrl>';
                
                if (preg_match('#http#', $order['tracking']['number'])) {

                    $feed .=
                            '<TrackingUrl><![CDATA[' . $order['tracking']['number'] . ']]></TrackingUrl>';
                } else {

                    $feed .= '                                        
                                        <TrackingNumber>' . $order['tracking']['number'] . '</TrackingNumber>';
                }
                
                
            }

            $feed .= '</ValidateOrder>';
            
        }

        $feed .= '</OrderList>
                            </validateOrderListMessage>
                        </ValidateOrderList>
                    </s:Body>
                </s:Envelope>';

        if (count($orders) > 0)
            $response = $this->_call(self::kValidateOrderList, $feed);
        else
            $response = 'No orders to update';

        return array('type' => self::kResponseTypeFile, 'content' => $response);

    }

    /**
     * get order list
     */
    public function getOrderList($order_statuses = null){

        if(is_null($order_statuses)){
            $order_statuses = Mage::Helper('Cdiscount/Orders')->getAllOrderStatuses();
        }
        
        $country = Mage::registry('mp_country');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        $fromDate = $account->getParam('order_importation_start_date');

        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
            <s:Body>
                <GetOrderList xmlns="http://www.cdiscount.com">
                    <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <a:Context>
                            <a:CatalogID>1</a:CatalogID>
                            <a:CustomerPoolID>1</a:CustomerPoolID>
                            <a:SiteID>100</a:SiteID>
                        </a:Context>
                        <a:Localization>
                            <a:Country>Fr</a:Country>
                            <a:Currency>Eur</a:Currency>
                            <a:DecimalPosition>2</a:DecimalPosition>
                            <a:Language>Fr</a:Language>
                        </a:Localization>
                        <a:Security>
                            <a:DomainRightsList i:nil="true" />
                            <a:IssuerID i:nil="true" />
                            <a:SessionID i:nil="true" />
                            <a:SubjectLocality i:nil="true" />
                            <a:TokenId>'.Mage::Helper('Cdiscount/Auth')->getToken().'</a:TokenId>
                            <a:UserName i:nil="true" />
                        </a:Security>
                        <a:Version>1.0</a:Version>
                    </headerMessage>
                    <orderFilter xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <BeginCreationDate>'.$fromDate.'</BeginCreationDate>
                        <FetchOrderLines>true</FetchOrderLines>
                        <States>';

        foreach($order_statuses as $status){

            $feed .= '<OrderStateEnum>'.$status.'</OrderStateEnum>';

        }

       $feed .=                 '</States>
                    </orderFilter>
                </GetOrderList>
            </s:Body>
        </s:Envelope>';

        $this->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeImportOrders);
        $response = $this->_call(self::kGetOrderList, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);
    }

    /**
     * Récupération de la liste des marques
     * 
     * @return array
     */
    public function getBrandList(){
        
        $feed = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
                    <s:Body>
                        <GetBrandList xmlns="http://www.cdiscount.com">
                            <headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                                <a:Context>
                                    <a:CatalogID>1</a:CatalogID>
                                    <a:CustomerPoolID>1</a:CustomerPoolID>
                                    <a:SiteID>100</a:SiteID>
                                </a:Context>
                                <a:Localization>
                                    <a:Country>Fr</a:Country>
                                    <a:Currency>Eur</a:Currency>
                                    <a:DecimalPosition>2</a:DecimalPosition>
                                    <a:Language>Fr</a:Language>
                                </a:Localization>
                                <a:Security>
                                    <a:DomainRightsList i:nil="true" />
                                    <a:IssuerID i:nil="true" />
                                    <a:SessionID i:nil="true" />
                                    <a:SubjectLocality i:nil="true" />
                                    <a:TokenId>'. Mage::Helper('Cdiscount/Auth')->getToken() .'</a:TokenId>
                                    <a:UserName i:nil="true" />
                                </a:Security>
                                <a:Version>1.0</a:Version>
                            </headerMessage>            
                        </GetBrandList>
                    </s:Body>
                </s:Envelope>';
            
        $this->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeImportOrders);
        $response = $this->_call(self::kGetBrandList, $feed);

        return array('type' => self::kResponseTypeFile, 'content' => $response);
                
    }

}
