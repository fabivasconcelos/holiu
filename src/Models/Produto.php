<?php
require_once __DIR__ . '/../../config.php';

class Produto {
    private $conn;

    public function __construct() {
        $cfg = require __DIR__ . '/../../config.php';
        $this->conn = new mysqli(
            $cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name']
        );
    }

    public function buscarPorSlug($slug) {
        $stmt = $this->conn->prepare("SELECT * FROM produtos WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function jaComprou($email, $slug) {
        $stmt = $this->conn->prepare("SELECT * FROM pagamentos WHERE email = ? AND slug = ?");
        $stmt->bind_param("ss", $email, $slug);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function registrarCompra($email, $slug) {
        $stmt = $this->conn->prepare("INSERT INTO pagamentos (email, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $slug);
        return $stmt->execute();
    }
}
