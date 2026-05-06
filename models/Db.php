<?php
class Db {
    private static $instance = NULL;
    private $pdo;

    private function __construct(){
        $this->pdo=new PDO("mysql:host=localhost;dbname=test","root",'');
	    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance(){
        if(self::$instance === NULL){
            self::$instance = new Db();
        }
        return self::$instance;
    }

    public function getConnection(){
        return $this->pdo;
    }
}