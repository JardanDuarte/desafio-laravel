# Meli Integration - Laravel + RabbitMQ

Desafio técnico desenvolvido para integrar com APIs mockadas do Mercado Livre, utilizando Laravel, RabbitMQ e MySQL.

---

## Tecnologias Utilizadas

- PHP 8.3
- Laravel 11
- RabbitMQ
- MySQL 8
- Docker
- Mockoon (API mock)

---

## Arquitetura

O fluxo de processamento segue a seguinte ordem:

1. Autenticação via API Meli-Auth
2. Consulta de anúncios via API Search
3. Processamento individual de cada anúncio via Job
4. Persistência no MySQL
5. Exposição via API REST

Arquitetura baseada em:

- Jobs assíncronos
- Fila RabbitMQ
- Idempotência via `updateOrCreate`
- Tratamento de erros e logs

---

## Subindo o Projeto

### 1️ Clonar o repositório

```bash
git clone https://github.com/SEU_USUARIO/meli-integration-laravel.git
cd desafio-laravel
```

---

### 2️ Subir containers

```bash
docker-compose up -d --build
```

---

### 3️ Instalar dependências

```bash
docker-compose exec app composer install
```

---

### 4️ Configurar ambiente

Copiar .env:

```bash
cd src
cp .env.example .env
```
Criar as seguintes variáveis no .env

MELI_BASE_URL=http://mockoon:3001
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
RABBITMQ_QUEUE=default

Alterar a variável
QUEUE_CONNECTION=database para QUEUE_CONNECTION=rabbitmq


Gerar chave:

```bash
docker-compose exec app php artisan key:generate
```

---

### 5️ Rodar migrations

```bash
docker-compose exec app php artisan migrate
```

---

## Executando o processamento

### Iniciar Worker

```bash
docker-compose exec app php artisan queue:work rabbitmq
```

### Disparar captura

Em outro terminal na raiz do projeto execute os dois comandos abaixo

```bash
docker-compose exec app php artisan tinker
```

```php
Bus::dispatch(new \App\Jobs\CaptureItemsJob());
```

---

## API REST

Endpoint:

```
GET http://localhost:8000/api/items
```

Retorna:

```json
{
  "data": [...],
  "total": 30
}
```

---

## 🐇 RabbitMQ

Painel:

```
http://localhost:15672
```

Usuário: guest  
Senha: guest  

---

## 🧠 Tratamento de Erros

- Tokens inválidos são detectados e logados
- APIs externas tratam 429
- Jobs não derrubam worker
- Logs apresentados no terminal

---

## 📊 Idempotência

Executar múltiplas vezes não gera duplicação de registros.

---

## 📌 Observações

- Implementação preparada para cenários de token inválido
- Arquitetura desacoplada
- Separação clara entre serviços e jobs