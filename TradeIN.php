<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Příjem Trade-IN</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="Příjem Trade-IN" />
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
    <div Id="table" class="responsive">
<?php
session_start();
require 'SQLconn.php'; 
require 'ProjectFunc.php';
If ($_SERVER["REQUEST_METHOD"] == "GET")
{
    if (isset($_GET["Input"])) 
    {  
    GetPNorRef($_GET["Input"]);  
    }
    elseif (isset($_GET["Menu"])) 
    {
        if($_GET["Menu"]=='no')
        {
        GetPNorRef($_SESSION['PARCELNO']);  
        }
        elseif($_GET["Menu"]=='yes')
        {        
        TRDIN_main();
        die;    
        }
    }    
    elseif(isset($_GET["Save"]))
    {   if (!isset($Connection)) 
        {$Connection = new PDOConnect("DPD_DB");}     
        $SQL=  "UPDATE [dbo].[TRADE_IN] SET [STATUS]= :STATUS,[CdfCharger]= :CdfCharger,[CdfHolder]= :CdfHolder,[Scantime]= :Scantime  where [REFERENCE] = :REFERENCE";
        $params = array('REFERENCE' => $_SESSION['Reference'],'STATUS' => $_GET['Status'], 'CdfCharger' => strtoupper($_GET['CdfCharger']), 'CdfHolder' => strtoupper($_GET['CdfHolder']),'Scantime' => date('Y-m-d H:i:s'));
        $upd = $Connection->update($SQL,$params);
        echo '<span class="DoneMsg">Záznam byl uložen na server.</span>';    
        TRDIN_main();
        die;  
    }        
    else
    {
        TRDIN_main();
        die;    
    }
}   

function GetPNorRef($input) {
    $Input = trim($input);
    $Result = new InputValue($Input);
    $PN = $Result->ParcelNumber()[0];
    $NumOrRef = $Result->ParcelNumber()[1];
    if ($NumOrRef == "NUM") 
    {   
        if (!isset($Connection)) 
        {$Connection = new PDOConnect("DPD_DB");}
        $SQL = "SELECT [Reference],[RCVDate],[PARCELNO],[PARCELNO_ST],[STATUS],[CdfCharger],[CdfHolder] FROM [TradeIN_View] WHERE ([PARCELNO] = :parcelno) OR ([PARCELNO_ST] = :parcelno_st)";
        $params = array(':parcelno' => $PN, ':parcelno_st' => $PN);
        $stmt = $Connection->select($SQL, $params);
        $count = $stmt['count'];
        $rows =  $stmt['rows'];
        if ($count === false || $count === null || $count === 0) {
            echo '<span class="ErrorMsg">Databáze neobsahuje toto číslo palety.</span>';
            TRDIN_main();
            die;
        } else {
            if (!isset($_SESSION)) {session_start();}
            $row = $rows[0];
            $_SESSION['Reference'] = $row['Reference'];
            $_SESSION['PARCELNO'] = $row['PARCELNO'];
            $_SESSION['PARCELNO_ST'] = $row['PARCELNO_ST'];
            $_SESSION['RCVDate'] = $row['RCVDate'];
            $_SESSION['Status'] = $row['STATUS'];
            $_SESSION['CdfCharger'] = $row['CdfCharger'];
            $_SESSION['CdfHolder'] = $row['CdfHolder'];
            require "TradeIN_form.php";
        }
    } 
    else 
    { 
        TRDIN_main();
        die;
    }
}

function TRDIN_main() 
{
echo "<div class='ScanParcel'>";
echo "<h1><b><strong>= Příjem TradeIN =</strong></b></h1>";
echo "<form  method='get' id='parcel_search_form'>";
echo "<label for='name' id='Inplbl'>Naskenujte číslo balíku:</label><br>";
echo "<input type='text' id='Input' name='Input' autofocus><br><br>";
echo "<input type='submit' value='Potvrdit'>";
echo "</form>";
echo "</div>";
echo "<br>";
if (!isset($Connection)) 
    {$Connection = new PDOConnect("DPD_DB");}
    $SQL = "SELECT [REFERENCE],[RCVDate],[PARCELNO],[PARCELNO_ST],[Customer],[Street],[City] FROM [dbo].[TradeIN_View] ORDER BY RCVDate,Reference";
    $stmt = $Connection->select($SQL);
    
    $rows = $stmt['rows'];
    $count = $stmt['count'];
    
    echo "Počet záznamů: " . $count . "<br>";
    
    $columnNames = ['Reference', 'Datum příjmu', 'Příchozí balík', 'Odchozí balík', 'Zákazník', 'Ulice', 'Město'];
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
<script>

  document.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault(); 
      var input = event.target;
      var index = input.tabIndex;
      var nextInput = document.querySelector('[tabindex="' + (index + 1) + '"]');
      if (nextInput) {
        nextInput.focus();
      }
    }
  });

  function submitForm() {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "TradeIN.php?Save=", true);
  xhr.send();

  document.forms[0].submit();
}


    function FieldMaster(field) {
        var Status = document.getElementById("Status");
        var CdfHolder = document.getElementById("CdfHolder");
        var CdfCharger = document.getElementById("CdfCharger");

        if (field.id === "Status") {
                CdfHolder.focus();
        } else if (field.id === "CdfHolder") {
                CdfCharger.focus();
        } else if (field.id === "CdfCharger") {
                Status.focus();
        }
    }

    function Confirmation() {
        if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "TradeIn.php?Menu=yes";

        } else {
            window.location.href = "TradeIn.php?Menu=no";
        }
    }
    </script>
    </div>
</body>