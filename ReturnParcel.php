<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Vrácené balíky</title>
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
<?php
session_start();
require 'SQLconn.php';
require 'ProjectFunc.php'; 
require 'Packeta_import.php';
require 'PPL_import.php';

If ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
//Open forms
    If(isset($_GET["Open"]))
    {
    Return_main() ;
    }
elseif (isset($_GET["Input"])) 
    {  
    GetPNorRef($_GET["Input"]);  
    header("Location: ReturnParcel.php?Open=");              
    }
else
    {
    Return_main(); 
    }
}

/******************************************************************************************************************************************************************************/
function GetPNorRef($input) {
    $Input = trim($input);
    $Result = new InputValue($Input);
    $PN = $Result->ParcelNumber()[0];
    $NumOrRef = $Result->ParcelNumber()[1];
    $Courier = $Result->ParcelNumber()[2];
    if (!isset($Connection)) 
    {$Connection = new PDOConnect("DPD_DB");}
    if ($NumOrRef == "NUM") 
    {
    
        $SQL = "SELECT [ID],[Reference],[PARCELNO] FROM [Returned_View] WHERE ([PARCELNO] = :parcelno)";
        $params = array(':parcelno' => $PN);
        $stmt = $Connection->select($SQL, $params);
        $count = $stmt['count'];

        //  unexpected parcel number or already scanned
        if ($count === false || $count === null || $count === 0)
            {
                $SQL = "SELECT [PARCELNO],[SCAN_CODE] FROM [PMIdB] WHERE ([PARCELNO] = :parcelno AND left([SCAN_CODE],2) = '50')";
                $params = array(':parcelno' => $PN);        
                $stmt1 = $Connection->select($SQL, $params);
                $count1 = $stmt1['count'];
            //  unexpected parcel number
                if ($count1 === false || $count1 === null || $count1 === 0)
                    {
                    //  try new download parcel number    
                    if ($Courier = 'PPL') {PPL_import($PN);}
                    elseif ($Courier = 'Packeta') {Packeta_import($PN);}
                    $SQL = "SELECT [ID],[Reference],[PARCELNO] FROM [Returned_View] WHERE ([PARCELNO] = :parcelno)";
                    $params = array(':parcelno' => $PN);
                    $stmt2 = $Connection->select($SQL, $params);
                    $count2 = $stmt2['count'];
                    if ($count2 === false || $count2 === null || $count2 === 0)
                        {
                        $SQL = "SELECT [PARCELNO],[SCAN_CODE],[Reference] FROM [PMIdB] WHERE ([PARCELNO] = :parcelno)";
                        $params = array(':parcelno' => $PN);        
                        $stmt3 = $Connection->select($SQL, $params);
                        $count3 = $stmt3['count'];
                    //  unknown pallet number from KN accounts from couriers
                        if ($count3 === false || $count3 === null || $count3 === 0)
                            {
                            InsertData(503, $PN);
                            $_SESSION["Error"] ="503";
                            }
                    //  parcel number from KN accounts from couriers but wrong or missing status
                        else
                            {
                            InsertData(502,$stmt3['rows'][0]["PARCELNO"],$stmt3['rows'][0]["Reference"]);
                            $_SESSION["Error"] ="502";                         
                            } 
                        }    
                    else
                        {
                        InsertData(501, $stmt2['rows'][0]["PARCELNO"],$stmt2['rows'][0]["Reference"], $stmt2['rows'][0]["ID"]);
                        $_SESSION["Error"] ="501";
                        }
                    }
            //  already scanned
                else
                    {
                    $rows1 =  $stmt['rows'];
                    $_SESSION["Error"] ="Scanned";
 
                    }
            }
    //  501 - expected parcel number      
        else  
            {

                InsertData(501, $stmt['rows'][0]["PARCELNO"],$stmt['rows'][0]["Reference"], $stmt['rows'][0]["ID"]);
                $_SESSION["Error"] ="501";
            }
    }
    else
    {  
        $_SESSION["Error"] ="BadFormat";
    }
}

/******************************************************************************************************************************************************************************/
Function InsertData($Code,$PN,$Reference = '',$ID = '')

    {
    if (!isset($Connection)) {$Connection = new PDOConnect("DPD_DB");}
    
        switch ($Code) {
            case 501:
                $data = array('PARCELNO' => $PN, 'SCAN_CODE' => '501', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' => $Reference, 'Source' => 'WHU', 'KN' => '','Customer' => '');
                $Connection->insert("PMIdB", $data);
        
                $data = array('PARCELNO' => $PN, 'SCAN_CODE' => '501', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' =>  $Reference, 'Source' => 'WHU', 'KN' => '');
                $Connection->insert("ScanBackup", $data);
        
                $SQL=  "UPDATE [dbo].[PMIdB] SET [KN] = 'Inbound' where ([ID] = :ID)";
                $params = array(':ID' => $ID);  
                $upd = $Connection->update($SQL,$params);
                break;
            case 502:
                $data = array('PARCELNO' => $PN, 'SCAN_CODE' => '502', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' =>  $Reference, 'Source' => 'WHU', 'KN' => '','Customer' => '');
                $Connection->insert("PMIdB", $data);

                $data = array('PARCELNO' => $PN, 'SCAN_CODE' => '502', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' =>  $Reference, 'Source' => 'WHU', 'KN' => '');
                $Connection->insert("ScanBackup", $data);
                break;
            case 503:
                $data = array('PARCELNO' => $PN, 'SCAN_CODE' => '503', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' =>  '', 'Source' => 'WHU', 'KN' => '','Customer' => '');
                $Connection->insert("PMIdB", $data);

                $data = array('PARCELNO' => $PN, 'SCAN_CODE' => '503', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' =>  '', 'Source' => 'WHU', 'KN' => '');
                $Connection->insert("ScanBackup", $data);
                break;
        }
    }

/******************************************************************************************************************************************************************************/
function Return_main() 
{

    if (isset($_SESSION["Error"])) 
    {   
        switch ($_SESSION["Error"]) 
        {
            case "501":
                echo '<span class="DoneMsg">Záznam byl přidán do databáze.</span>';;
                break;
            case "Scanned":
                echo '<span class="ErrorMsg">Databáze již obsahuje záznam o příjmu tohoto balíku.</span>';
                break;
            case "502":
                echo '<span class="WarningMsg">Špatný nebo chybějící status.</span>';
                break;
            case "503":
                echo '<span class="ErrorMsg">Balík nená avizován pro PMI, naskenujte do -> Balík mimo systém" .</span>';
                break;
            case "BadFormat":
                echo '<span class="ErrorMsg">Špatný formát čísla balíku.</span>';
                break;
        }        
    unset($_SESSION["Error"]);
    }

/******************************************************************************************************************************************************************************/
echo "<div class='ScanParcel'>";
echo "<h1><b><strong>= Příjem balíků =</strong></b></h1>";
echo "<form  method='get' class='InputPN'>";
echo "<label for='Input' id='Inplbl'>Naskenujte číslo balíku:</label><br>";
echo "<input type='text' id='Input' name='Input' autofocus><br><br>";
echo "<input type='submit' value='Potvrdit'>";
echo "</form>";
echo "</div>";
echo "<br>";
echo "<div class='TWOtable'>";
    echo "<div class='TWOtableColumn'>";
if (!isset($Connection)) 
    {$Connection = new PDOConnect("DPD_DB");}

    $SQL = "SELECT [ID],[PARCELNO],[REFERENCE],[EVENT_DATE_TIME],[SERVICE],[KN],[Source],[Notice] FROM [DPD_DB].[dbo].[Returned_View]  order by [EVENT_DATE_TIME] desc";
    $stmt = $Connection->select($SQL);

    $count = $stmt['count'];    
    echo "Počet záznamů: " . $count . "<br>";
    if ($count !== false || $count !== null || $count !== 0)
        {
        $rows = $stmt['rows'];    
        $columnNames = ['Číslo balík','Reference','Datum','Service','Zdroj','Info'];
        echo '<table border="2" cellspacing="1" cellpadding="5">';
        echo '<tr>';
        for ($i = 0; $i < count($columnNames); $i++) {
            echo '<th>' . $columnNames[$i] . '</th>';
        }
        echo '</tr>';
        
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $key => $value) 
            {
            if ($key !== 'ID' and $key !== 'KN')
                {
                echo '<td>' . $value . '</td>';
                }
            }
            echo '</tr>';
        }
        
        echo '</table>';
        }
    echo "</div>";

/******************************************************************************************************************************************************************************/
    echo "<div class='TWOtableColumn'>";
    $DT=date("Y-m-d");  
    $SQL = "SELECT [PARCELNO],[SCAN_CODE],[EVENT_DATE_TIME],[Source],[REFERENCE],[Notice] FROM [DPD_DB].[dbo].[Inbound_View] WHERE CONVERT(DATE,[EVENT_DATE_TIME]) = :DT order by [EVENT_DATE_TIME] desc";
    $params = array(':DT'=> $DT);
    $stmt = $Connection->select($SQL,$params);
    
   
    $count = $stmt['count'];
    echo "Počet záznamů: " . $count . "<br>";
    if ($count !== false || $count !== null || $count !== 0)
        {
        $rows = $stmt['rows'];
        $columnNames = ['Číslo balík','ScanCode','Datum','Zdroj','Reference','Infopole'];
        echo '<table border="2" cellspacing="1" cellpadding="5">';
        echo '<tr>';
        for ($i = 0; $i < count($columnNames); $i++) {
            echo '<th>' . $columnNames[$i] . '</th>';
        }
        echo '</tr>';
        
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $key => $value) 
            {
            if ($key !== 'ID' and $key !== 'KN')
                {
                echo '<td>' . $value . '</td>';
                }
            }
            echo '</tr>';
        }
        
        echo '</table>';
        }


    echo "</div>"; 
echo "</div>";

}
?>
</body>