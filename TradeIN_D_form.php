    <fieldset>
        <legend>Data zásilky: </legend><br>
        <label for='Reference' class='label-TradeIN'>Název depa:</label>
        <input type='text' id='Reference' name='Reference' value="<?php echo $_SESSION['Reference']; ?>" disabled><br><br>
        <label for='PARCELNO' class='label-TradeIN'>Příchozí balík: </label>
        <input type='text' id='PARCELNO' name='PARCELNO' value="<?php echo $_SESSION['PARCELNO']; ?>" disabled><br><br>
    </fieldset><br>
    <form method='GET'>
    <fieldset>
        <legend>Obsah zásilky: </legend><br>
        <label for='Obsah balíku' class='label-TradeIN'>Obsah balíku:</label>
        <select name='Status' onchange="FieldMaster(this)">
            <option id='COMPLETE'  value='COMPLETE'>COMPLETE</option>
            <option id='EMPTY' value='EMPTY' >EMPTY</option>
            <option id='HOLDER' value='HOLDER' >HOLDER</option>
            <option ID='CHARGER' value='CHARGER'>CHARGER</option>
            <option ID='OTHER'value='OTHER'>OTHER</option>
            <option ID='0' value='' selected></option>
        </select><br><br>
        <label for='CdfHolder' class='label-TradeIN'>Codentify holder: </label>
        <input type='text' id='CdfHolder' name='CdfHolder' onchange="FieldMaster(this)" value=""><br><br>
        <label for='CdfCharger' class='label-TradeIN'>Codentify charger: </label>
        <input type='text' id='CdfCharger' name='CdfCharger' onchange="FieldMaster(this)" value="">
    </fieldset><br>

    <fieldset class = 'Buttons'>
        <legend>Volby: </legend>
        <Table>
        <tr>
            <td><input type='submit' onclick="" class='Button' name='Save' id='Save' value='Další zařízení'></td>
    </form>
            <td></td>
            <td><input type="button" onclick="Confirmation()" class='Button' name='Back' id='Back' value='Zpět'></td>
        </tr>
        </Table>
    </fieldset><br>

<script>

document.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault(); 
      var input = event.target;
      var index = input.tabIndex;
      var nextInput = document.querySelector('[tabindex="' + (index + 1) + '"]');
      if (nextInput) {
        nextInput.focus();
      }
    }
  });

  function submitForm() {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "TradeIN.php?Save=", true);
  xhr.send();

  document.forms[0].submit();
}

    function FieldMaster(field) {
        var Status = document.getElementById("Status");
        var CdfHolder = document.getElementById("CdfHolder");
        var CdfCharger = document.getElementById("CdfCharger");

        if (field.id === "Status") {
                CdfHolder.focus();
        } else if (field.id === "CdfHolder") {
                CdfCharger.focus();
        } else if (field.id === "CdfCharger") {
                Status.focus();
        }
    }

    function Confirmation() {
        if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "TradeIn_D.php?Menu=yes";

        } else {
            window.location.href = "TradeIn_D.php?Menu=no";
        }
    }
    </script>
