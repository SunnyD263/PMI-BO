function CheckCDF(OrderItem,Scan,ScanItem,Reference)
{

var Checker = 'Codentify';

for (var index in OrderItem) 
    {
    var Codentify = OrderItem[index]['Codentify'];
    if (Codentify == Scan && OrderItem[index]['Sum'] < 0) 
        {
        var ScanQuantity = 1
        var Checker = 'Codentify';
        OrderItem[index]['Sum'] = ScanQuantity - OrderItem[index]['OrdQuantity'] ;
        OrderItem[index]['ScanQuantity'] = ScanQuantity;
        OrderItem[index]['Checker'] = 'Codentify';
        if (OrderItem[index]['EAN'] !== undefined && OrderItem[index]['EAN'] !== null && OrderItem[index]['EAN'] !== '') {EAN = OrderItem[index]['EAN'];} else {EAN =OrderItem[index]['EAN_CRT'];}
        var OrderValue = OrderItem[index];
        var ScanItem = ScanArray(OrderValue,ScanItem,Reference,Scan,EAN);
        break;
        }
    var Checker = 'EAN';   
    }

if (Checker == 'EAN')
    {
    var userInput = prompt('Naskenujte EAN:', '');
    if (userInput !== null) 
        {
        if(userInput.slice(-4) == 'SWAP')
            {        
            for (var index in OrderItem) 
                {
                var Checker = 'EAN';
                var EAN = OrderItem[index]['EAN'];
                var EAN_CRT = OrderItem[index]['EAN_CRT'];           
                if (EAN == userInput || EAN_CRT == userInput) 
                    {
                    var ScanQuantity = 1
                    OrderItem[index]['Sum'] = ScanQuantity - OrdQuantity;
                    OrderItem[index]['ScanQuantity'] = ScanQuantity;
                    if (OrderItem[index]['EAN'] !== undefined && OrderItem[index]['EAN'] !== null && OrderItem[index]['EAN'] !== '') {EAN = OrderItem[index]['EAN'];} else {EAN =OrderItem[index]['EAN_CRT'];}
                    var OrderValue = OrderItem[index];
                    var ScanItem = ScanArray(OrderValue,ScanItem,Reference,Scan,EAN);           
                    break;
                    }
                OrderItem[index]['Checker'] = '';
                var Checker = 'Other';   
                }
            }
        else
            {
            alert('Špatný formát skenovaného EANu. Poslední čtyři zanky musí být SWAP');
            var url = 'SWAP_form.php?Codentify=' + encodeURIComponent(Scan);
            window.location.href = url;
            }    
        }
    else 
        {
        alert('Uživatel stiskl Storno nebo zavřel dialog.');
        window.location.href = 'SWAP_form.php?Open=';
        }
    }

if (Checker == 'Other')
{
var OTHER_ITEM = userInput.slice(0,-4)
var ScanItem = ScanArray('',ScanItem,Reference,Scan,OTHER_ITEM);
}

fetch('SWAP_form.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ 'SWAP_ORDITEM': OrderItem, 'SWAP_SCNITEM': ScanItem })
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      console.log('Data byla úspěšně odeslána do PHP');
      window.location.href = 'SWAP_form.php?Open=';
    })
    .catch(error => {
      console.error('Chyba při odesílání dat do PHP', error);
      window.location.href = 'SWAP_form.php?Open=';
    });
}

function ScanArray (OrderItem,ScanItem,Reference, Codentify, EAN )
{
    if (ScanItem.length !==  0) {var dataArray = ScanItem;} 
    else {var dataArray = [];}

    if(OrderItem !== '')
        {
        var Product = OrderItem["Material"];
        var ProductName = OrderItem["MAKTX"];
        var ScanQuantity = OrderItem['ScanQuantity'];
        var Checker = OrderItem['Checker'];
        }
    else
        {
        var Product = '';
        var ProductName = '';
        var ScanQuantity = 1;
        var Checker = 'Other';
        }
    var currentDateTime = new Date();
    var DateTime = currentDateTime.toISOString().slice(0, 19).replace("T", " ");
        var recordObject = 
        {
        'Reference': Reference,
        'Product': Product,
        'ProductName': ProductName,
        'EAN': EAN,
        'Codentify': Codentify,
        'DateTime': DateTime,
        'ScanQuantity': ScanQuantity,
        'Checker': Checker
        };
        dataArray = AddToArray(dataArray, recordObject)
    return dataArray;
}

function AddToArray(existingArray, record) {
 
    existingArray.push(record);
  
    return  existingArray;
  }

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function Confirmation(Menu) 
{

    switch (Menu)
    {

    case 'Exit':
        if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "SWAP_form.php?Menu=yes";

        } else {
            window.location.href = "SWAP_form.php?Menu=no";
        }
        break;
    case 'Notmatch':
    if (confirm("Codentify neodpovídá očekávanému. Chcete pokračovat?")) {
            window.location.href = "SWAP_form.php?Notmatch=yes";

        } else {
            window.location.href = "SWAP_form.php?Notmatch=no";
        }
        break;
    case 'Codentify':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "SWAP_form.php?Notmatch=yes";

        } else {
            window.location.href = "SWAP_form?Notmatch=no";
        }
        break;
    case 'EAN':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "SWAP_form?Notmatch=yes";

        } else {
            window.location.href = "SWAP_form?Notmatch=no";
        }
        break;
    }    
}