
# 💳 Projeto de Pagamento com Verificação - Holi U

Este é um sistema simples em PHP que permite a criação e verificação de pagamentos para cursos com integração ao Mercado Pago e Zapier. Ele possui:

- Verificação automática de pagamento por email e slug do produto
- Geração de link de pagamento com embed do Mercado Pago
- Botão de verificação manual do pagamento
- Layout completo inspirado no design da plataforma Holi U

---

## 📁 Estrutura

```
/public/index.php              # Ponto de entrada da aplicação
/src/Controllers/              # Lógica da API (PaymentController.php)
    └── PaymentController.php
/src/Models/Produto.php        # Model que interage com o banco de dados
/src/Helpers/mercado_pago.php # Geração de link via API do Mercado Pago
/config.php                    # Carrega variáveis do .env
/.env                          # Contém credenciais e configurações
```

---

## 🚀 Como rodar localmente

### 1. Pré-requisitos

- Docker
- Docker Compose
- Git (opcional)

### 2. Clonar ou baixar o projeto

Você pode baixar o `.zip` ou clonar com:

```bash
git clone https://seurepositorio.com/holiu.git
cd holiu
```

### 3. Criar o arquivo `.env`

Crie o arquivo `.env` na raiz com:

```
DB_HOST=db
DB_NAME=pagamentos
DB_USER=root
DB_PASS=root

MERCADO_PAGO_TOKEN=SEU_TOKEN
MERCADO_PAGO_API_URL=https://api.mercadopago.com/v1/payments/
MERCADO_PAGO_PREFERENCE_URL=https://api.mercadopago.com/checkout/preferences
NOTIFICATION_URL=https://seudominio.com/webhook/mercado_pago.php

ZAPIER_WEBHOOK=https://hooks.zapier.com/hooks/catch/xxxxx/xxxxx/
```

### 4. Subir o projeto com Docker

```bash
docker-compose up -d --build
```

### 5. Acessar no navegador

```bash
http://localhost:9000/index.php?slug=curso-a&email=teste@exemplo.com
```

---

## 🧪 Testes

Você pode simular os cenários de:

- Parâmetros ausentes → mensagem de indisponível
- Produto inexistente → erro
- E-mail já pagou → botão de área de membros
- Geração de link de pagamento
- Verificação manual de pagamento após 3 minutos

---

## 🧰 Scripts importantes

### Banco de dados:

```sql
CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) UNIQUE,
  nome VARCHAR(255),
  podia_id VARCHAR(100),
  preco DECIMAL(10,2)
);

CREATE TABLE pagamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255),
  slug VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📬 Webhook Mercado Pago

Lembre de configurar o webhook no Mercado Pago para apontar para:

```
https://seudominio.com/webhook/mercado_pago.php
```
