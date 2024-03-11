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
<?php
session_start();
require 'SQLconn.php';
require 'ProjectFunc.php'; 

/******************************************************************************************************************************************************************************/
If ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
    if (isset($_GET["Input"])) 
        {  
        GetPNorRef($_GET["Input"]);  
        }
    else
        {
        if(isset($_SESSION['SumLocation'])){unset($_SESSION['SumLocation']);}
        if(isset($_SESSION['SWAP_Dvc_sum_View'])){unset($_SESSION['SWAP_Dvc_sum_View']);}
        SWAP_main();
        }
}   

/******************************************************************************************************************************************************************************/
function GetPNorRef($input) 
{
$Input = trim($input);
$Result = new InputValue($Input);
$PN = $Result->ParcelNumber()[0];
$NumOrRef = $Result->ParcelNumber()[1];
if (!isset($Connection)) 
{$Connection = new PDOConnect("DPD_DB");}
if ($NumOrRef == "Pal") 
{   
    $SQL = "SELECT [Reference],[PARCELNO] FROM [SWAP_Dvc_View] WHERE ([REFERENCE] = :reference)";
    $params = array(':reference' => $PN);
    $stmt = $Connection->select($SQL, $params);
    $count = $stmt['count'];
    $rows =  $stmt['rows'];
    if ($count === false || $count === null || $count === 0)
    {
        echo '<span class="ErrorMsg">Databáze neobsahuje toto číslo palety.</span>';
        SWAP_main();
        die;
    } 
    else 
    {
        if (!isset($_SESSION)) {session_start();}
        $row = $rows[0];
        $_SESSION['Reference'] = $row['Reference'];
        if (isset($_SESSION["SWAP_ORDITEM"])){unset($_SESSION["SWAP_ORDITEM"]);}
        if (isset($_SESSION["SWAP_SCNITEM"])){unset($_SESSION["SWAP_SCNITEM"]);}
        header("Location: SWAP_form.php?Open=");
    }
} 
elseif ($NumOrRef == "NUM") 
{
    $SQL = "SELECT [Reference],[PARCELNO] FROM [SWAP_Dvc_View] WHERE ([PARCELNO] = :parcelno) or ([REFERENCE] = :reference) ";
    $params = array(':parcelno' => $PN, ':reference' => $PN);
    $stmt = $Connection->select($SQL, $params);
    $count = $stmt['count'];
    $rows =  $stmt['rows'];
    if ($count === false || $count === null || $count === 0)
    {
        echo '<span class="ErrorMsg">Databáze neobsahuje toto číslo palety.</span>';
        SWAP_main();
        die;
    } 
    else 
    {
        if (!isset($_SESSION)) {session_start();}
        $row = $rows[0];
        $_SESSION['Reference'] = $row['Reference'];
        $_SESSION['PARCELNO'] = $row['PARCELNO'];
        if (isset($_SESSION["SWAP_ORDITEM"])){unset($_SESSION["SWAP_ORDITEM"]);}
        if (isset($_SESSION["SWAP_SCNITEM"])){unset($_SESSION["SWAP_SCNITEM"]);}
        header("Location: SWAP_form.php?Open=");
    }
}
else
{   echo '<span class="ErrorMsg">Neznámý formát čísla.</span>';
    SWAP_main();
    die;
}
}

/******************************************************************************************************************************************************************************/
function SWAP_main() 
{
echo "<div class='ScanParcel'>";
echo "<h1><b><strong>= Příjem SWAP =</strong></b></h1>";
echo "<form  method='get' class='InputPN'>";
echo "<label for='Input' id='Inplbl'>Naskenujte číslo balíku:</label><br>";
echo "<input type='text' id='Input' name='Input' autofocus><br><br>";
echo "<input type='submit' value='Potvrdit'>";
echo "</form>";
echo "</div>";
echo "<br>";
if (!isset($Connection)) 
    {$Connection = new PDOConnect("DPD_DB");}
    $SQL = "SELECT [REFERENCE],[PARCELNO],[Customer],[Street],[City],[EVENT_DATE_TIME],[Sum] FROM [DPD_DB].[dbo].[SWAP_Dvc_View] where Sum < 0 and EVENT_DATE_TIME >  DATEADD(Day,-14,GETDATE()) or SUM IS NULL ORDER by EVENT_DATE_TIME";
    $stmt = $Connection->select($SQL);
    
    $rows = $stmt['rows'];
    $count = $stmt['count'];
    
    echo "Počet záznamů: " . $count . "<br>";
    
    $columnNames = ['Reference','Číslo balík','Zákazník', 'Ulice', 'Město','Datum příjmu','Info'];
    echo '<table border="2" cellspacing="1" cellpadding="5">';
    echo '<tr>';
    for ($i = 0; $i < count($columnNames); $i++) {
        echo '<th>' . $columnNames[$i] . '</th>';
    }
    echo '</tr>';
    
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $key => $value) {
            echo '<td>' . $value . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
}
?>
</body>