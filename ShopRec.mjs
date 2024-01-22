import { UOMDevice } from './ProjectFunc.mjs';
import { InputValue } from './ProjectFunc.mjs';
import { NowDate } from './ProjectFunc.mjs';

var row;
var Sess_ShopRec;
var ID;
if (typeof row == 'undefined'){ row = 0};
if (typeof Sess_ShopRec == 'undefined'){ var Sess_ShopRec = []};
if (typeof ID == 'undefined'){ID = 0};

//-------------Refresh unsaved value-------------------------------------------------------------------------------------------------------------------------------
if (localStorage.getItem('ParcelNO') !== null && localStorage.getItem('ParcelNO') !== 'undefined' )
  {
  parcelNumber(JSON.parse(localStorage.getItem('ParcelNO')), 'ParcelNO', 'Slct_depo');
  }
if (localStorage.getItem('Shop') !== null && localStorage.getItem('Shop') !== 'undefined' )
  {
  ComboSelect('Slct_depo',JSON.parse(localStorage.getItem('Shop')));
  }
if (localStorage.getItem('Sess_ShopRec') !== null && localStorage.getItem('Sess_ShopRec')  !== 'undefined' )
  {
  Sess_ShopRec = JSON.parse(localStorage.getItem('Sess_ShopRec'));
  Sess_ShopRec.forEach(function(record) 
    {
    AddField(record.data.Product, record.data.ProductName, record.data.EAN, record.data.Codentify, record.data.Quantity, record.data.UOM, record.data.Type,record.data.DT); 
    row++; 
    if(record.id > ID || typeof ID == 'undefined'){ID = record.id};
    })
  }

//-------------------Scan parcel number ---------------------------------------------------------------------------------------------------------------------------
export function parcelNumber(PN,Disabled,Focus)
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

//------------------Scan EAN ---------------------------------------------------------------------------------------------------------------------------------------
export async function Check_EAN(EAN,Disabled,Focus)
{
  try 
  {
    const UOM = new UOMDevice(EAN);
    let DeviceValue = await UOM.processEAN();
    console.log(DeviceValue);
    var DT = NowDate();
    AddField(DeviceValue[0],DeviceValue[1],DeviceValue[2],DeviceValue[3],DeviceValue[5],DeviceValue[4],DeviceValue[6],DT);
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
    Sess_ShopRec.push({
      id: ID,  
      data: fields
    });
  ID++;

  var jsonString = JSON.stringify(Sess_ShopRec);
  localStorage.setItem('Sess_ShopRec', jsonString);  

  FocusChng(Disabled,Focus);
  }
  catch(error)
  {
  console.error("Chyba při zpracování EAN:", error);
  FocusChng('','EAN');
  }
 
}



document.addEventListener('DOMContentLoaded',  EnabledChng('ALL'));  

document.getElementById('Delete').addEventListener('click', function() {
  HeaderButton('Delete');
});

document.getElementById('ParcelNO').addEventListener('change', function() {
  var value = this.value;
  parcelNumber(value, 'ParcelNO', 'Slct_depo');
});

document.getElementById('Slct_depo').addEventListener('change', function() {
  var jsonString = JSON.stringify(this.value);
  localStorage.setItem('Shop', jsonString);  
  FocusChng('Slct_depo', 'EAN');
});

document.getElementById('EAN').addEventListener('change', function() {
  var value = this.value;
  Check_EAN( value,'','EAN');
});


export function FocusChng(Disabled,Focus) 
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

export function EnabledChng(Field) {

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

export function DisabledChng(Disabled) {
  var disabledField = document.getElementById(Disabled);
  disabledField.disabled = true;
}


function AddField(Product,ProductName,EAN,Codentify,Quantity,UOM,Type,DT)
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
    createLabel(LabelRow,"Množství");
    createLabel(LabelRow,"UOM");
    createLabel(LabelRow,"Stav");
    container.appendChild(LabelRow);

    var InputRow = document.createElement("tr");    
    createField(InputRow ,"Product","Product",row,Product);
    createField(InputRow ,"ProductName","Název",row, ProductName);
    createField(InputRow ,"EAN","EAN",row, EAN);
    createField(InputRow ,"Codentify","Codentify",row,Codentify);
    createField(InputRow ,"DT","Datum/čas",row, DT);
    createField(InputRow ,"Quantity","Množství",row,Quantity);
    createField(InputRow ,"UOM","UOM",row, UOM);
    createField(InputRow ,"Type","Stav",row,Type);

    createButton(InputRow ,"ChngQuant","Změnit množství",row);
    createButton(InputRow ,"Broken","Poškozené",row);
    createButton(InputRow ,"Lending","Lending",row);
    createButton(InputRow ,"DeleteRow","Smazat",row,FuncDeleteRow);
    container.appendChild(InputRow);
    }
else
    {
      var InputRow = document.createElement("tr");    
      createField(InputRow ,"Product","Product",row,Product);
      createField(InputRow ,"ProductName","Název",row, ProductName);
      createField(InputRow ,"EAN","EAN",row, EAN);
      createField(InputRow ,"Codentify","Codentify",row,Codentify);
      createField(InputRow ,"DT","Datum/čas",row, DT);
      createField(InputRow ,"Quantity","Množství",row,Quantity);
      createField(InputRow ,"UOM","UOM",row, UOM);
      createField(InputRow ,"Type","Stav",row,Type);
  
      createButton(InputRow ,"ChngQuant","Změnit množství",row);
      createButton(InputRow ,"Broken","Poškozené",row);
      createButton(InputRow ,"Lending","Lending",row);
      createButton(InputRow ,"DeleteRow","Smazat",row, FuncDeleteRow);
      container.appendChild(InputRow);
    }
row++;

function createLabel(LabelRow,text) 
    {
      var labelCell = document.createElement("th");
      var labelElement = document.createElement("label");
      labelElement.textContent = text + ":";
      labelCell.appendChild(labelElement);
      LabelRow.appendChild(labelCell);
    }
function createField(InputRow,name,text,id,value) 
    {
      var inputCell = document.createElement("td");
      var inputElement = document.createElement("input");
      inputElement.type = "text";
      inputElement.id = id;
      inputElement.name = name;
      inputElement.value = value;
      inputElement.textContent = text;
      inputElement.disabled = true;
      inputCell.appendChild(inputElement);
      InputRow.appendChild(inputCell);
    }
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
 
}

function FuncDeleteRow(clickedElementId) {
  var elementsToDelete = document.querySelectorAll("[id='" + clickedElementId + "']");
  const indexToDelete = Sess_ShopRec.findIndex(record => record.id === clickedElementId);

  elementsToDelete.forEach(function(element) {
  element.parentNode.removeChild(element);
 
  });
  Sess_ShopRec.splice(indexToDelete, 1);
  FocusChng('','EAN');
}

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

export function HeaderButton(Button)
{
switch (Button) 
    {
      case 'Delete':
        localStorage.removeItem('Shop'); 
        localStorage.removeItem('ParcelNO');
        localStorage.removeItem('Sess_ShopRec');
        ComboSelect('Slct_depo','Nothing');
        document.getElementById('ParcelNO').value = '';
        var bodyField = document.getElementById('BodyField');

        // Hromadné odstranění všech potomků tohoto prvku
       while (bodyField.firstChild) {
            bodyField.removeChild(bodyField.firstChild);
        }
        
        EnabledChng('ALL');

          break;
      case 'Save':
        break;
      case 'Back':
        break;
    }

}