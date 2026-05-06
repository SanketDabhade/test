<?php
require_once __DIR__ . '/Db.php';
class CodeHistory
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Db::getInstance()->getConnection();
    }

    public function save($code, $result)
    {
        $stmt = $this->pdo->prepare("INSERT INTO history (code, history) VALUES (?, ?)");
        return $stmt->execute([$code, json_encode($result)]);
    }

    public function all()
    {
        $prepared = $this->pdo->prepare("SELECT * FROM history ORDER BY id DESC");
        $prepared->execute();
        return $prepared->fetchAll(PDO::FETCH_ASSOC);
    }
}