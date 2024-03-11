<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Příjem z prodejen</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="Příjem z prodejen" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script
  src="https://code.jquery.com/jquery-3.7.1.min.js"
  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
  crossorigin="anonymous"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <header>
        <h1>PMI BO Tool</h1>
        <?php require 'navigation.php'; ?>
    </header>
<?php

session_start();
echo "<h2> Příjem z prodejen</h2>";
require 'SQLconn.php';

/******************************************************************************************************************************************************************************/
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{    
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
if ($data["Shop"] !== null and $data["ShopRec"] !== null)
    {
    $Records = $data["ShopRec"];
    $Shop = $data["Shop"];
    $ParcelNO = $data["ParcelNO"];
    if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}

    foreach($Records as $ShopRec)
        {
        $data = array('Shop' => $Shop, 'Product' => $ShopRec["data"]["ProductName"], 'EAN' => $ShopRec["data"]["EAN"],'DT' => $ShopRec["data"]["DT"],'Parcel' => $ParcelNO,'Quantity' => $ShopRec["data"]["Quantity"], 'Unit'=> $ShopRec["data"]["UOM"],'Type'=> $ShopRec["data"]["Type"],'Codentify'=>$ShopRec["data"]["Codentify"] , 'Material' => $ShopRec["data"]["Product"]);
        $Connection->insert("Direct", $data);        
        }
    }
}

/******************************************************************************************************************************************************************************/
if ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
//Open forms
If(isset($_GET["Open"]))
    {
    ShopRec();
    exec("node -e 'import {FocusChng } from \"ShopRec.mjs\";FocusChng('','ParcelNO');'");
    }

}

/******************************************************************************************************************************************************************************/
function ShopRec()
    {
    echo        "<div class='header'>";
    echo        "<fieldset>";
    echo                "<legend>Naskenujte:</legend>";
    echo        "<div>";    
    echo                "<label for='ParcelNO'>Číslo balíku:</label>";
    echo                "<input type='text' id='ParcelNO' name='ParcelNO'>";
    echo        "</div>";
    echo        "<div>"; 
    echo                "<label for='search-box'>Zadejte:</label>";
    echo                "<input type='text' id='search-box' onkeyup='searchFunction()' placeholder='Začněte psát...'>";
    echo        "</div>";
    echo        "<div>";    
    Shop_Combo();
    echo        "</div>";
    echo        "<div>";    
    echo                "<label for='EAN'>Codentify/EAN:</label>";
    echo                "<input type='text' id='EAN' name='EAN' >";
    echo        "</div>";  
    echo        "<div>"; 
    echo                "<label for='Delete'></label>";
    echo                "<input type='button' id='Delete' name='Delete'  value='Vymazat' >";
    echo        "</div>";
    echo        "<div>"; 
    echo                "<label for='Save'></label>";
    echo                "<input type='button' id='Save' name='Save' class='Bpx160' value='Další balík / Uložit' >";
    echo        "</div>";
    echo        "<div>";
    echo                "<label for='Back'></label>";  
    echo                "<input type='button' id='Back' name='Back' value='Zavřít' >";
    echo        "</div>";
    echo        "</fieldset>";
    echo        "</div>";
    echo        "</div><br>";
    echo        "<div>";
    echo        "<label for='counter'>Počet záznamů:</label>";
    echo        "<input type='text' id='counter' value = 0 style='width: 40px; text-align: center' disabled>";
    echo        "</div><br>";      
    echo        "<table id='BodyField' class='AddField'></table>";
   
    }

/******************************************************************************************************************************************************************************/
function Shop_Combo()
{
if (!isset($Connection)){$Connection = new PDOConnect("DPD_DB");}
$SQL=  "SELECT  [Shop],[ProjectID] FROM [DPD_DB].[dbo].[Shop]";
$stmt = $Connection->select($SQL);
$count = $stmt['count'];

echo    "<form  method='GET'>";
echo    "<label for='Slct_depo'>Vyberte depo:</label>";
echo    "<select name='Slct_depo' ID='Slct_depo'>";

if($count > 0)
    { 
    $rows = $stmt['rows'];
    echo "<option id='Nothing' value='' selected></option>"; 
    foreach($rows as $row) 
        {
        $Shop = $row['Shop'];
        echo "<option id='" .  $Shop . "'value='" .  $Shop . "' >".  $Shop ."</option>";
        }
    }
echo    "</select><br>";
echo    "</form>";  


}
?>
<script src="ProjectFunc.js"></script>
<script src="ShopRec.js"></script>
<script>

/*****************************************************************************************************/
/*--------------------------------------Event function-----------------------------------------------*/
/*****************************************************************************************************/

document.addEventListener('DOMContentLoaded',  EnabledChng('ALL'));  

document.getElementById('Delete').addEventListener('click', function() {
HeaderButton('Delete');
});

document.getElementById('Save').addEventListener('click', function() {
HeaderButton('Save');
});

document.getElementById('ParcelNO').addEventListener('change', function() {
  var value = this.value;
  parcelNumber(value, 'ParcelNO', 'search-box');
});

document.getElementById('search-box').addEventListener('keydown', function(event) {
    if (event.keyCode === 9) {
        var value = this.value;
        var jsonString = JSON.stringify(value);
        localStorage.setItem('Shop', jsonString);
        document.getElementById('search-box').disabled = true;
        FocusChng('Slct_depo', 'EAN');  
        document.getElementById('EAN').focus();
        event.preventDefault(); 
    }
});

document.getElementById('Slct_depo').addEventListener('change', function() {
  var jsonString = JSON.stringify(this.value);
  localStorage.setItem('Shop', jsonString);  
  document.getElementById('search-box').value = '';
  document.getElementById('search-box').disabled = true;
  FocusChng('Slct_depo', 'EAN');
});

document.getElementById('EAN').addEventListener('change', function() {
  var value = this.value.toUpperCase();
  Check_EAN( value,'','EAN');
});
</script>
</body>