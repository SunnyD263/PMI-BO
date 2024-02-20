<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Párování balíků s referencí</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Informace o PMI zásilkách" />
         <link rel="stylesheet" type="text/css" href="css/style.css" />
         <script
            src="https://code.jquery.com/jquery-3.7.1.slim.js"
            integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc="
            crossorigin="anonymous">
        </script>
        <script src="ProjectFunc.js"></script>
        <script src="AddParcels.js"></script>
    </head>
    <body>
        <header>
        <h1>PMI BO Tool</h1>                        
        <?php include 'navigation.php'; ?>
        </header>
<?php
require 'ProjectFunc.php'; 
require 'PPL_import.php'; 
require 'Packeta_import.php';
require 'SQLconn.php';
//---------------------------------------------------------------------------------------------------------------------//
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{    
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true); 
if(isset($data["ERROR"]))
    {

    }
else
    {
    $_SESSION["Reference"] = $data["Save"]["Reference"];
    $_SESSION["Parcel_st"] = $data["Save"]["Parcel_st"];
    $_SESSION["Parcel_nd"] = $data["Save"]["Parcel_nd"];
    }
}
//---------------------------------------------------------------------------------------------------------------------//

if ($_SERVER["REQUEST_METHOD"] == "GET") 
    { 
    if(isset($_GET["Slct_depo"])) 
        {
        if($_GET["Slct_depo"] == 'PD2')
            {
            Form_PD2();
            }
        elseif($_GET["Slct_depo"] == 'PD4')
            {
            Form_PD4();
            }
        else
            {
            Form_Open();
            }
        }
    elseif (isset($_GET["Save"])) 
        {
            $date = new DateTime();
            $DateNow = $date->format('Y-m-d');

        if (!isset($Connection)) {$Connection = new PDOConnect("DPD_DB");}
        if($_GET["Save"] == 'PD2')
            {
            $data = array('PARCELNO' => $_SESSION["Parcel_nd"], 'REFERENCE' => $_SESSION["Reference"], 'EVENT_DATE_TIME' => $DateNow );
            $Connection->insert('PD2', $data);
            Form_PD2();
            echo '<span class="DoneMsg">Reference č.' . $_SESSION["Reference"] . ' byla uložena</span>';
            }
        elseif($_GET["Save"] == 'PD4')
            {
            $data = array('PARCELNO' => $_SESSION["Parcel_nd"], 'PARCELNO_ST' => $_SESSION["Parcel_st"], 'REFERENCE' => $_SESSION["Reference"], 'EVENT_DATE_TIME' => $DateNow );
            $Connection->insert('PD4', $data);
            Form_PD4();
            echo '<span class="DoneMsg">Reference č.' . $_SESSION["Reference"] . ' byla uložena</span>';
            }
        else{
            Form_Open();
            }
        if (isset($_SESSION["Reference"])){unset($_SESSION["Reference"]);}
        if (isset($_SESSION["Parcel_st"])){unset($_SESSION["Parcel_st"]);}
        if (isset($_SESSION["Parcel_nd"])){unset($_SESSION["Parcel_nd"]);}
        }
    }
   
function Form_Open()
{
    echo "<form method='GET'>";
    echo "<label for='Slct_depo'>Zvolte variantu:</label>";
    echo "<select name='Slct_depo' ID='Slct_depo' onchange='submitForm(this)'>";
    echo "<option id='Nothing' value='Nothing' selected></option>";
    echo "<option id='PD2' value='PD2' >Normální balík</option>";
    echo "<option id='PD4' value='PD4' >Vratný balík</option>";
    echo "</select><br>";
    echo "</form>";
}

function Form_PD2()
    {
    echo "<form method='GET'>";
    echo "<label for='Slct_depo'>Zvolte variantu:</label>";
    echo "<select name='Slct_depo' ID='Slct_depo' onchange='submitForm(this)'>";
    echo "<option id='Nothing' value='Nothing'></option>";
    echo "<option id='PD2' value='PD2' >Normální balík</option>";
    echo "<option id='PD4' value='PD4' >Vratný balík</option>";
    echo "</select><br>";
    echo "</form>";
    echo "<div class='ScanParcel'>";
    echo "<h1><b><strong>= Párování balíků s referencí =</strong></b></h1>";
    echo "<label for='ParcelNO'  >Naskenujte/zadejte číslo balíku:</label><br>";
    echo "<input type='text' id='ParcelNO' name='ParcelNO' onchange='changeForm(this)' autofocus><br><br>";
    echo "<label for='Reference' >Naskenujte/zadejte číslo reference:</label><br>";
    echo "<input type='text' id='Reference' name='Reference' onchange='changeForm(this)'><br><br>";
    echo "</div>";
    echo "<br>";        
    }

function Form_PD4()
    {
    echo "<form method='GET'>";
    echo "<label for='Slct_depo'>Zvolte variantu:</label>";
    echo "<select name='Slct_depo' ID='Slct_depo' onchange='submitForm(this)'>";
    echo "<option id='Nothing' value='Nothing'></option>";
    echo "<option id='PD2' value='PD2' >Normální balík</option>";
    echo "<option id='PD4' value='PD4' >Vratný balík</option>";
    echo "</select><br>";
    echo "</form>";
    echo "<div class='ScanParcel'>";
    echo "<h1><b><strong>= Párování balíků s referencí =</strong></b></h1>";
    echo "<label for='ParcelNO_nd' >Naskenujte/zadejte číslo balíku:</label><br>";
    echo "<input type='text' id='ParcelNO_nd' name='ParcelNO_nd' onchange='changeForm(this)' autofocus><br><br>";
    echo "<label for='ParcelNO_st' >Naskenujte/zadejte číslo původího balíku:</label><br>";
    echo "<input type='text' id='ParcelNO_st' name='ParcelNO_st' onblur='changeForm(this)'><br><br>";
    echo "<label for='Reference' >Naskenujte/zadejte číslo reference:</label><br>";
    echo "<input type='text' id='Reference' name='Reference' onchange='changeForm(this)'><br><br>";
    echo "</div>";
    echo "<br>";        
    }
?>
</body>



