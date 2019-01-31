<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Helper_Checkbarcode extends Mage_Core_Helper_Abstract{

    const kUPC = "UPC";
    const kEAN = "EAN";
    const kISBN = "ISBN";

    const kPondMax = 3;
    const kPondMin = 1;

    /**
     * Retrieve barcode type
     *
     * @param int $code
     * @return string
     */
    public function getType($code){

        $length = strlen($code);
        $type = null;

        switch($length){

            case '12':
                $type = self::kUPC;
                break;
            case '13':
            case '14':
            case '8':
                $type = self::kEAN;
                break;
            default:
                $type = null;
                break;

        }

        return $type;

    }

    /**
     * Check barcode
     *
     * @param int $code
     * @return mixed
     */
    public function checkCode($code) {

        // Is ASIN code ?
        if(preg_match('#^B00#', $code)){
            return true;
        }
        
        $type = $this->getType($code);

        if(!$code || !$type || $code == "")
            return "Invalid EAN code.";

        
        $retour = "";
        
        // count caracters
        $length = array(
            'UPC' => array('12'),
            'EAN' => array('8', '13', '14')
        );

        switch($type){

            case 'EAN':
                $x = 3;
                $y = 1;
                break;
            case 'UPC':
                $x = 1;
                $y = 3;
                break;

        }

        if (in_array(strlen($code), $length[$type])) {
            // check only integers
            if (preg_match('/^[0-9]*$/', $code)) {

                // check validation code
                $values = str_split($code);
                $validationCode = $values[count($values) - 1];

                // somme
                $somme = 0;
                $tab = array();
                for ($i = 0; $i < count($values) - 1; $i++) {

                    if ($i % 2) {
                        $somme += $values[$i] * $x;
                    } else {
                        $somme += $values[$i] * $y;
                    }
                }

                // divide / 10
                $tmp = $somme / 10;

                $tmp = explode('.', $tmp);
                if (count($tmp) == 1)
                    $rest = 0;
                else
                    $rest = $tmp[1];

                $key = 10 - $rest;
                $key = ($key == 10) ? 0 : $key;

                // check calculated key and present key
                if ($key != $validationCode) {
                    $retour = 'Incorrect validation key.';
                }
                else{
                    $retour = true;
                }
            } else {
                $retour = 'Invalid code';
            }
        } else {
            $retour = 'Incorrect code length';
        }

        return $retour;

    }

    /**
     * Check code length in fonction af code type
     *
     * @param int $code
     * @param string $typeCode
     * @return boolean
     */
    public function checkCodeLength($code, $typeCode){

        $type = $this->getType($code);

        $type = strtolower($type);
        $typeCode = strtolower($typeCode);

        return $type == $typeCode;

    }


}
