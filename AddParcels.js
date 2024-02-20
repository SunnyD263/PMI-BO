
/*****************************************************************************************************/
/* -------------------------------------Scan parcel function-----------------------------------------*/
/*****************************************************************************************************/
function parcelNumber(PN,Disabled,Focus)
  {
  const inputValue = new InputValue(PN);
  let resultInputValue = inputValue.parcelNumber();
  var inputField = document.getElementById(Disabled);
  inputField.value = resultInputValue[0];
  console.log(resultInputValue);
  FocusChng(Disabled,Focus)
  return resultInputValue[0];
  }

/*****************************************************************************************************/
/*-------------------------------------Event function------------------------------------------------*/
/*****************************************************************************************************/
var Parcel_st, Parcel_nd

function submitForm(element) {
  var FormField = document.getElementById(element.name) 
    switch (FormField.name) 
    {
    case 'Slct_depo':
    var jsonString = JSON.stringify(FormField.value);
    localStorage.setItem('PD', jsonString);  
    FormField.form.submit();
      break;
    }
}

function changeForm(element) {
var FormField = document.getElementById(element.name) 
  switch (FormField.name) 
    {
    case 'ParcelNO':
    Parcel_nd = parcelNumber(FormField.value, element.name,'Reference');
      break;
    case 'ParcelNO_nd':
    Parcel_nd = parcelNumber(FormField.value, element.name,'ParcelNO_st');
    var parcelNO_st = document.getElementById('ParcelNO_st');
    parcelNO_st.value = Parcel_nd - 1
      break;
    case 'ParcelNO_st':
    Parcel_st = parcelNumber(FormField.value, element.name, 'Reference');
      break;
    case 'Reference':
    SendToPHP(FormField.value)
      break;      
    }
}



function SendToPHP(value)
{
if (!/^\d{10}$/.test(value)) 
    {
    alert("Hodnota musí být číselná a mít přesně 10 znaků!");
    inputField.focus;
    }
else 
    {
    const field = {
        "Parcel_st": Parcel_st ,
        "Parcel_nd": Parcel_nd ,
        "Reference": value,
    };
        
    fetch('AddParcels.php', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 'Save': field })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        PD = JSON.parse(localStorage.getItem('PD'));
        console.log('Data byla úspěšně odeslána do PHP');
        window.location.href = 'AddParcels.php?Save=' + PD;
        })
        .catch(error => {
        console.error('Chyba při odesílání dat do PHP', error);
        window.location.href = 'AddParcels.php?Open=';
        });              
    }
}

/*****************************************************************************************************/
/*--------------------------------------Form function------------------------------------------------*/
/*****************************************************************************************************/

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
function DisabledChng(Disabled) 
  {
    var disabledField = document.getElementById(Disabled);
    disabledField.disabled = true;
  }