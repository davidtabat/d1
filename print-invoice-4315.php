<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
$sql = $sql2 = $rows = $rows2 = '';
function surroundWithQuotes ($input)
{
    //$input = str_replace('"', '\"', $input); //escaping in csv files is done by doing the same quote twice, odd
	return '"' . $input . '"';
}


function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   
   //fputcsv($df, array_keys(reset($array)),';');
   fputcsv($df, array_map("surroundWithQuotes",array_keys(reset($array))),';',' ');
   
   foreach ($array as $row) {
	  fputcsv($df, array_map("surroundWithQuotes",$row),';',' ');
   }
   fclose($df);
   return ob_get_clean();
}

function bothCsv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   
   //fputcsv($df, array_keys(reset($array)),';');
   fputcsv($df, array_map("surroundWithQuotes",array_keys(reset($array))),';',' ');
   
   foreach ($array as $row) {
	  fputcsv($df, array_map("surroundWithQuotes",$row),';',' ');
   }
   fclose($df);
   return ob_get_clean();
}

function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

if(isset($_POST['download'])){
	//echo '<pre>';print_r($_POST);exit;
	Mage::app();
	$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
	
	//Zend_Debug::dump($rows);
	$stores_list = '';
	if(isset($_POST['stores'])){
		$stores_list = implode(",", $_POST['stores']);
	}
	$csv_type = 'invoice';
	
	if(isset($_POST['csv_type'])){
		$csv_type = $_POST['csv_type'];
	}
	

	$from = $to = '';
	
	if(isset($_POST['from'])){
		$from = $_POST['from'];
	}
	
	if(isset($_POST['to'])){
		$to = $_POST['to'];
		$date = new DateTime($to);
		$date->modify('+1 day');
		$to = $date->format('Y-m-d');
		
		
	}
	

	
	if($csv_type=='invoice'){
		$created_at = '';
		if($from !='' && $to!=''){
			#$created_at = "&& a.created_at BETWEEN '".$from."' AND '".$to."'";
			$created_at = "&& a.created_at >= '".$from."' AND a.created_at <= '".$to."'";
		}else if($from !=''){
			$created_at = "&& a.created_at >= '".$from."'";
		}else if($to !=''){
			$created_at = "&& a.created_at <= '".$to."'";
		}
		
		$sql        = "SELECT REPLACE(FORMAT(a.base_tax_amount,2),',','') as tax_amount, a.entity_id, a.store_id, 
						REPLACE(FORMAT(a.base_grand_total,2),',','') as gesamtbetrag_brutto, 
						a.total_qty as 'total_qty', 
						e.state as state, e.customer_id as 'Gegenkonto', e.customer_taxvat as 'Konto', a.increment_id as 'Rechnungsnr', a.created_at, b.lastname, b.company, b.firstname, b.country_id as country_id_invoice, 
						f.country_id as 'country_id_shipping', c.store_id, c.name, e.increment_id as 'Bestellnummer' 
						FROM `sales_flat_invoice` as a 
						LEFT JOIN `sales_flat_order_address` as b on (a.billing_address_id=b.entity_id) 
						LEFT JOIN core_store as c on (a.store_id=c.store_id) 
						Left join sales_flat_order as e on (a.order_id=e.entity_id) 
						Left join sales_flat_order_address as f on (a.shipping_address_id=f.entity_id) 
						where a.store_id in(".$stores_list.") ".$created_at." order by a.store_id, a.entity_id";
	}
	if($csv_type=='credit'){
		$created_at = '';
		if($from !='' && $to!=''){
			$created_at = "&& a.created_at BETWEEN '".$from."' AND '".$to."'";
		}else if($from !=''){
			$created_at = "&& a.created_at >= '".$from."'";
		}else if($to !=''){
			$created_at = "&& a.created_at <= '".$to."'";
		}
		$sql        = "SELECT REPLACE(FORMAT(a.base_tax_amount,2),',','') as tax_amount, a.entity_id, a.store_id, 
						REPLACE(FORMAT(a.base_grand_total,2),',','') as gesamtbetrag_brutto,  
						e.state as state, e.customer_id as 'Gegenkonto', e.customer_taxvat as 'Konto', a.increment_id as 'Gutschriftnummer', a.created_at, b.lastname, b.company, b.firstname, b.country_id as country_id_invoice, 
						f.country_id as 'country_id_shipping', c.store_id, c.name, e.increment_id as 'Bestellnummer' 
						FROM `sales_flat_creditmemo` as a 
						LEFT JOIN `sales_flat_order_address` as b on (a.billing_address_id=b.entity_id) 
						LEFT JOIN core_store as c on (a.store_id=c.store_id) 
						Left join sales_flat_order as e on (a.order_id=e.entity_id) 
						Left join sales_flat_order_address as f on (a.shipping_address_id=f.entity_id) 
						where a.store_id in(".$stores_list.") ".$created_at." order by a.store_id, a.entity_id";
	}
	
	if($csv_type=='both'){
		$created_at = '';
		if($from !='' && $to!=''){
			$created_at = "&& a.created_at BETWEEN '".$from."' AND '".$to."'";
		}else if($from !=''){
			$created_at = "&& a.created_at >= '".$from."'";
		}else if($to !=''){
			$created_at = "&& a.created_at <= '".$to."'";
		}
		$sql        = "SELECT REPLACE(FORMAT(a.base_tax_amount,2),',','') as tax_amount, a.entity_id, a.store_id, 
						REPLACE(FORMAT(a.base_grand_total,2),',','') as gesamtbetrag_brutto,  
						a.total_qty as 'total_qty', 
						e.state as state, e.customer_id as 'Gegenkonto', e.customer_taxvat as 'Konto', a.increment_id as 'Rechnungsnr', a.created_at, b.lastname, b.company, b.firstname, b.country_id as country_id_invoice, 
						f.country_id as 'country_id_shipping', c.store_id, c.name, e.increment_id as 'Bestellnummer' 
						FROM `sales_flat_invoice` as a 
						LEFT JOIN `sales_flat_order_address` as b on (a.billing_address_id=b.entity_id) 
						LEFT JOIN core_store as c on (a.store_id=c.store_id) 
						Left join sales_flat_order as e on (a.order_id=e.entity_id) 
						Left join sales_flat_order_address as f on (a.shipping_address_id=f.entity_id) 
						where a.store_id in(".$stores_list.") ".$created_at." order by a.store_id, a.entity_id";

		$sql2        = "SELECT REPLACE(FORMAT(a.base_tax_amount,2),',','') as tax_amount, a.entity_id, a.store_id, 
						REPLACE(FORMAT(a.base_grand_total,2),',','') as gesamtbetrag_brutto, 
						e.state as state, e.customer_id as 'Gegenkonto', e.customer_taxvat as 'Konto', a.increment_id as 'Gutschriftnummer', a.created_at, b.lastname, b.company, b.firstname, b.country_id as country_id_invoice, 
						f.country_id as 'country_id_shipping', c.store_id, c.name, e.increment_id as 'Bestellnummer' 
						FROM `sales_flat_creditmemo` as a 
						LEFT JOIN `sales_flat_order_address` as b on (a.billing_address_id=b.entity_id) 
						LEFT JOIN core_store as c on (a.store_id=c.store_id) 
						Left join sales_flat_order as e on (a.order_id=e.entity_id) 
						Left join sales_flat_order_address as f on (a.shipping_address_id=f.entity_id) 
						where a.store_id in(".$stores_list.") ".$created_at." order by a.store_id, a.entity_id";
	}
	
	if($sql!='')
		$rows = $connection->fetchAll($sql);
	if($sql2!='')
		$rows2 = $connection->fetchAll($sql2);
	
	$eu_array_wg = array('AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','GB');
	$eu_array 	 = array('AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','GB');
	
	if($rows!=''){
		$r = 0;
		foreach($rows as $row) {
				
				$rows[$r]['gesamtbetrag_brutto'] = number_format($row['gesamtbetrag_brutto'], 2, ",","");
		$rows[$r]['tax_amount'] = number_format($row['tax_amount'], 2, ",","");
			
				
			
			
			
			
			//If customer has Tax VAT ID and the invoice has 0% Tax and the country of invoice is a EU country but not Germany then add number “4125” in this colum.
			if($row['Konto']!='' && $row['tax_amount']=='0.00' && in_array($row['country_id_invoice'],$eu_array_wg)){
				$rows[$r]['Konto'] = '4125';
			}
			//If Contry is not Germany and not EU and Invoice is 0% Tax than add number “4120” in this column
			else if(!in_array($row['country_id_invoice'],$eu_array) && $row['tax_amount']=='0.00'){
				$rows[$r]['Konto'] = '4120';
			}
			//If invoice is EU Country but not Germany and tax of Invoice is 19% then add number “4315”
			else if(in_array($row['country_id_invoice'],$eu_array_wg) && $row['tax_amount'] > '0'){
				$rows[$r]['Konto'] = '4315';
			}
			//If country of invoice is Germany and tax is 19% add number “4400”
			else if($row['country_id_invoice']=='DE' && $row['tax_amount'] > '0'){
				$rows[$r]['Konto'] = '4400';
			}else {
				$rows[$r]['Konto'] = 'XXXXX';
			}
			$r++;
		}
	}
	
	if($rows2!=''){
		$r = 0;
		foreach($rows2 as $row) {

				$rows2[$r]['gesamtbetrag_brutto'] = number_format($row['gesamtbetrag_brutto'], 2, ",","");

						$rows2[$r]['tax_amount'] = number_format($row['tax_amount'], 2, ",","");
				
		
			
			//If customer has Tax VAT ID and the invoice has 0% Tax and the country of invoice is a EU country but not Germany then add number “4125” in this colum.
			if($row['Konto']!='' && $row['tax_amount']=='0.00' && in_array($row['country_id_invoice'],$eu_array_wg)){
				$rows2[$r]['Konto'] = '4125';
			}
			//If Contry is not Germany and not EU and Invoice is 0% Tax than add number “4120” in this column
			else if(!in_array($row['country_id_invoice'],$eu_array) && $row['tax_amount']=='0.00'){
				$rows2[$r]['Konto'] = '4120';
			}
			//If invoice is EU Country but not Germany and tax of Invoice is 19% then add number “4315”
			else if(in_array($row['country_id_invoice'],$eu_array_wg) && $row['tax_amount'] > '0'){
				$rows2[$r]['Konto'] = '4315';
			}
			//If country of invoice is Germany and tax is 19% add number “4400”
			else if($row['country_id_invoice']=='DE' && $row['tax_amount'] > '0'){
				$rows2[$r]['Konto'] = '4400';
			}else {
				$rows2[$r]['Konto'] = 'XXXXX';
			}
			$r++;
		}
	}
	
	if($rows!='' && $csv_type=='invoice'){
		download_send_headers("invoice_export_" . date("Y-m-d") . ".csv");
		echo array2csv($rows);
		die();
	}
	
		if($rows!='' && $csv_type=='credit'){
		download_send_headers("credit_export_" . date("Y-m-d") . ".csv");
		echo array2csv($rows);
		die();
	}

	if($rows2!='' && $csv_type=='credit'){
		download_send_headers("credit_export_" . date("Y-m-d") . ".csv");
		echo array2csv($rows2);
		die();
	}
	
	if($csv_type=='both'){
		download_send_headers("invoice_export_" . date("Y-m-d") . ".csv");
		
		if($rows!='')
			echo bothCsv($rows);
		if($rows2!='')
			echo bothCsv($rows2);
		
		die();
	}
	
	/*if($rows!='' && $csv_type=='both'){
		download_send_headers("both_export_" . date("Y-m-d") . ".csv");
		echo array2csv($rows);
		die();
	}*/
	
}
?>

<!DOCTYPE>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Export Tool</title>
<style type="text/css">
.store-table {
    border-collapse: collapse;border: 1px solid black;
}
.store-table td {
    border: 1px solid black;padding:10px;
}
td{vertical-align: top;}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>
  $(function() {
    $( "#from" ).datepicker({
      defaultDate: "+1w",
	  dateFormat: "yy-mm-dd",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function( selectedDate ) {
        $( "#to" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#to" ).datepicker({
      defaultDate: "+1w",
	  dateFormat: "yy-mm-dd",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function( selectedDate ) {
        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
  });
  </script>
</head>
<body style="width:900px;margin: 0px auto;">
<?php
	$eu_array_wg = array('AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','GB');
	$eu_array 	 = array('AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','GB');
	if($rows!='' && $csv_type=='invoice'){
?>
	<h2>Invoice</h2>
	<table width="100%" border="1" cellspacing="0" cellpadding="0">
	  <tr>
		<th scope="col">entity_id</th>
		<th scope="col">store_id</th>
		<th scope="col">Gesamtbetrag brutto</th>
		<th scope="col">MwSt Betrag</th>
		<th scope="col">Transportkosten brutto</th>
		<th scope="col">Transportkosten MwSt</th>
		<th scope="col">total_qty</th>
		<th scope="col">Current State</th>
		<th scope="col">Gegenkonto</th>
		<th scope="col">Konto</th>
		<th scope="col">Rechnungsnr</th>
		<th scope="col">created_at</th>
		<th scope="col">lastname</th>
		<th scope="col">company</th>
		<th scope="col">firstname</th>
		<th scope="col">country_id invoice</th>
		<th scope="col">country_id_shipping</th>
		<th scope="col">name</th>
		<th scope="col">sku</th>
		<th scope="col">Bestellnummer</th>
	  </tr>
<?php
	foreach($rows as $row) {
		//If customer has Tax VAT ID and the invoice has 0% Tax and the country of invoice is a EU country but not Germany then add number “4125” in this colum.
		$konto = 'XXXXX';
		if($row['Konto']!='' && $row['tax_amount']=='0,00' && in_array($row['country_id_invoice'],$eu_array_wg)){
			$konto = '4125';
		}
		//If Contry is not Germany and not EU and Invoice is 0% Tax than add number “4120” in this column
		if(!in_array($row['country_id_invoice'],$eu_array) && $row['tax_amount']=='0,00'){
			$konto = '4120';
		}
		//If invoice is EU Country but not Germany and tax of Invoice is 19% then add number “4315”
		if(in_array($row['country_id_invoice'],$eu_array_wg) && $row['tax_amount'] > '0'){
			$konto = '4315';
		}
		//If country of invoice is Germany and tax is 19% add number “4400”
		if($row['country_id_invoice']=='DE' && $row['tax_amount'] > '0'){
			$konto = '4400';
		}
?>
  <tr>
    <td><?php echo $row['entity_id']; ?></td>
    <td><?php echo $row['store_id']; ?></td>
    <td><?php echo $row['gesamtbetrag_brutto']; ?></td>
    <td><?php echo $row['tax_amount']; ?></td>
    <td><?php if (!empty($row['Transportkosten brutto'])) echo $row['Transportkosten brutto']; ?></td>
    <td><?php if (!empty($row['Transportkosten MwSt'])) echo $row['Transportkosten MwSt']; ?></td>
    <td><?php echo $row['total_qty']; ?></td>
    <td><?php echo $row['state']; ?></td>
	<td><?php echo $row['Gegenkonto']; ?></td>
	<td><?php echo $konto; ?></td>
    <td><?php echo $row['Rechnungsnr']; ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <td><?php echo $row['lastname']; ?></td>
    <td><?php echo $row['company']; ?></td>
    <td><?php echo $row['firstname']; ?></td>
    <td><?php echo $row['country_id_invoice']; ?></td>
    <td><?php echo $row['country_id_shipping']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['sku']; ?></td>
    <td><?php echo $row['Bestellnummer']; ?></td>
  </tr>

<?php
		}
?>
</table>
<?php
	}
	if($rows!='' && $csv_type=='credit'){
?>
<h2>Credit Notes</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="0">
	  <tr>
		<th scope="col">entity_id</th>
		<th scope="col">store_id</th>
		<th scope="col">Gesamtbetrag brutto</th>
		<th scope="col">MwSt Betrag</th>
		<th scope="col">Transportkosten brutto</th>
		<th scope="col">Transportkosten MwSt</th>
		<th scope="col">Current State</th>
		<th scope="col">Gegenkonto</th>
		<th scope="col">Konto</th>
		<th scope="col">Gutschriftnummer</th>
		<th scope="col">created_at</th>
		<th scope="col">lastname</th>
		<th scope="col">company</th>
		<th scope="col">firstname</th>
		<th scope="col">country_id invoice</th>
		<th scope="col">country_id_shipping</th>
		<th scope="col">name</th>
		<th scope="col">sku</th>
		<th scope="col">Bestellnummer</th>
	  </tr>
<?php
	foreach($rows as $row) {
		//If customer has Tax VAT ID and the invoice has 0% Tax and the country of invoice is a EU country but not Germany then add number “4125” in this colum.
		$konto = 'XXXXX';
		if($row['Konto']!='' && $row['tax_amount']=='0,00' && in_array($row['country_id_invoice'],$eu_array_wg)){
			$konto = '4125';
		}
		//If Contry is not Germany and not EU and Invoice is 0% Tax than add number “4120” in this column
		if(!in_array($row['country_id_invoice'],$eu_array) && $row['tax_amount']=='0,00'){
			$konto = '4120';
		}
		//If invoice is EU Country but not Germany and tax of Invoice is 19% then add number “4315”
		if(in_array($row['country_id_invoice'],$eu_array_wg) && $row['tax_amount'] > '0'){
			$konto = '4315';
		}
		//If country of invoice is Germany and tax is 19% add number “4400”
		if($row['country_id_invoice']=='DE' && $row['tax_amount'] > '0'){
			$konto = '4400';
		}
?>
  <tr>
    <td><?php echo $row['entity_id']; ?></td>
    <td><?php echo $row['store_id']; ?></td>
    <td><?php echo $row['gesamtbetrag_brutto']; ?></td>
    <td><?php echo $row['tax_amount']; ?></td>
    <td><?php if (!empty($row['Transportkosten brutto'])) echo $row['Transportkosten brutto']; ?></td>
    <td><?php if (!empty($row['Transportkosten MwSt'])) echo $row['Transportkosten MwSt']; ?></td>
    <td><?php echo $row['state']; ?></td>
	<td><?php echo $row['Gegenkonto']; ?></td>
	<td><?php echo $konto; ?></td>
    <td><?php echo $row['Gutschriftnummer']; ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <td><?php echo $row['lastname']; ?></td>
    <td><?php echo $row['company']; ?></td>
    <td><?php echo $row['firstname']; ?></td>
    <td><?php echo $row['country_id_invoice']; ?></td>
    <td><?php echo $row['country_id_shipping']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['sku']; ?></td>
    <td><?php echo $row['Bestellnummer']; ?></td>
  </tr>

<?php
		}
?>
</table>
<?php
		}
?>
<?php
	/*If csv_type == both */
	if($rows!='' && $csv_type=='both'){
?>
	<h2>Invoice</h2>
	<table width="100%" border="1" cellspacing="0" cellpadding="0">
	  <tr>
		<th scope="col">entity_id</th>
		<th scope="col">store_id</th>
		<th scope="col">Gesamtbetrag brutto</th>
		<th scope="col">MwSt Betrag</th>
		<th scope="col">Transportkosten brutto</th>
		<th scope="col">Transportkosten MwSt</th>
		<th scope="col">total_qty</th>
		<th scope="col">Current State</th>
		<th scope="col">Gegenkonto</th>
		<th scope="col">Konto</th>
		<th scope="col">Rechnungsnr</th>
		<th scope="col">created_at</th>
		<th scope="col">lastname</th>
		<th scope="col">company</th>
		<th scope="col">firstname</th>
		<th scope="col">country_id invoice</th>
		<th scope="col">country_id_shipping</th>
		<th scope="col">name</th>
		<th scope="col">sku</th>
		<th scope="col">Bestellnummer</th>
	  </tr>
<?php
	foreach($rows as $row) {
		//If customer has Tax VAT ID and the invoice has 0% Tax and the country of invoice is a EU country but not Germany then add number “4125” in this colum.
		$konto = 'XXXXX';
		if($row['Konto']!='' && $row['tax_amount']=='0,00' && in_array($row['country_id_invoice'],$eu_array_wg)){
			$konto = '4125';
		}
		//If Contry is not Germany and not EU and Invoice is 0% Tax than add number “4120” in this column
		if(!in_array($row['country_id_invoice'],$eu_array) && $row['tax_amount']=='0,00'){
			$konto = '4120';
		}
		//If invoice is EU Country but not Germany and tax of Invoice is 19% then add number “4315”
		if(in_array($row['country_id_invoice'],$eu_array_wg) && $row['tax_amount'] > '0'){
			$konto = '4315';
		}
		//If country of invoice is Germany and tax is 19% add number “4400”
		if($row['country_id_invoice']=='DE' && $row['tax_amount'] > '0'){
			$konto = '4400';
		}
?>
  <tr>
    <td><?php echo $row['entity_id']; ?></td>
    <td><?php echo $row['store_id']; ?></td>
    <td><?php echo $row['gesamtbetrag_brutto']; ?></td>
    <td><?php echo $row['tax_amount']; ?></td>
    <td><?php if (!empty($row['Transportkosten brutto'])) echo $row['Transportkosten brutto']; ?></td>
    <td><?php if (!empty($row['Transportkosten MwSt'])) echo $row['Transportkosten MwSt']; ?></td>
    <td><?php echo $row['total_qty']; ?></td>
    <td><?php echo $row['state']; ?></td>
	<td><?php echo $row['Gegenkonto']; ?></td>
	<td><?php echo $konto; ?></td>
    <td><?php echo $row['Rechnungsnr']; ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <td><?php echo $row['lastname']; ?></td>
    <td><?php echo $row['company']; ?></td>
    <td><?php echo $row['firstname']; ?></td>
    <td><?php echo $row['country_id_invoice']; ?></td>
    <td><?php echo $row['country_id_shipping']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['sku']; ?></td>
    <td><?php echo $row['Bestellnummer']; ?></td>
  </tr>

<?php
		}
?>
</table>
<?php
	}
	if($rows2!='' && $csv_type=='both'){
?>
<h2>Credit Notes</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="0">
	  <tr>
		<th scope="col">entity_id</th>
		<th scope="col">store_id</th>
		<th scope="col">Gesamtbetrag brutto</th>
		<th scope="col">MwSt Betrag</th>
		<th scope="col">Transportkosten brutto</th>
		<th scope="col">Transportkosten MwSt</th>
		<th scope="col">Current State</th>
		<th scope="col">Gegenkonto</th>
		<th scope="col">Konto</th>
		<th scope="col">Gutschriftnummer</th>
		<th scope="col">created_at</th>
		<th scope="col">lastname</th>
		<th scope="col">company</th>
		<th scope="col">firstname</th>
		<th scope="col">country_id invoice</th>
		<th scope="col">country_id_shipping</th>
		<th scope="col">name</th>
		<th scope="col">sku</th>
		<th scope="col">Bestellnummer</th>
	  </tr>
<?php
	foreach($rows2 as $row) {
		//If customer has Tax VAT ID and the invoice has 0% Tax and the country of invoice is a EU country but not Germany then add number “4125” in this colum.
		$konto = 'XXXXX';
		if($row['Konto']!='' && $row['tax_amount']=='0,00' && in_array($row['country_id_invoice'],$eu_array_wg)){
			$konto = '4125';
		}
		//If Contry is not Germany and not EU and Invoice is 0% Tax than add number “4120” in this column
		if(!in_array($row['country_id_invoice'],$eu_array) && $row['tax_amount']=='0,00'){
			$konto = '4120';
		}
		//If invoice is EU Country but not Germany and tax of Invoice is 19% then add number “4315”
		if(in_array($row['country_id_invoice'],$eu_array_wg) && $row['tax_amount'] > '0'){
			$konto = '4315';
		}
		//If country of invoice is Germany and tax is 19% add number “4400”
		if($row['country_id_invoice']=='DE' && $row['tax_amount'] > '0'){
			$konto = '4400';
		}
?>
  <tr>
    <td><?php echo $row['entity_id']; ?></td>
    <td><?php echo $row['store_id']; ?></td>
    <td><?php echo $row['gesamtbetrag_brutto']; ?></td>
    <td><?php echo $row['tax_amount']; ?></td>
    <td><?php if (!empty($row['Transportkosten brutto'])) echo $row['Transportkosten brutto']; ?></td>
    <td><?php if (!empty($row['Transportkosten MwSt'])) echo $row['Transportkosten MwSt']; ?></td>
    <td><?php echo $row['state']; ?></td>
	<td><?php echo $row['Gegenkonto']; ?></td>
	<td><?php echo $konto; ?></td>
    <td><?php echo $row['Gutschriftnummer']; ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <td><?php echo $row['lastname']; ?></td>
    <td><?php echo $row['company']; ?></td>
    <td><?php echo $row['firstname']; ?></td>
    <td><?php echo $row['country_id_invoice']; ?></td>
    <td><?php echo $row['country_id_shipping']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['sku']; ?></td>
    <td><?php echo $row['Bestellnummer']; ?></td>
  </tr>

<?php
		}
?>
</table>
<?php
		}
?>
<form method="post" name="export-tool">
<table align="center" width="800">
	<tr>
		<td colspan="2"><h1>Export Tool</h1></td>
	</tr>
	<tr>
		<td>Select Type</td>
		<td>
			<select name="csv_type">
				<option value="invoice">Invoices</option>
				<option value="credit">Credit Notes</option>
				<option value="both">Both</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Select Time Frame</td>
		<td>
			<label for="from">from</label>
			<input type="text" id="from" name="from" value="">
			<label for="to">to</label>
			<input type="text" id="to" name="to" value="">
		</td>
	</tr>
	<tr>
		<td>Select Stores</td>
		<td>
			<table class="store-table">
				<tr>
					<td>&nbsp;</td>
					<td>ID</td>
					<td>Name</td>
				</tr>
				<?php
				$allStores = Mage::app()->getStores();
				$s = 1;
				foreach ($allStores as $_eachStoreId => $val) 
				{
				$_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
				$_storeName = Mage::app()->getStore($_eachStoreId)->getName();
				$_storeId = Mage::app()->getStore($_eachStoreId)->getId();
				?>
				<tr>
					<td><input type="checkbox" name="stores[]" value="<?php echo $_storeId; ?>" /></td>
					<td><?php echo $s; ?></td>
					<td><?php echo $_storeName; ?></td>
				</tr>
				<?php $s++;} ?>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" name="download" value="Download CSV" /></td>
	</tr>
</table>
</form>
</body>
</html>