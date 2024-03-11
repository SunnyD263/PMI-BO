 class UOMDevice {
    constructor(EAN) {
    this.EAN = EAN
    }

    async processEAN() {
        let EAN = this.EAN
        return new Promise(async (resolve, reject) => {
            try {
                let Type = EAN.slice(-4);

                switch (Type) {
                    case 'SWAP':
                        this.Type = 'SWAP';
                        this.BarCode = EAN.slice(0, -4);
                        this.Codentify = '';
                        this.Material = '';
                        break;
                    default:
                        this.Type = 'NOVE';
                        if (EAN.length >= 40 && EAN.length <= 46) {
                            this.BarCode = EAN.slice(3, 16);
                            this.Codentify = EAN.slice(18, 30).toUpperCase();
                            this.Material = EAN.slice(-11).toUpperCase();
                        } else if (EAN.length === 13) {
                            this.BarCode = EAN;
                            this.Codentify = '';
                            this.Material = '';
                        } else {
                            reject("Špatná hodnota"); 
                            return;
                        }
                        break;
                }
                const processResult = await this.processSQLcmd(this.BarCode);
                console.log(processResult);
                
                resolve([this.Material, this.Product, this.EAN, this.Codentify, this.Unit, this.Quantity, this.Type, this.nonDvc]);
            } catch (error) {
                console.error(error);
                reject(error); 
            }
        });
    }

/******************************************************************************************************************************************************************************/
async processSQLcmd(BarCode) 
    {
    return new Promise(async (resolve, reject) => {
            const types = ["_PK", "_CT", "_BX"];
            for (const type of types) {
                try { 
                    var LastEAN
                    switch (type)
                    {
                    case "_PK":  
                    LastEAN = "LastEAN"
                    break
                    case "_CT":  
                    LastEAN  = "LastEAN_CT"
                    break         
                    case "_BX":  
                    LastEAN  = "LastEAN_BX"                          
                    break
                    }
                    const result = await SQLcmd('ProjectAPI.php', `SELECT * FROM EAN WHERE ${LastEAN} = 1 AND EAN${type} = '${BarCode}'`);
                    if (result && result.count > 0) {
                        this.Product = result.rows[0].MAKTX;
                        this.Material = result.rows[0].MATNR;
                        this.convert = result.rows[0].MATNR.slice(0, 2);
                        this.EAN = result.rows[0].EAN_PK;
                        switch (type)
                            {
                            case "_PK":  
                            this.Unit = "Pack"
                            break
                            case "_CT":  
                            this.Unit = "Crt"
                            break         
                            case "_BX":  
                            this.Unit = "Box"                                   
                            break
                            }
                        this.add();
                        resolve(result);
                        return;
                    }
                } catch (error) {
                    console.error(error);
                    reject(error);
                }
            }
            resolve(false); 
        });
    }

    add() {
        switch (this.convert) {
            case "ME":
            case "MW":
            case "MA":
            case "KA":
                this.Quantity = this.Unit === "Crt" ? 10 : 1;
                this.nonDvc = true;
                break;
            case "MJ":
            case "MU":
                this.Quantity = this.Unit === "Crt" ? 5 : 1;
                this.nonDvc = true;
                break;
            case "DR":
                this.Quantity = this.Unit === "Crt" ? 50 : 1;
                this.nonDvc = true;
                break;
            case "DE":
            case "DF":
            case "DP":
                this.Quantity = this.Unit === "Crt" ? 10 : 1;
                this.nonDvc = true;
                break;
            default:
                // (E,DK,DA,DC)
                this.nonDvc = false;
                this.Quantity = 1;
                break;
        }
        this.Unit = "Pack"

    }
}
/******************************************************************************************************************************************************************************/
 class InputValue {
    constructor(Input) {
        this.Input = Input;
    }

    parcelNumber() {
        let Input = this.Input;
        switch (true) {
            case (typeof Input === 'number' && Input.toString().length === 27):
                return [Input.toString().slice(7, 21), "NUM", "DPD"];
            case (typeof Input === 'number' && Input.toString().length === 12):
                return [Input.toString().slice(0, 11), "NUM", "DPD"];
            case typeof Input === 'number':
                return [Input.toString(), "NUM"];
            default:
                if (Input.slice(0, 2) === "%0") {
                    return [Input.slice(8, 22), "NUM", "DPD"];
                } else if (Input.charAt(12) === "X" && Input.length === 13) {
                    return [Input, "NUM", "ČP"];
                } else if (Input.charAt(0) === "Z" && Input.length === 11) {
                    return [Input.slice(1), "NUM", "Packeta"];
                } else if (Input.length === 17 && Input.charAt(11) === "-") {
                    return [Input.slice(0, 11), "NUM", "PPL"];
                } else {
                    return [Input, "Text", "Unkwonw"];
                }
        }
    }
}

/******************************************************************************************************************************************************************************/
function SQLcmd(SQL_Script, SQLcmd) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            type: "POST",
            url: SQL_Script,
            data: { SQL_Select: SQLcmd },
            dataType: "json",
            success: function(data) {
                resolve(data); 
            },
            error: function(xhr, status, error) {
                console.error("Chyba při komunikaci se serverem: " + error);
                reject(error); 
            }
        });
    });
}

/******************************************************************************************************************************************************************************/
 function NowDate()
{
    var currentDateTime = new Date();
    var offset = 0; // time change by hours
    var isDST = function(date) {
        var jan = new Date(date.getFullYear(), 0, 1);
        var jul = new Date(date.getFullYear(), 6, 1);
        return Math.min(jan.getTimezoneOffset(), jul.getTimezoneOffset()) === date.getTimezoneOffset();
    };
    var offsetHours = offset * 60 * 60 * 1000;
    if (isDST(currentDateTime)) {
        offsetHours += 60 * 60 * 1000;
    }
    var adjustedDateTime = new Date(currentDateTime.getTime() + offsetHours);
    
    var year = adjustedDateTime.getFullYear();
    var month = (adjustedDateTime.getMonth() + 1).toString().padStart(2, '0');
    var day = adjustedDateTime.getDate().toString().padStart(2, '0');
    var hours = adjustedDateTime.getHours().toString().padStart(2, '0');
    var minutes = adjustedDateTime.getMinutes().toString().padStart(2, '0');
    var seconds = adjustedDateTime.getSeconds().toString().padStart(2, '0');
    
    var formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    
    console.log(formattedDateTime);
    
    return formattedDateTime
}