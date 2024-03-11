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
<script src="ProjectFunc.js"></script>
<script src="SWAP_form.js"></script>

</head>
<body>
    <header>
        <h1>PMI BO Tool</h1>
        <?php require 'navigation.php'; ?>
    </header>
    <br>


<?php
session_start();
require 'SQLconn.php';
require 'ProjectFunc.php'; 

/******************************************************************************************************************************************************************************/
//return data from js
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{    
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
 
 $Order = $data["SWAP_ORDITEM"];
 foreach($Order as $OrdIndex)
    {
    $ID = $OrdIndex["id"];
//find article number and name 
    if ($OrdIndex['data']["Material"] == '')
        {
        if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}
            $SQL = "SELECT [MATNR],[MAKTX] FROM [DPD_DB].[dbo].[EAN] WHERE (([EAN_PK] = :EAN AND LastEAN = 1) OR ([EAN_CT]  = :EAN1 AND LastEAN = 1))";
            $params = array('EAN' => $OrdIndex['data']["EAN"], 'EAN1' => $OrdIndex['data']["EAN"]);
            $stmt = $Connection->select($SQL, $params);
            $count = $stmt['count'];
        if($count !== 0 )   
            {
            $rows = $stmt['rows'];
            foreach ($Order as $key => $row )
                {
                if ($row["id"] == $ID)
                    {
                    $Order[$key]["data"]["Material"] = $rows[0]['MATNR'];
                    $Order[$key]["data"]["MAKTX"] = $rows[0]['MAKTX'];
                    break;
                    } 
                }
            }
        } 
     }
unset($row);
$_SESSION["SWAP_ORDITEM"] = $Order;

/******************************************************************************************************************************************************************************/
$Scan = $data['SWAP_SCNITEM'];
foreach($Scan as $ScnIndex)
    {
    $ID = $ScnIndex["id"];
//find article number and name 
    if ($ScnIndex['data']["Product"] == '')
        {
        if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}
            $SQL = "SELECT [MATNR],[MAKTX] FROM [DPD_DB].[dbo].[EAN] WHERE (([EAN_PK] = :EAN AND LastEAN = 1) OR ([EAN_CT]  = :EAN1 AND LastEAN = 1))";
            $params = array('EAN' => $ScnIndex['data']["EAN"], 'EAN1' => $ScnIndex['data']["EAN"]);
            $stmt = $Connection->select($SQL, $params);
            $count = $stmt['count'];
        if($count !== 0 )   
            {
            $rows = $stmt['rows'];
            foreach ($Scan as $key => $row )
                {
                if ($row["id"] == $ID)
                    {
                    $Scan[$key]["data"]["Product"] = $rows[0]['MATNR'];
                    $Scan[$key]["data"]["ProductName"] = $rows[0]['MAKTX'];
                    break;
                    }
                }
            }
        }
    } 
$_SESSION['SWAP_SCNITEM'] =  $Scan;  
unset($row);
}

/******************************************************************************************************************************************************************************/
if ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
//Open forms
    if(isset($_GET["Open"]))
    {
    SWAP_Form();
    }
/******************************************************************************************************************************************************************************/
//exit pop-up windows
    elseif (isset($_GET["Menu"])) 
    {
        if($_GET["Menu"] == 'no')
        {
        $PN = $_SESSION['PARCELNO'];
        GetPNorRef($PN);  
        }
        elseif($_GET["Menu"] == 'yes')
        {
        unset($_SESSION['PARCELNO']);
        unset($_SESSION['Reference']);
        unset($_SESSION["SWAP_ORDITEM"]);
        unset($_SESSION["SWAP_SCNITEM"]);
        unset($_SESSION['PARCELNO']);
        header("Location: SWAP.php");
        }
    }

/******************************************************************************************************************************************************************************/
//scan to ean field    
    elseif (isset($_GET["Notmatch"])) 
    {
        if($_GET["Notmatch"] == 'no')
        {
        die;
        }
        elseif($_GET["Notmatch"] == 'yes')
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

/******************************************************************************************************************************************************************************/
    //scan to codentify field    
    elseif(isset($_GET["Codentify"]))
        {
        // if push or not button Prázdné(Empty)
        if ($_GET["Codentify"] !== "Prázdné")
            {
            $Codentify = strtoupper(trim($_GET["Codentify"]));
            if(substr($Codentify, 0, 1) == 'T')
                {     
                if(isset($_SESSION['SWAP_SCNITEM']))
                {
            // check duplicite codentify in order            
                    foreach ($_SESSION['SWAP_SCNITEM'] as $row)
                    {
                    if ($row['data']["Codentify"] == $Codentify)
                        {
                        $_SESSION["Error"] = 'SameCDF';
                        header("Location: SWAP_Form.php?Open=");
                        die;          
                        }
                    }
                }
                // JavaScript function to prompt user for EAN
                $orditem = isset($_SESSION["SWAP_ORDITEM"]) ? json_encode($_SESSION["SWAP_ORDITEM"]) : "[]";
                if(!isset($_SESSION['SWAP_SCNITEM'])){$_SESSION['SWAP_SCNITEM'] = [];}
                $scnitem = isset($_SESSION['SWAP_SCNITEM']) ? json_encode($_SESSION['SWAP_SCNITEM']) : "[]";
                $Scan = json_encode($Codentify);
                echo "<script>CheckCDF(" . $orditem . "," . $Scan . "," . $scnitem . ",". $_SESSION["Reference"] . ");</script>";
                }
            else
                {
                $_SESSION["Error"] = 'FormatCDF';
                header("Location: SWAP_Form.php?Open=");            
                }
            }
        // if push or not button Prázdné(Empty)
        else 
            {
            $orditem =  $_SESSION["TRADEIN_ORDITEM"];
            if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}
            foreach ($orditem as $row)
                {
                $data = array('REFERENCE' => $_SESSION["Reference"], 'EAN' => $row["data"]["EAN"],'Codentify' => $row["data"]["Codentify"], 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => 0 );
                $Connection->insert("TRADEIN_Dvc", $data);
                }
                unset($_SESSION['PARCELNO']);
                unset($_SESSION['Reference']);
                unset($_SESSION["TRADEIN_ORDITEM"]);
                unset($_SESSION["TRADEIN_SCNITEM"]);
                unset($_SESSION['PARCELNO']);
                header("Location: TRADEIN.php");
            }
        }

/******************************************************************************************************************************************************************************/
    //Delete scanned article
    elseif(isset($_GET["DeleteID"]))
    {
    $ID=$_GET["DeleteID"];
      foreach ($_SESSION["SWAP_ORDITEM"] as $key => $row )
        {
        if ($row["id"] == $ID)
            {
            if ($_SESSION["SWAP_ORDITEM"][$key]["data"]["Checker"] !== 'Other')
                {
                $_SESSION["SWAP_ORDITEM"][$key]["data"]["Sum"] = $_SESSION["SWAP_ORDITEM"][$key]["data"]["Sum"]  - 1;
                $_SESSION["SWAP_ORDITEM"][$key]["data"]["ScanQuantity"] = $_SESSION["SWAP_ORDITEM"][$key]["data"]["ScanQuantity"]  - 1;
                }
            else
                {
                unset($_SESSION["SWAP_ORDITEM"][$key]);
                }
            break;
            }
        }

    $key = 0;
    foreach ($_SESSION["SWAP_SCNITEM"] as $key => $row )
        {
        if ($row["id"] == $ID)
            {
            unset($_SESSION["SWAP_SCNITEM"][$key]);
            break;
            }
        }                  
    header("Location: SWAP_form.php?Open=");
    }

/******************************************************************************************************************************************************************************/
    //Saving
    elseif(isset($_GET["Save"]))
    { 
    if (!isset($Connection)) {$Connection = new PDOConnect("DPD_DB");}
    if (isset($_SESSION["SWAP_ORDITEM"]))
        {
        $Orders = $_SESSION["SWAP_ORDITEM"];
        $Scan = $_SESSION["SWAP_SCNITEM"];
        foreach($Scan as $ScnIndex)
            {
            if ($ScnIndex['data']["Checker"] !== 'Other')
                {
                $data = array('REFERENCE' => $ScnIndex['data']["Reference"], 'EAN' => $ScnIndex['data']["EAN"],'Codentify' => $ScnIndex['data']["Codentify"], 'Scantime' => $ScnIndex['data']["DateTime"], 'Quantity' => $ScnIndex['data']["ScanQuantity"] );
                $Connection->insert("SWAP_Dvc", $data);
                }
            else
                {
                $data = array('REFERENCE' => $ScnIndex['data']["Reference"], 'EAN' => $ScnIndex['data']["EAN"],'Codentify' => $ScnIndex['data']["Codentify"], 'Scantime' => $ScnIndex['data']["DateTime"], 'Quantity' => $ScnIndex['data']["ScanQuantity"] );
                $Connection->insert("SWAP_Dvc", $data);
                $data = array('REFERENCE' => $ScnIndex['data']["Reference"], 'ORDTyp' => 'PR1', 'Material' => $ScnIndex['data']["Product"],'Quantity' => 0,'Codentify' => $ScnIndex['data']["Codentify"] );
                $Connection->insert("OrderItems", $data);
                }
            }

        $Order = $_SESSION["SWAP_ORDITEM"];
        foreach($Order as $OrdIndex)
            {
            if ($OrdIndex['data']["Checker"] == 'Original')
                {
                $data = array('REFERENCE' => $_SESSION["Reference"], 'EAN' => $OrdIndex['data']["EAN"],'Codentify' => $OrdIndex['data']['Codentify'], 'Scantime' => date('Y-m-d H:i:s'), 'Quantity' => 0 );
                $Connection->insert("SWAP_Dvc", $data);
                }
            }
        unset($_SESSION['PARCELNO']);
        unset($_SESSION['Reference']);
        unset($_SESSION["OTHER_ITEM"]);
        unset($_SESSION["SWAP_ORDITEM"]);
        unset($_SESSION["SWAP_SCNITEM"]);
        unset($_SESSION["Error"]);
        header("Location: SWAP.php");
        echo '<span class="DoneMsg">Záznam byl uložen na server.</span><br><br>';
        }
    else
        {
        header("Location: SWAP_form.php?Open=");           
        }
    }
}

/******************************************************************************************************************************************************************************/
function SWAP_Form()
{
echo "<table class='EAN_form_style'>";
echo "<tr>";
echo    "<td>";
echo        "<form method='GET' id='EAN'>";
echo        "<fieldset>";
echo            "<legend>Naskenujte:</legend>";
echo            "<label for='Codentify' class='label-SWAP'>Codentify/EAN:</label>";
echo             "<input type='text' id='Codentify' name='Codentify' onchange='document.getElementById(\"EAN\").submit()'autofocus>";
echo        "</fieldset>";
echo        "</form>";
echo    "</td>";
echo    "<td></td>";
echo    "<td>";
echo        "<fieldset>";
echo            "<legend>Data zásilky:</legend>";
echo            "<label for='Reference' class='label-SWAP'>Reference:</label>";
echo            "<input type='text' id='Reference' name='Reference' value=" . $_SESSION['Reference'] ." disabled><br><br>";
echo            "<label for='PARCELNO' class='label-SWAP'>Číslo balíku: </label>";
echo            "<input type='text' id='PARCELNO' name='PARCELNO' value= " . $_SESSION['PARCELNO'] . " disabled><br>";
echo        "</fieldset>";
echo    "</td>";
echo "</tr>";
echo "</table>";
echo "<br>";

/******************************************************************************************************************************************************************************/
if (isset($_SESSION["Error"])) 
{   
    switch ($_SESSION["Error"]) 
    {
        case "Done":
            echo '<span class="DoneMsg">Záznam byl uložen na server.</span><br><br>';
            break;
        case "DeviceCDF":
            echo '<span class="ErrorMsg">Naskenujte QR kód.</span><br><br>';
            break;
        case "SameCDF":
            echo '<span class="WarningMsg">Toto codentify již bylo naskenováno.</span><br><br>';
            break;
        case "FormatCDF":
            echo '<span class="ErrorMsg">Neskanovali jste špatný format codentify.</span><br><br>';
            break;
    }        
unset($_SESSION["Error"]);
}

/******************************************************************************************************************************************************************************/
echo    "<fieldset>";
echo    "<legend>Obsah při odeslání zásilky: </legend><br>";
            SWAP_ORDITEM();
echo    "</fieldset><br>";
echo    "<fieldset>";
echo    "<legend>Naskenované zařízení: </legend><br>";
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
echo            "<form method='GET'>";
echo               "<th><input type='submit' onclick='' class='Button' name='Codentify' id='Empty' value='Prázdné'></th>";
echo            "</form>";
echo        "</tr>";
echo    "</table>";
echo "</fieldset><br>";
}

/******************************************************************************************************************************************************************************/
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
        if ($count !== 0 )
            {
            $ID = 0;
            foreach ($rows as $row) 
                {
                foreach ($row as $key => $value) 
                    {
                    $rowData[$key] = $value;
                    }
                $rowData["Checker"]='Original';
                $Session[] = array(
                    'id' => $ID,
                    'data' => $rowData);
                $ID++;
                }
            $_SESSION['SWAP_ORDITEM'] =  $Session;
            $rows = $Session;
            }
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
        foreach ($row['data'] as $key => $value)
            {
            switch ($key) 
            {
                case 'Checker':
                    break;       
                default:
                echo '<td>' . $value . '</td>';
                    break;
            }
            }
        echo '</tr>';
        }
    echo '</table>';
    echo '<br>';
}

/******************************************************************************************************************************************************************************/
function SWAP_SCNITEM()
{
    if (isset($_SESSION['SWAP_SCNITEM']) and !empty($_SESSION['SWAP_SCNITEM']))
    {
    $rows = $_SESSION['SWAP_SCNITEM'];
    }

    $columnNames = ['Produkt','Název produktu', 'EAN', 'Codentify', 'Datum', 'Množství'];
    echo "<table border='2' cellspacing='1' cellpadding='5'>";
    echo '<tr>';
    for ($i = 0; $i < count($columnNames); $i++) {
        echo '<th>' . $columnNames[$i] . '</th>';
    }
    echo '</tr>';
    if (isset($rows)) {
        foreach ($rows as $row) {
            echo '<tr>';
            $ButtonID = $row['id'];
            foreach ($row['data'] as $key => $value) 
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
            echo    "<button type='submit' name='DeleteID' id='DeleteID' value='".$ButtonID."'>Smazat</button>";
            echo    "</form>";
            echo    "</td>";            
            echo '</tr>';
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