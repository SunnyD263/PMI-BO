<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Příjem SWAP</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="Příjem SWAP" />
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
<script src="SWAP_form.js"></script>
<?php
session_start();
require 'SQLconn.php';
require 'ProjectFunc.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{    
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
 
$_SESSION["SWAP_ORDITEM"] = $data["SWAP_ORDITEM"];
$Scan = $data['SWAP_SCNITEM'];
$ID=0;
foreach($Scan as $ScnIndex)
    {
    if ($ScnIndex["Product"] == '')
        {
        if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}
            $SQL = "SELECT [MATNR],[MAKTX] FROM [DPD_DB].[dbo].[EAN] WHERE (([EAN_PK] = :EAN AND LastEAN = 1) OR ([EAN_CT]  = :EAN1 AND LastEAN = 1))";
            $params = array('EAN' => $ScnIndex["EAN"], 'EAN1' => $ScnIndex["EAN"]);
            $stmt = $Connection->select($SQL, $params);
            $count = $stmt['count'];
        if($count !== 0 )   
            {
            $rows = $stmt['rows'];
            $Scan[$ID]["Product"]=$rows[0]['MATNR'];
            $Scan[$ID]["ProductName"]=$rows[0]['MAKTX'];
            }
        else
            {

            }
        }
    $ID++;    
    }
$_SESSION['SWAP_SCNITEM'] =  $Scan;
}
If ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
//Open forms
    If(isset($_GET["Open"]))
    {
    SWAP_Form();
    }
//Pop-up windows
    elseif (isset($_GET["Menu"])) 
    {
        if($_GET["Menu"]=='no')
        {
        $PN = $_SESSION['PARCELNO'];
        GetPNorRef($PN);  
        }
        elseif($_GET["Menu"]=='yes')
        {
        unset($_SESSION['PARCELNO']);
        unset($_SESSION['Reference']);
        unset($_SESSION["SWAP_ORDITEM"]);
        unset($_SESSION["SWAP_SCNITEM"]);
        unset($_SESSION['PARCELNO']);
        header("Location: SWAP.php");
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

    $orditem = isset($_SESSION["SWAP_ORDITEM"]) ? json_encode($_SESSION["SWAP_ORDITEM"]) : "[]";
    if(!isset($_SESSION['SWAP_SCNITEM'])){$_SESSION['SWAP_SCNITEM'] = [];}
    $scnitem = isset($_SESSION['SWAP_SCNITEM']) ? json_encode($_SESSION['SWAP_SCNITEM']) : "[]";
    $Scan = json_encode(trim($_GET["Codentify"]));

    echo "<script>CheckCDF(" . $orditem . "," . $Scan . "," . $scnitem . ",". $_SESSION["Reference"] . ");</script>";
    }
//Delete scanned article
    elseif(isset($_GET["ScanArrayID"]))
    {
        $key=$_GET["ScanArrayID"];
        if (array_key_exists($key, $_SESSION["SWAP_SCNITEM"])) 
        {
            $_SESSION["SWAP_ORDITEM"][$key]['Sum'] =  $_SESSION["SWAP_ORDITEM"][$key]['Sum'] - $_SESSION["SWAP_SCNITEM"][$key]["ScanQuantity"];           
            $_SESSION["SWAP_ORDITEM"][$key]['ScanQuantity'] =  $_SESSION["SWAP_ORDITEM"][$key]['ScanQuantity'] - $_SESSION["SWAP_SCNITEM"][$key]["ScanQuantity"] ;  
            unset($_SESSION["SWAP_SCNITEM"][$key]);
            header("Location: SWAP_form.php?Open=");
        }
    }
//Saving
    elseif(isset($_GET["Save"]))
    { 
    if (!isset($Connection)) {$Connection = new PDOConnect("DPD_DB");}
    $FinalSum = 0;
    if (isset($_SESSION["SWAP_ORDITEM"]))
        {
        $Orders = $_SESSION["SWAP_ORDITEM"];
        foreach($Orders as $Index)
            {
            $FinalSum = $FinalSum + $Index["Sum"];
            }
        if($FinalSum == 0)
            {

            }

        $Scan = $_SESSION["SWAP_SCNITEM"];
        foreach($Scan as $ScnIndex)
            {
            if ($ScnIndex["Checker"] !== 'Other')
                {
                $data = array('REFERENCE' => $ScnIndex["Reference"], 'EAN' => $ScnIndex["EAN"],'Codentify' => $ScnIndex["Codentify"], 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => $ScnIndex["ScanQuantity"] );
                $Connection->insert("SWAP_Dvc", $data);
                }
            else
                {
                $data = array('REFERENCE' => $ScnIndex["Reference"], 'EAN' => $ScnIndex["EAN"],'Codentify' => $ScnIndex["Codentify"], 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => $ScnIndex["ScanQuantity"] );
                $Connection->insert("SWAP_Dvc", $data);
                $data = array('REFERENCE' => $ScnIndex["Reference"], 'ORDTyp' => 'PR4', 'Material' => $ScnIndex["Product"],'Quantity' => $ScnIndex["ScanQuantity"],'Codentify' => 'XXXXXXXXXXXXXX' );
                $Connection->insert("OrderItems", $data);
                }
            }
        $Order = $_SESSION["SWAP_ORDITEM"];
        foreach($Order as $OrdIndex)
            {
            if ($OrdIndex["Checker"] == '')
                {
                $data = array('REFERENCE' => $_SESSION["Reference"], 'EAN' => $OrdIndex["EAN"],'Codentify' => 'EMPTY', 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => 0 );
                $Connection->insert("SWAP_Dvc", $data);
                }
            }
        unset($_SESSION['PARCELNO']);
        unset($_SESSION['Reference']);
        unset($_SESSION["OTHER_ITEM"]);
        unset($_SESSION["SWAP_ORDITEM"]);
        unset($_SESSION["SWAP_SCNITEM"]);
        header("Location: SWAP.php");
        echo '<span class="DoneMsg">Záznam byl uložen na server.</span>';
        }
    else
        {
        header("Location: SWAP_form.php?Open=");           
        }
    }
}

function SWAP_Form()
{
echo "<table class='EAN_form_style'>";
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
            SWAP_ORDITEM();
echo    "</fieldset><br>";
echo    "<fieldset>";
echo        "<legend>Naskenované zařízení: </legend><br>";
            SWAP_SCNITEM();
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
echo        "</tr>";
echo    "</table>";
echo "</fieldset><br>";
}


function SWAP_ORDITEM()
{
    if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}
    if (!isset($_SESSION['SWAP_ORDITEM']) and empty($_SESSION['SWAP_ORDITEM']))
        {
        $SQL = 'SELECT [Material],[MAKTX],[EAN],[EAN_CRT],[Codentify],[ScanQuantity],[OrdQuantity],[Sum] FROM [DPD_DB].[dbo].[SWAP_Dvc_sum_View] WHERE ([REFERENCE] = :REFERENCE)';
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
        if (isset($data)) {$_SESSION['SWAP_ORDITEM'] = $data;}            
        }
    else
        {
        $rows = $_SESSION['SWAP_ORDITEM'];
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
            if ($key !== 'Checker')
                {
                echo '<td>' . $value . '</td>';
                }
            }
        echo '</tr>';
        }
    echo '</table>';
    echo '<br>';
}


function SWAP_SCNITEM()
{
    if (isset($_SESSION['SWAP_SCNITEM']) and !empty($_SESSION['SWAP_SCNITEM']))
    {
    $rows = $_SESSION['SWAP_SCNITEM'];
    foreach ($rows as $row) 
        {
        $rowData = array();
        foreach ($row as $key => $value) {
            $rowData[$key] = $value;
        }
        $data[] = $rowData;
        }
    $_SESSION['SWAP_SCNITEM'] = $data;
    }

    $columnNames = ['Produkt','Název produktu', 'EAN', 'Codentify', 'Datum', 'Množství'];
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