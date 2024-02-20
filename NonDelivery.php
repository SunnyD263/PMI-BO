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
<?php
session_start();
require 'SQLconn.php';
require 'ProjectFunc.php'; 
If ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
    if (isset($_GET["Open"]))
        {
        //$DT=getWorkingDay(date("Y-m-d"));
        $_SESSION["Date"] = date("Y-m-d");
        NonDelivery_main($_SESSION["Date"]);        
        }
    elseif (isset($_GET["Input"])) 
        {  
        GetPNorRef($_GET["Input"]);  
        }
    elseif (isset($_GET["ChngDate"]))
        {
        $_SESSION["Date"] = $_GET["ChngDate"];
        NonDelivery_main($_SESSION["Date"]);           
        }
    else
        {
        if (!isset($_SESSION["Date"])) {$_SESSION["Date"] = date("Y-m-d"); }
        NonDelivery_main($_SESSION["Date"]);
        }
}   

function GetPNorRef($input) {
    $Input = trim($input);
    $Result = new InputValue($Input);
    $PN = $Result->ParcelNumber()[0];
    $NumOrRef = $Result->ParcelNumber()[1];
    if (!isset($Connection)) 
    {$Connection = new PDOConnect("DPD_DB");}

    if ($NumOrRef == "NUM") 
    {
        $SQL = "SELECT [Reference],[PARCELNO] FROM [NonDlv_Dvc_View] WHERE ([PARCELNO] = :parcelno)";
        $params = array(':parcelno' => $PN);
        $stmt = $Connection->select($SQL, $params);
        $count = $stmt['count'];
        $rows =  $stmt['rows'];
        if ($count === false || $count === null || $count === 0)
            {
            echo '<span class="ErrorMsg">Databáze neobsahuje toto číslo palety.</span>';
            if (!isset($_SESSION["Date"])) {$_SESSION["Date"] = date("Y-m-d"); }
            NonDelivery_main($_SESSION["Date"]);
            die;
            } 
        else 
            {
            if (!isset($_SESSION)) {session_start();}
            $row = $rows[0];
            $_SESSION['Reference'] = $row['Reference'];
            $_SESSION['PARCELNO'] = $row['PARCELNO'];
            if (isset($_SESSION["NonDlv_ORDITEM"])){unset($_SESSION["NonDlv_ORDITEM"]);}
            if (isset($_SESSION["NonDlv_SCNITEM"])){unset($_SESSION["NonDlv_SCNITEM"]);}
            header("Location: NonDelivery_form.php?Open=");
            }
    }
    else
    {   echo '<span class="ErrorMsg">Neznámý formát čísla.</span>';
        if (!isset($_SESSION["Date"])) {$_SESSION["Date"] = date("Y-m-d"); }
        NonDelivery_main($_SESSION["Date"]);
        die;
    }
}

function NonDelivery_main($DT) 
    { 
    
    echo "<div class='ScanParcel'>";
    echo "<h1><b><strong>= Nedoručené zářízení =</strong></b></h1>";
    echo "<form method='get' class='InputPN'>";
    echo "<label for='ChngDate'>Vyberte datum:</label><p>";
    echo "<input type='date' id='ChngDate' name='ChngDate' value ='". $DT ."'><p>";
    echo "<input type='submit' value='Odeslat'>";
    echo "</form>";

    echo "<form  method='get' class='InputPN'>";
    echo "<label for='Input' id='Inplbl'>Naskenujte číslo balíku:</label><br>";
    echo "<input type='text' id='Input' name='Input' autofocus><br><br>";
    echo "</form>";
    echo "</div>";
    echo "<br>";

    if (!isset($Connection)) 
        {$Connection = new PDOConnect("DPD_DB");}
        $SQL = "SELECT [REFERENCE] ,[Carrier],[PARCELNO],[Customer],[Street],[City],[EVENT_DATE_TIME],[Sum] FROM [DPD_DB].[dbo].[NonDlv_Dvc_View] WHERE CONVERT(date, EVENT_DATE_TIME) = :DT ORDER by EVENT_DATE_TIME";
        $params = array(':DT' => $DT);
        $stmt = $Connection->select($SQL,$params);
        
        $rows = $stmt['rows'];
        $count = $stmt['count'];
        
        echo "Počet záznamů: " . $count . "<br>";
        
        $columnNames = ['Reference','Dopravce','Číslo balík','Zákazník', 'Ulice', 'Město','Datum příjmu','Info'];
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