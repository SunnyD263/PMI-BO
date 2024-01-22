<?php
class UOMDevice {

    public function UOM_device($EAN) {

        $Type = substr($EAN, -4);
        switch ($Type) 
            {
            case 'SWAP':
                $Type = 'SWAP';
                $BarCode = substr($EAN, 0, -4);
                $Codentify = '';
                $Material = '';
                break;
            
            default:
                $Type = 'NOVE';
        
                if (strlen($EAN) >= 40 && strlen($EAN) <= 46) {
                    $BarCode = substr($EAN, 3, 13);
                    $Codentify = strtoupper(substr($EAN, 18, 12));
                    $Material = strtoupper(substr($EAN, -11));
                } elseif (strlen($EAN) == 13) {
                    $BarCode = $EAN;
                    $Codentify = '';
                    $Material = '';
                } else {
                    echo "Špatná hodnota";
                    return false;
                }
                break;
            }

        if (!isset($Connection)) 
        {$Connection = new PDOConnect("DPD_DB");}
        $SQL = "SELECT * FROM EAN WHERE ([EAN_PK] = :EAN AND LastEAN = 1)";
        $params = array(':EAN' => $BarCode);
        $stmt = $Connection->select($SQL, $params);


        if ($stmt['count'] > 0) {
            $Product = $stmt['rows'][0]['MAKTX'];
            $convert = substr($stmt['rows'][0]['MATNR'], 0, 2);
            $Unit = "Pack";
            goto Add;
        }

        $SQL = "SELECT * FROM EAN WHERE ([EAN_BX] = :EAN AND LastEAN_BX = 1)";
        $params = array(':EAN' => $BarCode);
        $stmt = $Connection->select($SQL, $params);

        if ($stmt['count'] > 0) {
            $Product = $stmt['rows'][0]['MAKTX'];
            $convert = substr($stmt['rows'][0]['MATNR'], 0, 2);
            $Unit = "Box";
            goto Add;
        }

        $SQL = "SELECT * FROM EAN WHERE ([EAN_CT] = :EAN AND LastEAN_CT = 1)";
        $params = array(':EAN' => $BarCode);        
        $stmt = $Connection->select($SQL,$params);

        if ($stmt['count'] == 0) {
            echo "Neznámý EAN";
            return;
        } else {
            $Product = $stmt['rows'][0]['MAKTX'];
            $convert = substr($stmt['rows'][0]['MATNR'], 0, 2);
            $Unit = "Crt";
            goto Add;
        }

        Add:
        switch ($convert) {
            case "ME":
                if ($Unit == "Crt") {
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "MW":
                if ($Unit == "Crt") {
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "MA":
                if ($Unit == "Crt") {
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "MJ":
                if ($Unit == "Crt") {
                    $Quantity = 5;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "MU":
                if ($Unit == "Crt") {
                    $Quantity = 5;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "DR":
                if ($Unit == "Crt") {
                    $Quantity = 50;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "DE":
                if ($Unit == "Crt") {
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "DF":
                if ($Unit == "Crt") {
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "DP":
                if ($Unit == "Crt") {
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;

            case "KA":
                if ($Unit == "Crt") {
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                $nonDvc = true;
                break;
                
            default:
            //(E,DK,DA,DC)
                $nonDvc = false;
                $Quantity = 1;
                break;
        }

    
    return array($Material,$Product,$BarCode,$Codentify,$Unit,$Quantity,$nonDvc);
    }
}

class InputValue 
{
private $Input;
    public function __construct($Input){
        $this->Input = $Input;
    }

    public function ParcelNumber()
    {
        $Input = $this->Input;
        switch(true):  
            case (is_numeric ($Input) and strlen(strval($Input)) == 27);
                return array(substr(strval($Input),7,14),"NUM","DPD");
            case (is_numeric ($Input) and strlen(strval($Input)) == 12);
                return array(substr(strval($Input),0,11),"NUM","DPD");
            case is_numeric($Input); 
                return array($Input,"NUM");
            break;
                
            default:
            if(substr($Input,0,2) == "%0")
                {    
                return array(substr($Input,8,14),"NUM","DPD");    
                }
            elseif(substr($Input,12,1) == "X" and strlen($Input)==13)
                {
                return array($Input,"NUM","ČP"); 
                }
            elseif (substr($Input,0,1) == "Z" and strlen($Input)==11)
                {
                return array(substr($Input,1,10),"NUM","Packeta");    
                }
            elseif(strlen($Input) == 17 and substr(($Input),11,1) == "-" )
                {
                return array(substr(strval($Input),0,11),"NUM","PPL");              
                }
            elseif(strlen($Input) == 17 and substr(($Input),11,1) == "-" )
                {
                return array(substr(strval($Input),0,11),"NUM","PPL");              
                }
            else
                {
                return array($Input,"Text","Unkwonw");      
                }
            endswitch;   
    }

}


function addToArray($existingArray, $record) {
    array_push($existingArray, $record);
    return $existingArray;
}


function getWorkingDay($date,$daysBack = 1) 
    { 
    $dateObj = new DateTime($date);
    $dateObj->sub(new DateInterval("P" . $daysBack . "D"));
    if ($dateObj->format('N') >= 6) {
        $dateObj->modify('previous Friday');}
        $DTform =array ($dateObj->format('Y-m-d'),$dateObj->format('Y_m_d'));
    return $DTform;
    }
?>