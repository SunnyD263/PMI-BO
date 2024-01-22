<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Nedoručená zařízení</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="Nedoručená zařízení" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script
        src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc="
        crossorigin="anonymous">  
    </script>
</head>
<body>
    <header>
        <h1>PMI BO Tool</h1>
        <?php require 'navigation.php'; ?>
    </header>
    <br>
<script src="NonDelivery_form.js"></script>
<?php
session_start();
require 'SQLconn.php';
require 'ProjectFunc.php'; 

//---------------------------------------------------------------------------------------------------------------------//

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{    
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true); 
if(isset($data["ERROR"]))
    {
    $_SESSION["Error"] = 'MissEAN';
    }
else
    {
    $_SESSION["NonDlv_ORDITEM"] = $data["NonDlv_ORDITEM"];
    $_SESSION['NonDlv_SCNITEM'] = $data['NonDlv_SCNITEM'];
    }
}

//---------------------------------------------------------------------------------------------------------------------//

If ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
//Open forms
    If(isset($_GET["Open"]))
    {
    NonDelivery_Form();
    }
//Pop-up windows
    elseif (isset($_GET["Menu"])) 
    {
        if($_GET["Menu"]=='no')
        {
        NonDelivery_Form();
        }
        elseif($_GET["Menu"]=='yes')
        {
        unset($_SESSION['PARCELNO']);
        unset($_SESSION['Reference']);
        unset($_SESSION["NonDlv_ORDITEM"]);
        unset($_SESSION["NonDlv_SCNITEM"]);
        unset($_SESSION['PARCELNO']);
        header("Location: NonDelivery.php");
        }
    }
    elseif (isset($_GET["Notmatch"])) 
    {
        if($_GET["Notmatch"]=='no')
        {
        die;
        }
        elseif($_GET["Notmatch"]=='yes')
        {
            If(strlen($_GET["EAN"]) == 17 and substr($_GET["EAN"], -4) == "SWAP" )
            {
                Confirmation('Exit');
            }
            elseif(strlen($_GET["EAN"]) == 14)
            {
                Confirmation('Exit');
            }
            else
            {
                
            }    
            die;
        }
    }
    elseif(isset($_GET["Codentify"]))
    {
    // JavaScript function to prompt user for EAN
    If ($_GET["Codentify"] !== "Prázdné")
        {
        $orditem = isset($_SESSION["NonDlv_ORDITEM"]) ? json_encode($_SESSION["NonDlv_ORDITEM"]) : "[]";
        if(!isset($_SESSION['NonDlv_SCNITEM'])){$_SESSION['NonDlv_SCNITEM'] = [];}
        $scnitem = isset($_SESSION['NonDlv_SCNITEM']) ? json_encode($_SESSION['NonDlv_SCNITEM']) : "[]";
        $Call_Class = new UOMDevice;
        $UOM = $Call_Class->UOM_device(trim($_GET["Codentify"]));
        if ($UOM !== false)
            {
            if ($UOM[3] == '' and  $UOM[6] == false and $UOM[4] == 'Pack')
                {
                $_SESSION["Error"] = 'DeviceCDF';    
                NonDelivery_Form();
                }
            elseif ($UOM[3] == '' and  $UOM[6] == true and $UOM[4] == 'Crt')
                {
                $_SESSION["Error"] = 'DeviceCDF';    
                NonDelivery_Form();            
                }
            else
                {
                $Scan = json_encode($UOM);
                echo "<script>CheckCDF(" . $orditem . "," . $Scan . "," . $scnitem . ",". $_SESSION["Reference"] . ");</script>";
                }
            }
        else
            {
            $_SESSION["Error"] = "FormatEAN";
            header("Location: NonDelivery_Form.php?Open=");    
            }
        }
    else
        {
        $orditem =  $_SESSION["NonDlv_ORDITEM"];
        foreach ($orditem as $row)    
            {
                $recordObject = array(
                    'Reference' => $_SESSION["Reference"],
                    'Product' => $row["Material"],
                    'ProductName' => $row["MAKTX"],
                    'EAN' => $row["EAN"],
                    'EAN_CRT' => $row["EAN_CRT"],
                    'Codentify' => $row["Codentify"],
                    'DateTime' =>  date("Y-m-d hh:mm:ss"),
                    'ScanQuantity' => 0,
                    'Checker' => 'EMPTY'
                );
            if(!isset($_SESSION['NonDlv_SCNITEM'])){$_SESSION['NonDlv_SCNITEM'] = [];}                
            $_SESSION["NonDlv_SCNITEM"]= addToArray($_SESSION["NonDlv_SCNITEM"], $recordObject);
            header("Location: NonDelivery_Form.php?Open=");
            }     
        }     
    }

//Delete scanned article
    elseif(isset($_GET["ScanArrayID"]))
    {
        $key=$_GET["ScanArrayID"];
        if (array_key_exists($key, $_SESSION["NonDlv_SCNITEM"])) 
        {
        $CompareValue = $_SESSION["NonDlv_SCNITEM"][$key]["ProductName"];
        $OrdKey = 0;
        foreach ($_SESSION["NonDlv_ORDITEM"] as $Index)    
            {
                if( $Index["MAKTX"] == $CompareValue)
                {
                $_SESSION["NonDlv_ORDITEM"][$OrdKey]['Sum'] =  $_SESSION["NonDlv_ORDITEM"][$OrdKey]['Sum'] - $_SESSION["NonDlv_SCNITEM"][$key]["ScanQuantity"];           
                $_SESSION["NonDlv_ORDITEM"][$OrdKey]['ScanQuantity'] =  $_SESSION["NonDlv_ORDITEM"][$OrdKey]['ScanQuantity'] - $_SESSION["NonDlv_SCNITEM"][$key]["ScanQuantity"] ;  
                unset($_SESSION["NonDlv_SCNITEM"][$key]);
                header("Location: NonDelivery_Form.php?Open=");
                break;
                }
            $OrdKey++;
            }
        }
    }
//Saving
    elseif(isset($_GET["Save"]))
    { 
    if (!isset($Connection)) {$Connection = new PDOConnect("DPD_DB");}
    $FinalSum = 0;
    if(isset($_SESSION["NonDlv_ORDITEM"][0]["ORDTyp"])){$OrdTyp =substr_replace($_SESSION["NonDlv_ORDITEM"][0]["ORDTyp"], "R", 1, 1);}
    if (isset($_SESSION["NonDlv_ORDITEM"]))
        {
        $Orders = $_SESSION["NonDlv_ORDITEM"];
        foreach($Orders as $Index)
            {
            $FinalSum = $FinalSum + $Index["Sum"];
            }
        if($FinalSum == 0)
            {
                
            }

        $Scan = $_SESSION["NonDlv_SCNITEM"];
        foreach($Scan as $ScnIndex)
            {
            if ($ScnIndex["Checker"] !== 'Other')
                {
                $data = array('REFERENCE' => $ScnIndex["Reference"], 'EAN' => $ScnIndex["EAN"], 'EAN_CT' => $ScnIndex["EAN_CRT"],'Codentify' => $ScnIndex["Codentify"], 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => $ScnIndex["ScanQuantity"] );
                $Connection->insert("NonDlv_Dvc", $data);
                }
            else
                {
                $data = array('REFERENCE' => $ScnIndex["Reference"], 'EAN' => $ScnIndex["EAN"], 'EAN_CT' => $ScnIndex["EAN_CRT"],'Codentify' => $ScnIndex["Codentify"], 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => $ScnIndex["ScanQuantity"] );
                $Connection->insert("NonDlv_Dvc", $data);

                $data = array('REFERENCE' => $ScnIndex["Reference"], 'ORDTyp' => $OrdTyp, 'Material' => $ScnIndex["Product"],'Quantity' => $ScnIndex["ScanQuantity"],'Codentify' => 'XXXXXXXXXXXXXX' );
                $Connection->insert("OrderItems", $data);
                }
            }
        if (isset($_SESSION["NonDlv_ORDITEM"]))
        {
        $Order = $_SESSION["NonDlv_ORDITEM"];
        foreach($Order as $OrdIndex)
            {
            if ($OrdIndex["Checker"] == 'Other' )
                {
                $data = array('REFERENCE' => $_SESSION["Reference"], 'EAN' => $OrdIndex["EAN"],'Codentify' => 'EMPTY', 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => 0 );
                $Connection->insert("NonDlv_Dvc", $data);
                }
            }
        }    
        unset($_SESSION['PARCELNO']);
        unset($_SESSION['Reference']);
        unset($_SESSION["OTHER_ITEM"]);
        unset($_SESSION["NonDlv_ORDITEM"]);
        unset($_SESSION["NonDlv_SCNITEM"]);
        header("Location: NonDelivery.php");
        echo '<span class="DoneMsg">Záznam byl uložen na server.</span>';
        }
    else
        {
        header("Location: NonDelivery_Form.php?Open=");           
        }
    }
}

function NonDelivery_Form()
{

    if (isset($_SESSION["Error"])) 
    {   
        switch ($_SESSION["Error"]) 
        {
            case "FormatEAN":
                echo '<span class="DoneMsg">Nesprávný formát EANu.</span>';;
                break;
            case "DeviceCDF":
                echo '<span class="ErrorMsg">Naskenujte QR kód.</span>';
                break;
            case "502":
                echo '<span class="WarningMsg">Špatný nebo chybějící status.</span>';
                break;
            case "MissEAN":
                echo '<span class="ErrorMsg">Naskenovaný EAN není v kmenových datech.</span>';
                break;
            case "BadFormat":
                echo '<span class="ErrorMsg">Špatný formát čísla balíku.</span>';
                break;
        }        
    unset($_SESSION["Error"]);
    }

echo "<br><table class='EAN_form_style'>";
echo "<tr>";
echo    "<td>";
echo        "<form method='GET' id='EAN'>";
echo        "<fieldset>";
echo                "<legend>Naskenujte:</legend>";
echo                "<label for='Codentify' class='label-TradeIN'>Codentify/EAN:</label>";
echo                "<input type='text' id='Codentify' name='Codentify' onchange='document.getElementById(\"EAN\").submit()'autofocus>";
echo        "</fieldset>";
echo        "</form>";
echo    "</td>";
echo    "<td></td>";
echo    "<td>";
echo        "<fieldset>";
echo            "<legend>Data zásilky:</legend>";
echo            "<label for='Reference' class='label-TradeIN'>Reference:</label>";
echo            "<input type='text' id='Reference' name='Reference' value=" . $_SESSION['Reference'] ." disabled><br><br>";
echo            "<label for='PARCELNO' class='label-TradeIN'>Číslo balíku: </label>";
echo            "<input type='text' id='PARCELNO' name='PARCELNO' value= " . $_SESSION['PARCELNO'] . " disabled><br>";
echo        "</fieldset>";
echo    "</td>";
echo "</tr>";
echo "</table>";
echo "<br>";
echo    "<fieldset>";
echo        "<legend>Obsah při odeslání zásilky: </legend><br>";
            NonDlv_ORDITEM();
echo    "</fieldset><br>";
echo    "<fieldset>";
echo        "<legend>Naskenované zařízení: </legend><br>";
            NonDlv_SCNITEM();
echo    "</fieldset><br>";

echo "<fieldset class='Buttons'>";
echo    "<legend>Volby: </legend>";
echo    "<table>";
echo        "<tr>";
echo            "<form method='GET'>";
echo               "<th><input type='submit' onclick='' class='Button' name='Save' id='Save' value='Uložit'></th>";
echo            "</form>";
echo            "<th></th>";
echo            "<th><input type='submit' onclick=\"Confirmation('Exit')\" class='Button' name='Back' id='Back' value='Zpět'></th>";
echo            "<th></th>";
echo            "<form method='GET'>";
echo               "<th><input type='submit' onclick='' class='Button' name='Codentify' id='Empty' value='Prázdné'></th>";
echo            "</form>";
echo        "</tr>";
echo    "</table>";
echo "</fieldset><br>";
}


function NonDlv_ORDITEM()
{
    if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}
    if (!isset($_SESSION['NonDlv_ORDITEM']) and empty($_SESSION['NonDlv_ORDITEM']))
        {
        $SQL = 'SELECT [Material],[MAKTX],[EAN],[EAN_CRT],[Codentify],[ScanQuantity],[OrdQuantity],[Sum],[ORDTyp] FROM [DPD_DB].[dbo].[NonDlv_Dvc_Sum_View] WHERE ([REFERENCE] = :REFERENCE)';
        $params = array(':REFERENCE' => $_SESSION['Reference']);
        $stmt = $Connection->select($SQL, $params);            
        $rows = $stmt['rows'];
        $count = $stmt['count'];
        foreach ($rows as $row) 
        {
        $rowData = array();
        foreach ($row as $key => $value) {
            $rowData[$key] = $value;
        }
        $data[] = $rowData;
        }
        if (isset($data)) {$_SESSION['NonDlv_ORDITEM'] = $data;}            
        }
    else
        {
        $rows = $_SESSION['NonDlv_ORDITEM'];
        }
        
    $columnNames = ['Produkt', 'Název produktu', 'EAN PACK', 'EAN CRT', 'Codentify', 'Skenováné', 'Objednané', 'Celkem'];
    echo "<table border='2' cellspacing='1' cellpadding='5'>";
    echo '<tr>';
    for ($i = 0; $i < count($columnNames); $i++) 
        {
        echo '<th>' . $columnNames[$i] . '</th>';
        }
        echo '</tr>';
    foreach ($rows as $row) 
        {
        echo '<tr>';
        foreach ($row as $key => $value)
            {
            if ($key !== 'Checker' and $key !== 'ORDTyp')
                {
                echo '<td>' . $value . '</td>';
                }
            }
        echo '</tr>';
        }
    echo '</table>';
    echo '<br>';
}


function NonDlv_SCNITEM()
{
    if (isset($_SESSION['NonDlv_SCNITEM']) and !empty($_SESSION['NonDlv_SCNITEM']))
    {
    $rows = $_SESSION['NonDlv_SCNITEM'];
    foreach ($rows as $row) 
        {
        $rowData = array();
        foreach ($row as $key => $value) {
            $rowData[$key] = $value;
        }
        $data[] = $rowData;
        }
    $_SESSION['NonDlv_SCNITEM'] = $data;
    }

    $columnNames = ['Produkt','Název produktu', 'EAN','EAN_CRT', 'Codentify', 'Datum', 'Množství'];
    echo "<table border='2' cellspacing='1' cellpadding='5'>";
    echo '<tr>';
    for ($i = 0; $i < count($columnNames); $i++) {
        echo '<th>' . $columnNames[$i] . '</th>';
    }
    echo '</tr>';
    $ButtonID = 0;
    if (isset($rows)) {
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $key => $value) 
                {
                if ($key == 'Reference' or $key == 'Checker')
                    {}
                else
                    {
                    echo '<td>' . $value . '</td>';
                    }
                }
            echo    "<td>";
            echo    "<form method='GET'>";
            echo    "<button type='submit' name='ScanArrayID' id='ScanArrayID' value='".$ButtonID."' onclick=''>Smazat</button>";
            echo    "</form>";
            echo    "</td>";            
            echo '</tr>';
            $ButtonID ++;
        }
    }
    echo '</table>';
    echo '<br>';
}

?>
<script>
function callCheckCDF() {
    CheckCDF();
}
</script>
</body>
</html>