<fieldset>
        <legend>Data zásilky: </legend><br>
        <label for='Reference' class='label-TradeIN'>Reference:</label>
        <input type='text' id='Reference' name='Reference' value="<?php echo $_SESSION['Reference']; ?>" disabled><br><br>
        <label for='PARCELNO' class='label-TradeIN'>Příchozí balík: </label>
        <input type='text' id='PARCELNO' name='PARCELNO' value="<?php echo $_SESSION['PARCELNO']; ?>" disabled><br><br>
        <label for='PARCELNO_ST' class='label-TradeIN'>Odchozí balík: </label>
        <input type='text' id='PARCELNO_ST' name='PARCELNO_ST' value="<?php echo $_SESSION['PARCELNO_ST']; ?>" disabled><br><br>
        <label for='RCVDate' class='label-TradeIN'>Datum příjmu: </label>
        <input type='text' id='RCVDate' name='RCVDate' value="<?php echo $_SESSION['RCVDate']; ?>" disabled><br>
    </fieldset><br>
    <form method='GET'>
    <fieldset>
        <legend>Obsah zásilky: </legend><br>
        <label for='Obsah balíku' class='label-TradeIN'>Obsah balíku:</label>
        <select name='Status' onchange="FieldMaster(this)">
            <option id='COMPLETE'  value='COMPLETE'<?php if ($_SESSION['Status'] == 'COMPLETE') echo "selected"; ?>>COMPLETE</option>
            <option id='EMPTY' value='EMPTY' <?php if ($_SESSION['Status'] == 'EMPTY') echo "selected"; ?>>EMPTY</option>
            <option id='HOLDER' value='HOLDER' <?php if ($_SESSION['Status'] == 'HOLDER') echo "selected"; ?>>HOLDER</option>
            <option ID='CHARGER' value='CHARGER'<?php if ($_SESSION['Status'] == 'CHARGER') echo "selected"; ?>>CHARGER</option>
            <option ID='OTHER'value='OTHER'<?php if ($_SESSION['Status'] == 'OTHER') echo "selected"; ?>>OTHER</option>
            <option ID='0' value='' <?php if ($_SESSION['Status'] == '') echo "selected"; ?>></option>
        </select><br><br>
        <label for='CdfHolder' class='label-TradeIN'>Codentify holder: </label>
        <input type='text' id='CdfHolder' name='CdfHolder' onchange="FieldMaster(this)" value="<?php echo $_SESSION['CdfHolder']; ?>"><br><br>
        <label for='CdfCharger' class='label-TradeIN'>Codentify charger: </label>
        <input type='text' id='CdfCharger' name='CdfCharger' onchange="FieldMaster(this)" value="<?php echo $_SESSION['CdfCharger']; ?>">
    </fieldset><br>
    
    <fieldset class = 'Buttons'>
        <legend>Volby: </legend>
        <Table>
        <tr>
            <td><input type='submit' onclick="" class='Button' name='Save' id='Save' value='Uložit'></td>
    </form>
            <td></td>
            <td><input type='submit' onclick="Confirmation()" class='Button' name='Back' id='Back' value='Zpět'></td>
        </tr>
        </Table>
    </fieldset><br>

