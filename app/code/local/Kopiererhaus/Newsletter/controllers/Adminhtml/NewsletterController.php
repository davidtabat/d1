<?php

/*
 Idealo Exportmodul for Magento 1.4.1 and 1.5.0

 (c) idealo 2011,

 Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.

 Extended by

 Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
 */

class Kopiererhaus_Newsletter_Adminhtml_NewsletterController extends Mage_Adminhtml_Controller_Action {
	
	private $_apiKey = '1e76f1290ab51ecb230b0ce1d7b7f908';
	
	private $_groups = array();
	
	private $_api;
	
	private $_wsdl = 'http://api.cleverreach.com/soap/interface_v5.0.php?wsdl';
	
	public function initAction() {
		
		
	}
	
	public function initApi() {
		$this->_api = new SoapClient($this->_wsdl);
	}
	
	public function loadGroups() {
		$this->_groups = array();
		
		$result		= $this->_api->groupGetList($this->_apiKey);
 		
		if ($result->status == 'SUCCESS') {
			foreach ($result->data as $group) {
				$this->_groups[] = $group;
			}
		}
		//var_dump($this->_groups);
	}
	
	public function hasGroup($groupName) {
		foreach ($this->_groups as $group) {
			if ($group == $groupName) {
				return true;
			}
		}
		return false;
	}
	
	public function getGroupId($groupName) {
		foreach ($this->_groups as $group) {
			if ($group->name == $groupName) {
				return $group->id;
			}
		}
		return 0;
	}
	
	public function addGroup($groupName) {
		$result = $this->_api->groupAdd($this->_apiKey, $groupName);
		
		if ($result->status == 'SUCCESS') {
			return $result->data->id;
		}
		
		$this->loadGroups();
		
		return false;
	}

	public function receiverAdd($groupId, $userData) {
		$result = $this->_api->receiverAdd($this->_apiKey, $groupId, $userData);
		
		if ($result->status == 'SUCCESS') {
 			//var_dump($result);
 			return true;
		}
		return false;
	}
	
	public function receiverGetByEmail($groupId, $email) {
		$result = $this->_api->receiverGetByEmail($this->_apiKey, $groupId, $email, 0);
		
		/*if ($groupId == 114404) {
			print_r($result);
		}*/
		
		if ($result->status == 'SUCCESS') {
 			return $result->data;
		}
		return null;
	} 
	
	
	/**
	 * Grid view
	 */
	public function indexAction() {
		
		$this->initApi();
		$this->loadGroups(); 
		
		$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
		$resource = Mage::getModel('sales/order') -> getCollection();
		$productModel = Mage::getModel('catalog/product');
		
		//echo "E-Mail,Orderstatus,SKU,Produktname\n"; 
		//echo "E-Mail,SKU,Produktname\n";
		
		
		$list = array();
		
		foreach ($resource as $order) {
			
			if ($order->getStatus() != 'canceled') {
				$exportOrder = false;
				
				//$products = Mage::getResourceModel('sales/order_item_collection')->setOrderFilter($orderId);
				$customerEmail = $order->getCustomer_email();
				$items = $order->getAllItems();
				
				foreach ($items as $item) {
					$productId = $item->getProductId();
					
					$product = $productModel->load($productId);
					$attributeSetModel->load($product->getAttributeSetId());
					$attributeSetName  = $attributeSetModel->getAttributeSetName();	
					
					if ($attributeSetName == 'Kopierer') {
						$productSku = $product->getSku();
						$productName = $product->getName();
						
						$userList = array();
						if (array_key_exists($productId, $list)) {
							$data = $list[$productId];
							$userList = $data['receivers']; 
						} else {
							$data = array();							
						}
						
						$userList[] = $customerEmail;
						
						$data['sku']		= $productSku;
						$data['name']		= $productName; 
						$data['receivers']	= $userList;
						
						$list[$productId] = $data;
					}
					
				}
			}
		}

		$groupsAdded = 0;
		$receiverAdded = 0;

		//echo "<pre>";
		foreach ($list as $data) {
			$receivers = $data['receivers'];
			if (sizeof($receivers) >= 5) {
				$groupName = $data['name'];
				$groupId = $this->getGroupId($groupName);
				
				//echo $groupId . " - " . $groupName;
						
				if ($groupId == false) {
					$groupId = $this->addGroup($groupName);
					$groupsAdded++;
				} 
				
				if ($groupId != false) {
					foreach ($receivers as $email) {
						$receiver = $this->receiverGetByEmail($groupId, $email);
						
						if ($receiver == null) {
							$user = array(
							     "email" => $email,
							     "registered" => time(),
							     "activated" => time(),
							     "source" => 'Magento',
							);
							$status = $this->receiverAdd($groupId, $user);
							if ($status) {
								$receiverAdded++;
							}
						}
					}
				}
			}
		}
		
		echo "<pre>";
		echo $groupsAdded . " neue Gruppe(n) hinzugefügt \n";
		echo $receiverAdded . " neue Empfänger hinzugefügt \n";
		echo "Gruppen Insgesamt: " . sizeof($this->_groups) . "\n";
		
		echo "</pre>";
		//echo "<br />";
		echo '<a href="/admin">Zurück zum Backend</a>';
		
		//$this->_redirect('dashboard_index');
	}

}
