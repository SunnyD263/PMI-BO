
function CheckCDF(OrderItem,Scan,ScanItem,Reference)
{

// control value for variant import to SQLsrv
var Checker = 'Codentify';

for (var index in OrderItem) 
    {
    var Codentify = OrderItem[index]['data']['Codentify'];
// check scan codentify with order codentify
    if (Codentify == Scan ) 
        {
        var ScanQuantity = 1
        var Checker = 'Codentify';
        OrderItem[index]['data']['Sum'] = ScanQuantity - OrderItem[index]['data']['OrdQuantity'] ;
        OrderItem[index]['data']['ScanQuantity'] = ScanQuantity;
        if (OrderItem[index]['data']['EAN'] !== undefined && OrderItem[index]['data']['EAN'] !== null && OrderItem[index]['data']['EAN'] !== '') {EAN = OrderItem[index]['data']['EAN'];} else {EAN =OrderItem[index]['data']['EAN_CRT'];}        OrderItem[index]['data']['Checker'] = Checker;  
        var OrderValue = OrderItem[index];
        var ScanItem = ScanArray(OrderValue,ScanItem,Reference,Scan,EAN,parseInt(index));
        break;
        }
    var Checker = 'EAN';   
    }
// if scan codentify !== order codentify then scan EAN 
if (Checker == 'EAN')
    {
    var userInput = prompt('Naskenujte EAN:', '').toUpperCase();
    if (userInput !== null) 
        {
        if(userInput.slice(-4) == 'SWAP' || userInput == 'ACCS1234567890')
            { 
            var Checker = 'Other';
            var IndexID = 0;                   
            for (var index in OrderItem) 
                {                
                IndexID++;
                }
                var recordObject = 
                {
                'Material' : '',
                'MAKTX' : '',
                'EAN': userInput.slice(0,-4),
                'EAN_CRT': '',
                'Codentify': Scan,
                'ScanQuantity': 1,
                'OrdQuatity': 0,
                'Sum' : 1,
                'Checker': 'Other'     
                };

                OrderItem.push({
                    id: IndexID,  
                    data: recordObject
                  });               
// special aticle for accessories
            if (Scan == 'ACCS1234567890')
                { 
                var ScanItem = ScanArray('',ScanItem,Reference,Scan,'8999999999999',IndexID);
                }
            else
                {
                var OTHER_ITEM = userInput.slice(0,-4)
                var ScanItem = ScanArray('',ScanItem,Reference,Scan,OTHER_ITEM,IndexID);
                }
            }
        else
            {
            alert('Špatný formát skenovaného EANu. Poslední čtyři zanky musí být SWAP');
            var url = 'TRADEIN_form.php?Codentify=' + encodeURIComponent(Scan);
            window.location.href = url;
            }    
        }
    else 
        {
        alert('Uživatel stiskl Storno nebo zavřel dialog.');
        window.location.href = 'TRADEIN_form.php?Open=';
        }
    }


fetch('TRADEIN_form.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ 'TRADEIN_ORDITEM': OrderItem, 'TRADEIN_SCNITEM': ScanItem })
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      console.log('Data byla úspěšně odeslána do PHP');
      window.location.href = 'TRADEIN_form.php?Open=';
    })
    .catch(error => {
      console.error('Chyba při odesílání dat do PHP', error);
      window.location.href = 'TRADEIN_form.php?Open=';
    });
}

function ScanArray (OrderItem,ScanItem,Reference,Codentify,EAN,ID)
{
    if(OrderItem !== '')
        {
        var Product = OrderItem['data']["Material"];
        var ProductName = OrderItem['data']["MAKTX"];
        var ScanQuantity = OrderItem['data']['ScanQuantity'];
        var Checker = OrderItem['data']['Checker'];
        }
    else
        {
        var Product = '';
        var ProductName = '';
        var ScanQuantity = 1;
        var Checker = 'Other';
        }

        var DT = NowDate();

        var recordObject = 
        {
        'Reference': Reference,
        'Product': Product,
        'ProductName': ProductName,
        'EAN': EAN,
        'Codentify': Codentify,
        'DateTime': DT,
        'ScanQuantity': ScanQuantity,
        'Checker': Checker
        };
        ScanItem.push({
            id: ID,  
            data: recordObject
          });     
    return ScanItem;
}

function Confirmation(Menu) 
{

    switch (Menu)
    {

    case 'Exit':
        if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "TRADEIN_form.php?Menu=yes";

        } else {
            window.location.href = "TRADEIN_form.php?Menu=no";
        }
        break;
    case 'Notmatch':
    if (confirm("Codentify neodpovídá očekávanému. Chcete pokračovat?")) {
            window.location.href = "TRADEIN_form.php?Notmatch=yes";

        } else {
            window.location.href = "TRADEIN_form.php?Notmatch=no";
        }
        break;
    case 'Codentify':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "TRADEIN_form.php?Notmatch=yes";

        } else {
            window.location.href = "TRADEIN_form?Notmatch=no";
        }
        break;
    case 'EAN':
    if (confirm("Neuložená data se ztratí. Chcete pokračovat?")) {
            window.location.href = "TRADEIN_form?Notmatch=yes";

        } else {
            window.location.href = "TRADEIN_form?Notmatch=no";
        }
        break;
    }    
}