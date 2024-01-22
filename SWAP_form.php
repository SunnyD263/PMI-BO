    <table class='EAN_form_style'>
        <tr>
            <td>
                <fieldset>
                    <form method='GET'>
                        <legend>Naskenujte:</legend><br>
                        <label for='EAN' class='label-TradeIN'>EAN:</label>
                        <input type='text' id='EAN' name='EAN' onchange='submitForm()' autofocus><br><br><br><br>
                    </form>
                </fieldset>
            </td>
            <td></td>
            <td>
                <fieldset>
                    <legend>Data zásilky: </legend><br>
                    <label for='Reference' class='label-TradeIN'>Reference:</label>
                    <input type='text' id='Reference' name='Reference' value='<?php echo $_SESSION['Reference']; ?>' disabled><br><br>
                    <label for='PARCELNO' class='label-TradeIN'>Číslo balíku: </label>
                    <input type='text' id='PARCELNO' name='PARCELNO' value='<?php echo $_SESSION['PARCELNO']; ?>' disabled><br><br>
                </fieldset>
            </td>
        </tr>
    </table>
    <br>
    <fieldset>
        <legend>Obsah při odeslání zásilky: </legend><br>
        <?php SWAP_ORDITEM(); ?>
    </fieldset><br>
    <fieldset>
        <legend>Naskenované zařízení: </legend><br>
        <?php SWAP_SCNITEM(); ?>
    </fieldset><br>

    <fieldset class='Buttons'>
        <legend>Volby: </legend>
        <table>
            <tr>
            <form method='GET' action='SWAP.php'>
                <th><input type='submit' onclick='' class='Button' name='Save' id='Save' value='Uložit'></th>
            </form>
                <th></th>
                <th><input type='submit' onclick="Confirmation('Exit')" class='Button' name='Back' id='Back' value='Zpět'></th>
            </tr>
        </table>
    </fieldset><br>


<?php
        function SWAP_ORDITEM()
        {
            if (!isset($Connection)) {
                $Connection = new PDOConnect('DPD_DB');
            }
            if (!isset($_SESSION['SWAP_Dvc_sum_View']) and empty($_SESSION['SWAP_Dvc_sum_View']))
                {
                $SQL = 'SELECT [Reference],[Material],[MAKTX],[EAN],[EAN_CRT],[Codentify],[ScanQuantity],[OrdQuantity],[Sum] FROM [DPD_DB].[dbo].[SWAP_Dvc_sum_View] WHERE ([REFERENCE] = :REFERENCE)';
                $params = array(':REFERENCE' => $_SESSION['Reference']);
                $stmt = $Connection->select($SQL, $params);            
                $rows = $stmt['rows'];
                $count = $stmt['count'];
                }
            else
                {
                $rows= $_SESSION['SumLocation'];
                }

            foreach ($rows as $row) {
                    $rowData = array();
                    foreach ($row as $key => $value) {
                        $rowData[$key] = $value;
                    }
                    $data[] = $rowData;
                }

            $_SESSION['SWAP_Dvc_sum_View'] = $data;
            $columnNames = ['Reference', 'Produkt', 'Název produktu', 'EAN PACK', 'EAN CRT', 'Codentify', 'Skenováné', 'Objednané', 'Celkem'];
            echo "<table border='2' cellspacing='1' cellpadding='5'>";
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
            echo '<br>';
        }


        function SWAP_SCNITEM()
        {
            $columnNames = ['Název produktu', 'EAN PACK', 'EAN CRT', 'Codentify', 'Datum', 'Množství'];
            echo "<table border='2' cellspacing='1' cellpadding='5'>";
            echo '<tr>';
            if (isset($_SESSION['SumLocation'])) {$rows = $_SESSION['SumLocation'];}
            for ($i = 0; $i < count($columnNames); $i++) {
                echo '<th>' . $columnNames[$i] . '</th>';
            }
            echo '</tr>';

            if (isset($rows)) {
                foreach ($rows as $row) {
                    echo '<tr>';
                    foreach ($row as $key => $value) {
                        echo '<td>' . $value . '</td>';
                    }
                    echo '</tr>';
                }
            }
            echo '</table>';
            echo '<br>';
        }
        ?>