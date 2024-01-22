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
<script type="module" src="ShopRec.mjs"></script>
<script type="module" src="ProjectFunc.mjs"></script>
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
If ($_SERVER["REQUEST_METHOD"] == "POST")
{ 
if (!isset($Connection)){$Connection = new PDOConnect("DPD_DB");}
if (isset($_POST['SQL_Select']))
    {
    $stmt = $Connection->select($_POST['SQL_Select']);
    echo json_encode($stmt);  
    }

}


If ($_SERVER["REQUEST_METHOD"] == "GET")
{ 
//Open forms
If(isset($_GET["Open"]))
    {
    ShopRec();
    exec("node -e 'import {FocusChng } from \"ShopRec.mjs\";FocusChng('','ParcelNO');'");
    }

}

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
    Shop_Combo();
    echo        "</div>";
    echo        "<div>";    
    echo                "<label for='EAN'>Codentify/EAN:</label>";
    echo                "<input type='text' id='EAN' name='EAN' >";
    echo        "</div>";  
    echo        "<div>"; 
    echo                "<input type='button' id='Delete' name='Delete'  value='Vymazat' >";
    echo        "</div>";
    echo        "<div>"; 
    echo                "<input type='button' id='Save' name='Save'  value='Další balík' >";
    echo        "</div>";
    echo        "<div>";  
    echo                "<input type='button' id='Back' name='Back' value='Zavřít' >";
    echo        "</div>";
    echo        "</fieldset>";
    echo        "</div>";   
    echo        "<table id='BodyField' class='AddField'></table>";
   
    }

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
</body>