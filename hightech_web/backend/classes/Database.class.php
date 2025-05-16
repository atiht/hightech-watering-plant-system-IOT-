<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '-1');
date_default_timezone_set("Asia/Bangkok");
class Database
{
    private $host = "localhost";
    private $uname = "tnjdevelop_hightech";
    private $passwd = "bgXtVsjuJeue6NsRRntV";
    private $dbname = "tnjdevelop_hightech_project";

    public $dbConn;
    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true
        ];
        try {
            $this->dbConn = new PDO($dsn, $this->uname, $this->passwd, $options);
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
    public function db()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true
        ];
        try {
            $pdo = new PDO($dsn, $this->uname, $this->passwd, $options);
            return $pdo;
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
}

$monthNameTH = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
