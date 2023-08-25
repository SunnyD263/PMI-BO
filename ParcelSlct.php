<?php
class InputValue 
{
private $Input;
    public function __construct($Input){
        $this->Input = $Input;
    }

    public function DPD()
    {
        $Input = $this->Input;
        switch(true):  
            case (is_numeric ($Input) and strlen(strval($Input)) == 27);
                return array(substr(strval($Input),7,14),"NUM");    
            case is_numeric($Input); 
                return array($Input,"NUM");    
            break;
                
            default:
                if(substr($Input,0,2) == "%0")
                {    
                return array(substr($Input,8,14),"NUM");    
                }
                elseif (substr($Input,0,1) == "Z" and strlen($Input)==11)
                {
                return array(substr($Input,1,10),"NUM");    
                }
                else
                {
                return array($Input,"Text");      
                }
            endswitch;   

    } 
}


?>