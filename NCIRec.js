var row;
var Sess_NCIRec;
var ID;
if (typeof row == 'undefined'){ row = 0};
if (typeof Sess_NCIRec == 'undefined'){ var Sess_NCIRec = []};

/*****************************************************************************************************/
/*--------------------------------------Refresh function--------------------------------------------*/
/*****************************************************************************************************/
if (localStorage.getItem('ParcelNO') !== null && localStorage.getItem('ParcelNO') !== 'undefined' )
  {
  parcelNumber(JSON.parse(localStorage.getItem('ParcelNO')), 'ParcelNO', 'Slct_depo');
  }
if (localStorage.getItem('Shop') !== null && localStorage.getItem('Shop') !== 'undefined' )
  {
  ComboSelect('Slct_depo',JSON.parse(localStorage.getItem('Shop')));
  var disabledField =  document.getElementById('search-box');
  disabledField.disabled = true;
  }
if (localStorage.getItem('Sess_NCIRec') !== null && localStorage.getItem('Sess_NCIRec') !== 'undefined') {
  Sess_NCIRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));    
  Sess_NCIRec.reverse().forEach(function (record) {
    AddField(record.data.Product, record.data.ProductName, record.data.EAN, record.data.Codentify, record.data.Quantity, record.data.UOM, record.data.Type, record.data.DT,record.id);
  });
}

/*****************************************************************************************************/
/*--------------------------------------Scan parcel function-----------------------------------------*/
/*****************************************************************************************************/
function parcelNumber(PN,Disabled,Focus)
  {
  const inputValue = new InputValue(PN);
  let resultInputValue = inputValue.parcelNumber();
  var inputField = document.getElementById(Disabled);
  inputField.value = resultInputValue[0];
  var jsonString = JSON.stringify(resultInputValue[0]);
  localStorage.setItem('ParcelNO', jsonString);  
  console.log(resultInputValue);
  FocusChng(Disabled,Focus)
  }

/*****************************************************************************************************/
/*--------------------------------------Scan EAN function--------------------------------------------*/
/*****************************************************************************************************/
async function Check_EAN(EAN,Disabled,Focus)
  {
  try 
    {
      ID = 0;  
      if (typeof Sess_NCIRec == 'undefined'){ var Sess_NCIRec = []};

      const UOM = new UOMDevice(EAN);
      let DeviceValue = await UOM.processEAN();
      console.log(DeviceValue);

      if (localStorage.getItem('Sess_NCIRec') !== null && localStorage.getItem('Sess_NCIRec') !== 'undefined') {
        Sess_NCIRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));
        var maxID = 0;
        Sess_NCIRec.forEach(function (record) {
          ID = record.id;
          var CDF = record.data.Codentify;
            if (ID > maxID) {
                maxID = ID; 
            }
            if (CDF == DeviceValue[3] && DeviceValue[3] !== '') {
              alert('Duplicitní codentify');
              throw new Error('Duplicitní codentify');   
            }
        });
      ID = maxID + 1
      }

      var DT = NowDate();
      const fields = {
        "Product": DeviceValue[0],
        "ProductName": DeviceValue[1],
        "EAN": DeviceValue[2],
        "Codentify": DeviceValue[3],
        "DT": DT,
        "Quantity": DeviceValue[5],
        "UOM": DeviceValue[4],
        "Type": DeviceValue[6]
      };
      Sess_NCIRec.push({
        id: ID,  
        data: fields
      });
 

    var jsonString = JSON.stringify(Sess_NCIRec);
    localStorage.setItem('Sess_NCIRec', jsonString);

    var bodyField = document.getElementById('BodyField');
    while (bodyField.firstChild) {
      bodyField.removeChild(bodyField.firstChild);
    }
    StorageRow();
    FocusChng(Disabled,Focus);
    }
  catch(error)
    {
    console.error("Chyba při zpracování EAN:", error);
    FocusChng('','EAN');
    }
  }

/*****************************************************************************************************/
/*--------------------------------------Button function----------------------------------------------*/
/*****************************************************************************************************/

function FuncDeleteRow(clickedElementId) 
  {
  var elementsToDelete = document.querySelectorAll("[id='" + clickedElementId + "']");

  elementsToDelete.forEach(function(element) {
  element.parentNode.removeChild(element);
  });

  Sess_NCIRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));
  const indexToDelete = Sess_NCIRec.findIndex(record => record.id === clickedElementId);
  Sess_NCIRec.splice(indexToDelete, 1);
  var jsonString = JSON.stringify(Sess_NCIRec);
  localStorage.setItem('Sess_NCIRec', jsonString);
  var counterField = document.getElementById('counter');
  counterField.value = counterField.value - 1; 
  FocusChng('','EAN');
  }

/*****************************************************************************************************/
function AddLending(clickedElementId) 
  {
  var ChngElement = document.querySelector("input[name='Type'][id='" + clickedElementId + "']");
  ChngElement.value = 'LEND';

  Sess_NCIRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));
  const ChngRecord  = Sess_NCIRec.findIndex(record => record.id === clickedElementId);
  Sess_NCIRec[ChngRecord].data.Type = 'LEND';
  
  var jsonString = JSON.stringify(Sess_NCIRec);
  localStorage.setItem('Sess_NCIRec', jsonString);
  FocusChng('','EAN');
  }

/*****************************************************************************************************/
function ChngQuant(clickedElementId) 
  {
  var userInput = prompt('Zadat nové množství:', '');
  if (userInput !== null) 
    {
      var Number = parseFloat(userInput);

      if (!isNaN(Number)) 
        {
        var ChngElement = document.querySelector("input[name='Quantity'][id='" + clickedElementId + "']");
        ChngElement.value = Number;

        Sess_NCIRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));
        const ChngRecord  = Sess_NCIRec.findIndex(record => record.id === clickedElementId);
        Sess_NCIRec[ChngRecord].data.Quantity = Number;
        var jsonString = JSON.stringify(Sess_NCIRec);
        localStorage.setItem('Sess_NCIRec', jsonString);      
        FocusChng('','EAN');
        }
      else
        {
        alert('Zadaná hodnota musí být číselná.');
        }    
    }
  else 
    {
    }
  }

/*****************************************************************************************************/
function AddBroken(clickedElementId) 
  {
    var ChngElement = document.querySelector("input[name='Type'][id='" + clickedElementId + "']");
    ChngElement.value = 'POSK'

    Sess_NCIRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));
    const ChngRecord  = Sess_NCIRec.findIndex(record => record.id === clickedElementId);
    Sess_NCIRec[ChngRecord].data.Type = 'POSK';

    var jsonString = JSON.stringify(Sess_NCIRec);
    localStorage.setItem('Sess_NCIRec', jsonString);

    FocusChng('','EAN');
  }

/*****************************************************************************************************/
function HeaderButton(Button)
  {
  switch (Button) 
    {
      case 'Delete':
        Sess_NCIRec = undefined
        localStorage.removeItem('Shop'); 
        localStorage.removeItem('ParcelNO');
        localStorage.removeItem('Sess_NCIRec');
        ComboSelect('Slct_depo','Nothing');
        document.getElementById('search-box').value = '';
        document.getElementById('ParcelNO').value = '';
        var bodyField = document.getElementById('BodyField');

        while (bodyField.firstChild) {
              bodyField.removeChild(bodyField.firstChild);
          }
          ID = 0;    
        EnabledChng('ALL');
          row = 0;
        var counterField = document.getElementById('counter');
        counterField.value = row;
            break;

//----------------------------------------------------------------------------------------------------
      case 'Save':
        var SendRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));
        var Shop = JSON.parse(localStorage.getItem('Shop'));
        var ParcelNO = JSON.parse(localStorage.getItem('ParcelNO'));
        fetch('NCIRec.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({'Shop': Shop, 'ParcelNO': ParcelNO , 'NCIRec': SendRec })
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            Sess_NCIRec = undefined
            localStorage.removeItem('Shop'); 
            localStorage.removeItem('ParcelNO');
            localStorage.removeItem('Sess_NCIRec');
            ComboSelect('Slct_depo','Nothing');
            document.getElementById('ParcelNO').value = '';
            document.getElementById('search-box').value = '';
            var bodyField = document.getElementById('BodyField');
    
          while (bodyField.firstChild) {
                bodyField.removeChild(bodyField.firstChild);
            }
          EnabledChng('ALL');
            console.log('Data byla úspěšně odeslána do PHP');
            window.location.href = 'NCIRec.php?Open=';
          })
          .catch(error => {
            console.error('Chyba při odesílání dat do PHP', error);
            window.location.href = 'NCIRec.php?Open=';
          });
        break;

//----------------------------------------------------------------------------------------------------
      case 'Back':
        break;
    }
  }


/*****************************************************************************************************/
/*--------------------------------------Form function------------------------------------------------*/
/*****************************************************************************************************/

function StorageRow()
  {
    Sess_NCIRec = JSON.parse(localStorage.getItem('Sess_NCIRec'));
    row = 0;   
    Sess_NCIRec.reverse().forEach(function (record) {
      AddField(record.data.Product, record.data.ProductName, record.data.EAN, record.data.Codentify, record.data.Quantity, record.data.UOM, record.data.Type, record.data.DT,record.id);
    });
  }

function FocusChng(Disabled,Focus) 
  {
  var inputField = document.getElementById(Focus);
  if(Disabled == '' && Focus == 'EAN')
    {
      inputField.value = '';
    }
  inputField.focus();
  if(Disabled !== '')
    {
    DisabledChng(Disabled);
    }
  }

/*****************************************************************************************************/
function EnabledChng(Field) 
  {
  switch (Field) 
    {
    case 'ParcelNO':
      var disabledField = document.getElementById('ParcelNO');
      disabledField.enabled = true;
      disabledField.focus();
        break;
    case 'Slct_depo':
      var disabledField = document.getElementById('Slct_depo');
      disabledField.enabled = true;
      disabledField.focus();
      break;
    case 'EAN':
      var disabledField = document.getElementById('EAN');
      disabledField.enabled = true;
      disabledField.focus();
      break;


    default:

    if (localStorage.getItem('Shop') == null || localStorage.getItem('Shop') == 'undefined' )
      {        
        var disabledField = document.getElementById('search-box');
        disabledField.disabled = false;
        var disabledField = document.getElementById('Slct_depo');
        disabledField.disabled = false;
      }
        var disabledField = document.getElementById('EAN');
        disabledField.disabled = false;

    if (localStorage.getItem('ParcelNO') == null || localStorage.getItem('ParcelNO') == 'undefined' )
      {
        var disabledField = document.getElementById('ParcelNO');
        disabledField.disabled = false;
      }
      disabledField.focus();
      break;
    }
  }

/*****************************************************************************************************/
function DisabledChng(Disabled) 
  {
    var disabledField = document.getElementById(Disabled);
    disabledField.disabled = true;
  }

/*****************************************************************************************************/
function ComboSelect(Element,id)
  {
  var comboBox = document.getElementById(Element);
  var desiredId = id;

  for (var i = 0; i < comboBox.options.length; i++) 
    {
    if (comboBox.options[i].id === desiredId) 
      {
      comboBox.selectedIndex = i;
      break;
      }
    }
  FocusChng('Slct_depo', 'EAN');
  }

/*****************************************************************************************************/
function AddField(Product,ProductName,EAN,Codentify,Quantity,UOM,Type,DT,ID)
  {
  
    const container = document.getElementById("BodyField");
  if (row === 0) 
    {

    var LabelRow = document.createElement("tr");
    createLabel(LabelRow,"Product");
    createLabel(LabelRow,"Název");
    createLabel(LabelRow,"EAN");
    createLabel(LabelRow,"Codentify");
    createLabel(LabelRow,"Datum/čas");
    createLabel(LabelRow,"OTY");
    createLabel(LabelRow,"UOM");
    createLabel(LabelRow,"Stav");
    container.appendChild(LabelRow);
    }
      var InputRow = document.createElement("tr");
      InputRow.id = ID;     
      createField(InputRow ,"Product","Produkt",ID,Product,'Px120');
      createField(InputRow ,"ProductName","Název",ID, ProductName,'Px400');
      createField(InputRow ,"EAN","EAN",ID, EAN,'Px120');
      createField(InputRow ,"Codentify","Codentify",ID,Codentify,'Px120');
      createField(InputRow ,"DT","Datum/čas",ID, DT,'Px150');
      createField(InputRow ,"Quantity","QTY",ID,Quantity,'Px50');
      createField(InputRow ,"UOM","UOM",ID, UOM,'Px50');
      createField(InputRow ,"Type","Stav",ID,Type,'Px50');

      createButton(InputRow ,"ChngQuant","Změnit množství",ID, ChngQuant);
      createButton(InputRow ,"Broken","Poškozené",ID,AddBroken);
      createButton(InputRow ,"Lending","Lending",ID,AddLending);
      createButton(InputRow ,"DeleteRow","Smazat",ID,FuncDeleteRow);
      container.appendChild(InputRow); 
  row++;
  var counterField = document.getElementById('counter');
  counterField.value = row;
  }

/*****************************************************************************************************/
function createLabel(LabelRow,text) 
  {
    var labelCell = document.createElement("th");
    var labelElement = document.createElement("label");
    labelElement.textContent = text + ":";
    labelCell.appendChild(labelElement);
    LabelRow.appendChild(labelCell);
  }

/*****************************************************************************************************/
function createField(InputRow,name,text,id,value,format) 
  {
    var inputCell = document.createElement("td");
    var inputElement = document.createElement("input");
    inputElement.type = "text";
    inputElement.id = id;
    inputElement.name = name;
    inputElement.value = value;
    inputElement.className = format;
    inputElement.textContent = text;
    inputElement.disabled = true;
    inputCell.appendChild(inputElement);
    InputRow.appendChild(inputCell);
  }

/*****************************************************************************************************/
function createButton(InputRow, name, text, id, CallFunc) 
  {
    var inputCell = document.createElement("td");
    var inputElement = document.createElement("input");
    inputElement.type = "button";
    inputElement.id = id;
    inputElement.name = name;
    inputElement.value = text;
    inputElement.addEventListener("click", function() { CallFunc(id);});
    inputCell.appendChild(inputElement);
    InputRow.appendChild(inputCell);
  }
  
/****************************************************************************************************/
  function searchFunction() {
    var input, filter, select, option, i;
    input = document.getElementById('search-box');
    filter = input.value.toUpperCase();
    select = document.getElementById('Slct_depo');
    option = select.getElementsByTagName('option');

    for (i = 0; i < option.length; i++) {
        if (option[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
            option[i].style.display = "";
        } else {
            option[i].style.display = "none";
        }
    }
}