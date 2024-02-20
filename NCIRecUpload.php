<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Nahrát VO</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="Nahrát VO" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script
  src="https://code.jquery.com/jquery-3.7.1.min.js"
  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
  crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <h1>PMI BO Tool</h1>
        <?php require 'navigation.php'; ?>
    </header>
<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();
require 'SQLconn.php';
if(isset($_POST['submit']))
    {
    $uploadedFile = $_FILES['excel_file']['tmp_name'];

    $spreadsheet = IOFactory::load($uploadedFile);
    $worksheet = $spreadsheet->getActiveSheet();

    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

    $data = [];
    for ($row = 2; $row <= $highestRow; ++$row) {
        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
            $data[$row][$col] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
    }
    if ($row > 2)
        {
        if (!isset($Connection)) {$Connection = new PDOConnect('DPD_DB');}
        $SQL = 'DELETE FROM Shop_NCI';
        $stmt = $Connection->execute($SQL);
        $Records = $data;   
        foreach($Records as $ShopRec)
            {
            $data = array('Shop' => $ShopRec[1], 'ProjectID' => $ShopRec[2]);
            $Connection->insert("Shop_NCI", $data);
            }
        }
    }

echo "<h2>Vyberte soubor s VO k načtení:</h2>";
echo "<form action='NCIRecUpload.php' method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='excel_file' class='Button' accept='.xlsx'><br><br>";
echo "<button type='submit' name='submit' class='Button' >Nahrát soubor</button>";
echo "</form>";
?>
</body>