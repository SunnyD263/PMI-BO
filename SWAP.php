<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Příjem SWAP</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="Příjem SWAP" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script src="https://code.jquery.com/jquery-3.6.4.js"
        integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E=" crossorigin="anonymous">
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
require 'ParcelSlct.php'; 
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
            $PN = $_SESSION['PARCELNO'];
            GetPNorRef($PN);  
            }
            elseif($_GET["Menu"]=='yes')
            {
            unset ($_SESSION['PARCELNO']);
            unset ($_SESSION['Reference']);
            unset($_SESSION["SWAP_Dvc_sum_View"]);
            SWAP_main();
            die;
            }
        }
        elseif (isset($_GET["Notmatch"])) 
        {
            if($_GET["Notmatch"]=='no')
            {
            die;
            }
            elseif($_GET["Notmatch"]=='yes')
            {
                If(strlen($_GET["EAN"]) == 17 and substr($_GET["EAN"], -4) == "SWAP" )
                {
                    Confirmation('Exit');
                }
                elseif(strlen($_GET["EAN"]) == 14)
                {
                    Confirmation('Exit');
                }
                else
                {
                    
                }    
                die;
            }
        }   
    elseif(isset($_GET["EAN"]))
        {

        $Sken = $_GET["EAN"];
        $Order = $_SESSION["SWAP_Dvc_sum_View"];

        foreach ($Order as $index) 
            {
                $EAN = $index['EAN'];
                $Codentify = $index['Codentify'];
                $SUM = $index['Sum'];
                $EAN_CRT = $index['EAN_CRT'];
                $ScanQuantity = $index['ScanQuantity'];
                $OrdQuantity = $index['OrdQuantity'];
                if ($Codentify == $Sken && $SUM !== 0 ) 
                {
                    $uomDevice = new UOMDevice();
                    $index['ScanQuantity'] = $Quantity;
                    $index['Sum'] = $index['ScanQuantity'] - $index['ScanQuantity'];
                    
                    if (empty($_SESSION['SWAP_DVC_scan']))
                    {
                        $dataArray = array();
                        $MaxIndex=0;
                    }
                    else 
                    {
                    $dataArray=$_SESSION['SWAP_DVC_scan'];
                    $MaxIndex = max(array_keys($dataArray));
                    }            
                    $currentDateTime = new DateTime();
                    $DateTime = $currentDateTime->format('Y-m-d H:i:s');
                    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$_SESSION["Reference"]);
                    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$EAN);
                    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Codentify);
                    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$DateTime);
                    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$ScanQuantity);
                    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$EAN_CRT);
                    $_SESSION['SumLocation']=$dataArray;
                    unset ($_SESSION["SWAP_Dvc_sum_View"]);
                } 
                else
                {
                echo '<script>alert("Vzkaz byl odeslán.");</script>';  
                }

            }
        

            
        }       
    elseif(isset($_GET["Save"]))
        { 
        if (!isset($Connection)) 
        {$Connection = new PDOConnect("DPD_DB");}     
        $SQL=  "UPDATE [dbo].[TRADE_IN] SET [STATUS]= :STATUS,[CdfCharger]= :CdfCharger,[CdfHolder]= :CdfHolder,[Scantime]= :Scantime  where [REFERENCE] = :REFERENCE";
        $params = array('REFERENCE' => $_SESSION['Reference'],'STATUS' => $_GET['Status'], 'CdfCharger' => strtoupper($_GET['CdfCharger']), 'CdfHolder' => strtoupper($_GET['CdfHolder']),'Scantime' => date('Y-m-d H:i:s'));
        $upd = $Connection->update($SQL,$params);
        echo '<span class="DoneMsg">Záznam byl uložen na server.</span>';
        SWAP_main();

        }
    else
        {
        SWAP_main();
        }
}   

function GetPNorRef($input) {
    $Input = trim($input);
    $Result = new InputValue($Input);
    $PN = $Result->DPD()[0];
    $NumOrRef = $Result->DPD()[1];
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
            require "SWAP_form.php";
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
            require "SWAP_form.php";
        }
    }
    else
    {   echo '<span class="ErrorMsg">Neznámý formát čísla.</span>';
        SWAP_main();
        die;
    }
}

function SWAP_main() 
{
echo "<div class='ScanParcel'>";
echo "<h1><b><strong>= Příjem SWAP =</strong></b></h1>";
echo "<form  method='get' id='parcel_search_form'>";
echo "<label for='name' id='Inplbl'>Naskenujte číslo balíku:</label><br>";
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
<script>
function submitForm() {
  // Získání hodnoty pole EAN
  var eanValue = document.getElementById("EAN").value;

  // Odeslání hodnoty pole pomocí AJAX nebo form submit (zvolte jednu z možností)

  // 1. AJAX metoda
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "SWAP.php?EAN=" + encodeURIComponent(eanValue), true);
  xhr.send();

  // 2. Form submit metoda
  document.forms[0].submit();
}

function Confirmation(Menu) 
{

    switch (Menu)
    {

    case 'Exit':
        if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "SWAP.php?Menu=yes";

        } else {
            window.location.href = "SWAP.php?Menu=no";
        }
        break;
    case 'Notmatch':
    if (confirm("Codentify neodpovídá očekávanému. Chcete pokračovat?")) {
            window.location.href = "SWAP.php?Notmatch=yes";

        } else {
            window.location.href = "SWAP.php?Notmatch=no";
        }
        break;
    case 'Codentify':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "SWAP.php?Notmatch=yes";

        } else {
            window.location.href = "SWAP.php?Notmatch=no";
        }
        break;
    case 'EAN':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "SWAP.php?Notmatch=yes";

        } else {
            window.location.href = "SWAP.php?Notmatch=no";
        }
        break;
    }    
}
</script>
</div>
</body>