<?php
class PDOConnect 
{
    private static $instance;
    private $conn;
    private $ServerName;
    private $UID;
    private $PWD;
    private $Db;

    public function __construct($Db)    
    {
        try {
            $SQLtxt = file_get_contents('http://localhost/sqldb.txt');
            $items = explode(';', $SQLtxt);
            $this->ServerName = $items[0];
            $this->UID = $items[2];
            $this->PWD = base64_decode($items[3]);
            $this->Db = $Db;
            $this->conn = new PDO("sqlsrv:Server=$this->ServerName;Database=$this->Db", $this->UID, $this->PWD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public static function getInstance($Db)
    {
        if (!self::$instance) {
            self::$instance = new PDOConnect($Db);
        }
        return self::$instance;
    }

    public function select($query, $params = array()) 
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $stmt= array(
                'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'count' => $stmt->rowCount()
                       );
            return $stmt;
        } catch(PDOException $e) {
            echo "Error SQL Select: " . $e->getMessage();
        }
    }

    public function insert($table, $data) 
    {
        try {
            $columns = implode(',', array_keys($data));
            $values = ':' . implode(',:', array_keys($data));      
            $query = "INSERT INTO $table ($columns) VALUES ($values)";        

            $stmt = $this->conn->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();        
            return $stmt->rowCount();
        } catch(PDOException $e) {
            echo "Error SQL Insert: " . $e->getMessage();
        }
    }
    
    public function update($query, $params = array()) 
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            echo "Error SQL Update: " . $e->getMessage();
        }
    }

    public function tempTB($sql,$tableName)
    {
        try {
            $checkTableExists = "IF OBJECT_ID('$tableName', 'U') IS NULL BEGIN $sql END";
    
            $this->conn->exec($checkTableExists);
        } 
        catch (PDOException $e) {
            echo "Chyba při vytváření dočasné tabulky: " . $e->getMessage();
        }
    }
}
?>