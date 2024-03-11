<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Balík info</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Informace o PMI zásilkách" />
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
        <?php include 'navigation.php'; ?>
        </header>
<div class='ScanParcel'>
    <h1><b><strong>= Balík info =</strong></b></h1>
    <form  method='get' class='InputPN'>
        <label for='Input' id='Inplbl'>Naskenujte číslo balíku:</label><br>
        <input type='text' id='Input' name='Input' autofocus><br><br>
        <label for='Actualization' id='Inplbl'>Stáhnout aktuální data balíku:</label><br>
        <input type="checkbox" id='Actualization' name="Actualization" class="checkbox" value="1"><br><br>
        <input type='submit' value='Potvrdit'>
    </form>
</div>
<br>

<?php
require 'ProjectFunc.php'; 
require 'PPL_import.php'; 
require 'Packeta_import.php';
require 'SQLconn.php';

/******************************************************************************************************************************************************************************/
If ($_SERVER["REQUEST_METHOD"] == "GET") { 
    if (isset($_GET["Input"])) {    
        $Input = $_GET["Input"];
        $Result = new InputValue(trim($Input));
        $PN = $Result->ParcelNumber()[0];
        $NumOrRef = $Result->ParcelNumber()[1];
        $Courier = $Result->ParcelNumber()[2];
        if(isset($_GET["Actualization"]))
        {
        switch ($Courier) 
            {
                case 'Packeta':
                    Packeta_import($PN);
                    break;
                
                case 'PPL':
                    PPL_import( $PN);
                    break;
            }
        }

/******************************************************************************************************************************************************************************/
        if (!isset($Connection)){$Connection = new PDOConnect("DPD_DB");}
        if ($NumOrRef == "NUM")
            {
            $SQL=  "SELECT * FROM Parcel_view WHERE ([PARCELNO] = :parcelno) or ([REFERENCE] = :reference) ORDER BY EVENT_DATE_TIME DESC";
            $params = array(':parcelno' => $PN, ':reference' => strval($PN));
            $stmt = $Connection->select($SQL, $params);
            }
        elseif($NumOrRef == "Pal")
            {
                $SQL=  "SELECT * FROM Parcel_view WHERE ([REFERENCE] = :reference) ORDER BY EVENT_DATE_TIME DESC";
                $params = array(':reference' => $PN);
                $stmt = $Connection->select($SQL, $params);    
            }
        elseif($NumOrRef == "Text")
            {
                $SQL=  "SELECT * FROM Parcel_view WHERE ([REFERENCE] = :reference) ORDER BY EVENT_DATE_TIME DESC";
                $params = array(':reference' => $PN);
                $stmt = $Connection->select($SQL, $params);    
            }
        else
            {
                echo '<span class="ErrorMsg">Neznámý formát čísla.</span>';
                die;
            }
        $rows = $stmt['rows'];
        $count = $stmt['count'];
        
        echo "Počet záznamů: " . $count . "<br>";
        
        $columnNames = ['Číslo palety', 'Reference', 'Událost', 'Datum a čas', 'Služba', 'PSČ','Zdroj','Status','Infopole','Poznámky'];
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
}   
?>

<script>
var checkbox = document.getElementById('Actualization');
if (checkbox) {
    checkbox.addEventListener('change', function() {
    var inputField = document.getElementById('Input');
    inputField.focus();
});
}   
</script>
</body>



