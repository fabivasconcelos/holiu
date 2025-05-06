<?php
require_once __DIR__ . '/../../config.php';

class Product {
    private $conn;

    public function __construct() {
        $cfg = require __DIR__ . '/../../config.php';
        $this->conn = new mysqli(
            $cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name']
        );
    }

    public function findBySlug($slug) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findBuyPodiaId($podia_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE podia_id = ?");
        $stmt->bind_param("s", $podia_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
