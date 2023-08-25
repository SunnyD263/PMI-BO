<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Balík info</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Informace o PMI zásilkách" />
         <link rel="stylesheet" type="text/css" href="css/style.css" />
        <script
            src="https://code.jquery.com/jquery-3.6.4.js"
            integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
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
    <form  method='get' id='parcel_search_form'>
        <label for='name' id='Inplbl'>Naskenujte číslo balíku:</label><br>
        <input type='text' id='Input' name='Input' autofocus><br><br>
        <input type='submit' value='Potvrdit'>
    </form>
</div>
<br>

<?php
require 'ParcelSlct.php'; 
If ($_SERVER["REQUEST_METHOD"] == "GET") { 
    if (isset($_GET["Input"])) {    
        $Input = $_GET["Input"];
        $Result = new InputValue(trim($Input));
        $PN = $Result->DPD()[0];
        $NumOrRef = $Result->DPD()[1];
        require 'SQLconn.php';
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

<!-- <script>
    $(document).ready(function() {
        $('#parcel_search_form').submit(function(event) {
            // zastavit defaultní chování formuláře
            event.preventDefault();
            
            // získat hodnotu z inputu
            var inputValue = $('#Input').val();
            
            // odeslat AJAX požadavek na server
            $.ajax({
                type: 'GET',
                url: 'parcelslct.php',
                data: { Input: inputValue },
                success: function(data) {
                    // vložit výsledek do elementu s id "result"
                    $('#Input').val("").focus();
                    $('#result').html(data);
                },
                error: function() {
                    alert('Chyba při získávání dat.');
                }
            });
        });
    });
</script> -->


</body>



