<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Balík mimo systém</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Balík mimo systém" />
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
        <?php include 'navigation.php'; 
                require 'SQLconn.php';
        ?>
    </header>
<script>
function InputPN()
    {
    var InputPN =document.getElementById('Input');        
    InputPN.disabled = true;
    var InputField = document.getElementById('InputText');
    InputField.style.display = 'block';
    }
function SenderPN()
    {
    var InputPN =document.getElementById('Input');        
    InputPN.disabled = true;
    var InputField = document.getElementById('InputText');
    InputField.style.display = 'block';
    var InputPN =document.getElementById('Sender');        
    InputPN.disabled = true;
    }
</script>
<br>

<?php
require 'ProjectFunc.php'; 
session_start();
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{ 
    if (isset($_GET["Open"])) 
        {
        Unsys_Main();
        }
    elseif (isset($_GET["Input"])) 
        {    
        $Input = $_GET["Input"];
        $Result = new InputValue(trim($Input));
        $PN = $Result->ParcelNumber()[0];
        $NumOrRef = $Result->ParcelNumber()[1];
        $Courier = $Result->ParcelNumber()[2];
        $_SESSION["Courier"] = $Courier;
        $_SESSION["PARCELNO"] = $PN;
        Unsys_Main($PN);
        echo "<script> InputPN();</script>";

        }
    elseif (isset($_GET["Sender"])) 
        {    
        $Customer = substr(trim($_GET["Sender"]),0,199);
        if(isset($_SESSION["PARCELNO"])){$PN = $_SESSION["PARCELNO"];}
        $_SESSION["Customer"]= $Customer;
        Unsys_Main($PN,$Customer);
        echo "<script> SenderPN();</script>";
        }
    elseif (isset($_GET["Back"])) 
        {    
        header("Location: index.php");         

        }
    elseif (isset($_GET["Save"])) 
        { 
        if (!isset($Connection)){$Connection = new PDOConnect("DPD_DB");}        
        if ($_SESSION["Courier"] == 'ČP')
            {                    
            $data = array('PARCELNO' => substr($_SESSION["PARCELNO"],2,10 ), 'SCAN_CODE' => '503', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' =>  $_SESSION["PARCELNO"], 'Source' => 'WHU', 'KN' => '', 'Customer'=> $_SESSION["Customer"]);
            $Connection->insert("PMIdb", $data);
            }
        else
            {               
            $data = array('PARCELNO' => $_SESSION["PARCELNO"], 'SCAN_CODE' => '503', 'EVENT_DATE_TIME' => date('Y-m-d H:i:s'),'SERVICE' => '901', 'REFERENCE' =>  '', 'Source' => 'WHU', 'KN' => '', 'Customer'=> $_SESSION["Customer"]);
            $Connection->insert("PMIdb", $data);
            }

            if(isset($_SESSION["PARCELNO"])){unset($_SESSION["PARCELNO"]);}
            if(isset($_SESSION["Customer"])){unset($_SESSION["Customer"]);} 
            if(isset($_SESSION["Courier"])){unset($_SESSION["Courier"]);}       
            Unsys_Main();     
        }
    
}   

function Unsys_Main($PN = '',$Customer='')
{

    if (isset($_SESSION["Error"])) 
    {   
        switch ($_SESSION["Error"]) 
        {
            case "501":
                echo '<span class="DoneMsg">Záznam byl přidán do databáze.</span>';;
                break;
        }        
    unset($_SESSION["Error"]);
    }
    echo "<h1><b><strong>= Balík mimo systém =</strong></b></h1>";
    echo "<fieldset class='InputField'>";
    echo "<div class='ScanParcel'>";
        echo "<br><form  method='get' class='InputPN' style='display: block'>";
            echo "<label for='Input'>Naskenujte číslo balíku:</label><br>";
            echo "<input type='text' id='Input' name='Input'  value='" . $PN . "'><br><br>";  
        echo "</form>";
        echo "<form class='InputText' id='InputText' style='display: none'>";
            echo "<label for='Sender'>Zadejte zákazníka:</label><br>";
            echo "<input type='text' id='Sender' onchange='this.submit()' name='Sender' value='" . $Customer . "' ><br><br>";        
        echo "</form>";
    echo "</div>";
    echo "</fieldset>";

    echo "<fieldset class='Buttons'>";
    echo    "<legend>Volby: </legend>";
    echo    "<table>";
    echo        "<tr>";
    echo            "<form method='GET'>";
    echo               "<th><input type='submit' onclick='' class='Button' name='Save' id='Save' value='Uložit'></th>";
    echo            "</form>";
    echo            "<th></th>";
    echo            "<form method='GET'>";
    echo            "<th><input type='submit' onclick='' class='Button' name='Back' id='Back' value='Zpět'></th>";
    echo            "</form>";
    echo        "</tr>";
    echo    "</table>";
    echo "</fieldset><br>";
}

?>
</body>