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
        require "TradeIN_D_form.php";
        }
        elseif($_GET["Menu"]=='yes')
        {       
        unset($_SESSION['Reference']);
        unset($_SESSION['PARCELNO']); 
        TRDIN_main();
        die;    
        }
    }    
    elseif(isset($_GET["Save"]))
    {   if (!isset($Connection)){$Connection = new PDOConnect("DPD_DB");}
        if ($_GET['Status'] !== 'EMPTY' and  strtoupper($_GET['CdfCharger']) !== '' and strtoupper($_GET['CdfHolder']) !== '' )    
        { 
        $data = array('REFERENCE' => $_SESSION['Reference'], 'ParcelNO' => $_SESSION['PARCELNO'],'STATUS' => $_GET['Status'], 'CdfCharger' => strtoupper($_GET['CdfCharger']), 'CdfHolder' => strtoupper($_GET['CdfHolder']),'Scantime' => date('Y-m-d H:i:s'));
        $Connection->insert("TRADE_IN_D", $data);
        echo '<span class="DoneMsg">Záznam byl uložen na server.</span>';
        }
        require "TradeIN_D_form.php";
        die;  
    }
    elseif(isset($_GET["Back"]))
    {

    }
    else
    {
        TRDIN_main();

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
        if (!isset($_SESSION)) {session_start();}
        $_SESSION['Reference'] = $_GET["Slct_depo"];
        $_SESSION['PARCELNO'] = $PN;
        require "TradeIN_D_form.php";
    } 
    else 
    { 
        TRDIN_main();
        echo "<h1><b>Kontaktujte IS</b></h1>";
        die;
    }
}

function TRDIN_main() 
{

echo "<h1><b><strong>= Příjem TradeIN =</strong></b></h1>";

if (!isset($Connection)){$Connection = new PDOConnect("DPD_DB");}
$SQL=  "SELECT  [Shop],[ProjectID] FROM [DPD_DB].[dbo].[Shop] where projectID= 300";
$stmt = $Connection->select($SQL);
$count = $stmt['count'];

echo    "<div class='TradeIn_D'>";
echo    "<fieldset>";
echo    "<form  method='GET'>";
echo    "<label for='Slct_depo'>Vyberte depo:</label>";
echo    "<select name='Slct_depo' ID='Slct_depo' onchange=''>";
$firstrow = true;
if($count > 0)
    { 
    $rows = $stmt['rows'];
    foreach($rows as $row) 
        {
        $Shop = $row['Shop'];
        if ($firstrow == true)
            {
            echo "<option id='" . $Shop . "'value='" .  $Shop . "' selected>".  $Shop ."</option>";            
            $firstrow = false;
            }
        else
            {
            echo "<option id='" .  $Shop . "'value='" .  $Shop . "' >".  $Shop ."</option>";
            }
        }
    }
echo    "</select><br>";
echo    "<label for='Input'>Naskenujte balík:</label><br>";
echo    "<input type='text' id='Input' name='Input' onchange='this.submit()' autofocus>";
echo    "</form>";
echo    "</fieldset>";
echo    "</div>";
}

?>
<script>

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
            window.location.href = "TradeIn_D.php?Menu=yes";

        } else {
            window.location.href = "TradeIn_D.php?Menu=no";
        }
    }
    </script>
    </div>
</body>