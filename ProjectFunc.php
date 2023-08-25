<?php
class UOMDevice {

    public function UOM_device($ean) {
        if (!isset($Connection)) 
        {$Connection = new PDOConnect("DPD_DB");}
        $SQL = "SELECT * FROM EAN WHERE ([EAN PK] = :EAN AND LastEAN = 1)";
        $params = array(':EAN' => $ean);
        $stmt = $Connection->select($SQL, $params);


        if ($stmt['count'] > 0) {
            $Product = $stmt['rows'][0]['MAKTX'];
            $convert = substr($stmt['rows'][0]['MATNR'], 0, 2);
            $Unit = "Pack";
            goto Add;
        }

        $SQL = "SELECT * FROM EAN WHERE ([EAN BX] = :EAN AND LastEAN_BX = 1)";
        $params = array(':EAN' => $ean);
        $stmt = $Connection->select($SQL, $params);

        if ($stmt['count'] > 0) {
            $Product = $stmt['rows'][0]['MAKTX'];
            $convert = substr($stmt['rows'][0]['MATNR'], 0, 2);
            $Unit = "Box";
            goto Add;
        }

        $SQL = "SELECT * FROM EAN WHERE ([EAN CT] = :EAN AND LastEAN_CT = 1)";
        $stmt = $this->select($SQL, $params);

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
                    $nonDvc = null;
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                break;

            case "MJ":
                if ($Unit == "Crt") {
                    $nonDvc = null;
                    $Quantity = 5;
                } else {
                    $Quantity = 1;
                }
                break;

            case "MU":
                if ($Unit == "Crt") {
                    $nonDvc = null;
                    $Quantity = 5;
                } else {
                    $Quantity = 1;
                }
                break;

            case "DR":
                if ($Unit == "Crt") {
                    $nonDvc = null;
                    $Quantity = 50;
                } else {
                    $Quantity = 1;
                }
                break;

            case "DP":
                if ($Unit == "Crt") {
                    $nonDvc = null;
                    $Quantity = 10;
                } else {
                    $Quantity = 1;
                }
                break;

            default:
                $nonDvc = true;
                $Quantity = 1;
                break;
        }
    return array($Product,$Unit,$Quantity);
    }
}
?>