<?php
class MercadoPagoService {
    private $token;
    private $base_url;

    public function __construct($config) {
        $this->token = $config['mercado_pago_token'];
        $this->base_url = $config['mercado_pago_api_url'];
    }

    public function consultarPagamento($id) {
        $url = $this->base_url . $id;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->token
            ]
        ]);
        $res = curl_exec($ch);
        return json_decode($res, true);
    }
}
