<?php
require_once __DIR__ . '/../../config.php';

class Payment {
    private $conn;

    public function __construct() {
        $cfg = require __DIR__ . '/../../config.php';
        $this->conn = new mysqli(
            $cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name']
        );
    }

    public function existsForEmailAndSlug($email, $slug) {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE email = ? AND slug = ? AND `status` = 'approved'");
        $stmt->bind_param("ss", $email, $slug);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function findByEmailAndSlug($email, $slug) {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE email = ? AND slug = ? AND `status` = 'approved' AND podia_register_url IS NOT NULL");
        $stmt->bind_param("ss", $email, $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function exists($paymentCode) {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE payment_code = ?");
        $stmt->bind_param("s", $paymentCode);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function create($name, $lastName, $email, $slug, $status, $paymentCode) {
        $stmt = $this->conn->prepare("INSERT INTO payments (`name`, last_name, email, slug, `status`, payment_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $lastName, $email, $slug, $status, $paymentCode);
        return $stmt->execute();
    }

    public function updateSatus($paymentCode, $status) {
        $stmt = $this->conn->prepare("UPDATE payments SET `status` = ? WHERE payment_code = ?");
        $stmt->bind_param("ss", $status, $paymentCode);
        return $stmt->execute();
    }

    public function updateRegisterURL($paymentCode, $registerURL) {
        $stmt = $this->conn->prepare("UPDATE payments SET podia_register_url = ? WHERE payment_code = ?");
        $stmt->bind_param("ss", $registerURL, $paymentCode);
        return $stmt->execute();
    }
}
