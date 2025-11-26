<?php
declare(strict_types=1);

require_once __DIR__ . '/config.inc.php';

function conectarPDO(): PDO
{
    try {
        $pdo = new PDO(DSN, USUARIO, SENHA);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}
