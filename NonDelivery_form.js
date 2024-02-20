
function CheckCDF(OrderItem,Scan,ScanItem,Reference)
{

var Checker = 'Codentify';

//var Status = Scan[1];   // New or used

var Article = Scan[0];   // Scanned Article number
var ArtcName = Scan[1];  // Scanned Article name
var ScanEAN = Scan[2];   // Scanned EAN 
var ScanCDF = Scan[3];   // Scanned Codentify
var Unit = Scan[4];      // Article UOM
var Quantity = Scan[5];  // Quantity
var NonDvc = Scan[6];    // Device or other 
var Error = false;
/******************************************************************************************************************************************************************************/
for (var index in OrderItem) 
    {
    if (ScanCDF !== '' && NonDvc === false)
        {    
        var Codentify = OrderItem[index]['Codentify'];
        if (Codentify == ScanCDF && OrderItem[index]['Sum'] < 0) 
            {
            var Checker = 'Codentify';
            OrderItem[index]['ScanQuantity'] = Quantity;
            OrderItem[index]['Sum'] = OrderItem[index]['ScanQuantity'] - OrderItem[index]['OrdQuantity'];
            OrderItem[index]['Checker'] = Checker;    
            var ScanItem = ScanArray( ScanItem, Reference,Article,ArtcName,ScanEAN,ScanCDF,Quantity,Unit,Checker,OrderItem);     
            break;
            }
            else
            {
            var Checker = 'EAN';  
            } 
        }
    else
        {
            var OrdArtcName  = OrderItem[index]['MAKTX'];
   
        if (OrdArtcName  == ArtcName ) 
            {
            var Checker = 'EANCheck';
            OrderItem[index]['ScanQuantity'] = Quantity + parseInt(OrderItem[index]['ScanQuantity'])  ;
            OrderItem[index]['Sum'] = OrderItem[index]['ScanQuantity'] - OrderItem[index]['OrdQuantity'];
            OrderItem[index]['Checker'] = Checker;                    
//---------------Start scanning codentify for nondvc good need give "ScanCDF" -> to '') 
            var ScanItem = ScanArray( ScanItem,Reference,OrderItem[index]['Material'],ArtcName,ScanEAN,'',Quantity,Unit, Checker,OrderItem);  
            break;
            }
            else
            {
            var Checker = 'Other';  
            } 
        }

    }
/******************************************************************************************************************************************************************************/

if (Checker == 'EAN')
    {
    var userInput = prompt('Naskenujte EAN:', '');
    if (userInput !== null) 
        {
        if(userInput.slice(-4) == 'SWAP' || userInput.length == 13)
            {        
            for (var index in OrderItem) 
                {
                var Checker = 'EAN';
                var EAN = OrderItem[index]['EAN'];
                var EAN_CRT = OrderItem[index]['EAN_CRT'];           
                if (EAN == userInput || EAN_CRT == userInput) 
                    {
                    OrderItem[index]['ScanQuantity'] = Quantity;
                    OrderItem[index]['Sum'] = OrderItem[index]['ScanQuantity'] - OrderItem[index]['OrdQuantity'];
                    OrderItem[index]['Checker'] = Checker;
                    var ScanItem = ScanArray(ScanItem, Reference,Article,ArtcName,ScanEAN,ScanCDF,Quantity,Unit,Checker,OrderItem);
                    break;
                    }
                    else
                    {
                    OrderItem[index]['Checker'] = '';
                    var Checker = 'Other';   
                    }
                }
            }
        else
            {
            alert('Špatný formát skenovaného EANu. Poslední čtyři zanky musí být SWAP');
            var url = 'NonDelivery_form.php?Codentify=' + encodeURIComponent(Scan);
            window.location.href = url;
            }    
        }
    else 
        {
        alert('Uživatel stiskl Storno nebo zavřel dialog.');
        window.location.href = 'NonDelivery_form.php?Open=';
        }
    }

/******************************************************************************************************************************************************************************/

if (Checker == 'Other')
{

    if (ScanCDF !== '' && userInput !== undefined && NonDvc === false)
        {    
        var OTHER_ITEM = userInput.slice(0,-4)
        var ScanItem = ScanArray(ScanItem,Reference,Article,ArtcName,OTHER_ITEM,ScanCDF,Quantity,Unit,Checker,OrderItem);
        }
    else
        {
//---------------Start scanning codentify for nondvc good need give "ScanCDF" -> to '') 
        var ScanItem = ScanArray(ScanItem, Reference,Article,ArtcName,ScanEAN,'',Quantity,Unit, Checker,OrderItem);
        }
}

/******************************************************************************************************************************************************************************/

if (Error == false)
    {
    fetch('NonDelivery_form.php', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 'NonDlv_ORDITEM': OrderItem, 'NonDlv_SCNITEM': ScanItem })
    })
    .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        console.log('Data byla úspěšně odeslána do PHP');
        window.location.href = 'NonDelivery_form.php?Open=';
      })
      .catch(error => {
        console.error('Chyba při odesílání dat do PHP', error);
        window.location.href = 'NonDelivery_form.php?Open=';
      });
    }
else
    {
  fetch('NonDelivery_form.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ 'ERROR': 'Error'})
    })
    .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        console.log('Data byla úspěšně odeslána do PHP');
        window.location.href = 'NonDelivery_form.php?Open=';
      })
      .catch(error => {
        console.error('Chyba při odesílání dat do PHP', error);
        window.location.href = 'NonDelivery_form.php?Open=';
      });

    }
}

/******************************************************************************************************************************************************************************/

function ScanArray (ScanItem,Reference,Article,ArtcName,ScanEAN,ScanCDF,Quantity,Unit,Checker,OrderItem)
{
    if (ScanItem.length !==  0) {var dataArray = ScanItem;} 
    else {var dataArray = [];}


    if (Unit == 'Crt') 
        {
        EAN_CRT = ScanEAN
        EAN = ''
        } 
    else 
        {
        EAN_CRT = ''
        EAN = ScanEAN
        }

if (Checker == 'Other')
    {
        var recordObject = 
        {
        'Material': Article,
        'MAKTX': ArtcName,
        'EAN': EAN,
        'EAN_CRT': EAN_CRT,
        'Codentify': ScanCDF,
        'ScanQuantity': Quantity,
        'OrdQuantity': 0,
        'Sum': Quantity,
        'ORDTyp': OrderItem[1].ORDTyp,
        'Checker': Checker
        };
        AddToArray(OrderItem, recordObject)
    }

    var DT = NowDate();   
        var recordObject = 
        {
        'Reference': Reference,
        'Product': Article,
        'ProductName': ArtcName,
        'EAN': EAN,
        'EAN_CRT': EAN_CRT,
        'Codentify': ScanCDF,
        'DateTime': DT,
        'ScanQuantity': Quantity,
        'Checker': Checker
        };
        dataArray = AddToArray(dataArray, recordObject)
    return dataArray;
}

/******************************************************************************************************************************************************************************/

function AddToArray(existingArray, record) {
 
    existingArray.push(record);
  
    return  existingArray;
  }

/******************************************************************************************************************************************************************************/

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

/******************************************************************************************************************************************************************************/
function Confirmation(Menu) 
{

    switch (Menu)
    {

    case 'Exit':
        if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "NonDelivery_form.php?Menu=yes";

        } else {
            window.location.href = "NonDelivery_form.php?Menu=no";
        }
        break;
    case 'Notmatch':
    if (confirm("Codentify neodpovídá očekávanému. Chcete pokračovat?")) {
            window.location.href = "NonDelivery_form.php?Notmatch=yes";

        } else {
            window.location.href = "NonDelivery_form.php?Notmatch=no";
        }
        break;
    case 'Codentify':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "NonDelivery_form.php?Notmatch=yes";

        } else {
            window.location.href = "NonDelivery_form?Notmatch=no";
        }
        break;
    case 'EAN':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "NonDelivery_form?Notmatch=yes";

        } else {
            window.location.href = "NonDelivery_form?Notmatch=no";
        }
        break;
    }    
}