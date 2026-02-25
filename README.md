# Desafio Técnico Meli

Desafio técnico desenvolvido para integrar com APIs mockadas do Mercado Livre, utilizando Laravel, RabbitMQ e MySQL.

---

## Tecnologias Utilizadas

- PHP 8.3
- Laravel 12
- RabbitMQ
- MySQL 8
- Docker
- Mockoon (API mock)

---

## Arquitetura

O fluxo de processamento segue a seguinte ordem:

1. Autenticação
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

### 1 Clonar o repositório

```bash
git clone https://github.com/JardanDuarte/desafio-laravel.git
cd desafio-laravel
```

---

### 2 Subir containers

```bash
docker-compose up -d --build
```

---

### 3 Instalar dependências

```bash
docker-compose exec app composer install
```

---

### 4 Configurar ambiente

Copiar .env:

```bash
cd src
cp .env.example .env
```

Criar as seguintes variáveis no .env

MELI_BASE_URL=http://mockoon:3001<br>
RABBITMQ_HOST=rabbitmq<br>
RABBITMQ_PORT=5672<br>
RABBITMQ_USER=guest<br>
RABBITMQ_PASSWORD=guest<br>
RABBITMQ_VHOST=/<br>
RABBITMQ_QUEUE=default<br>

Alterar as variáveis de conexão com o banco<br>
DB_CONNECTION=mysql<br>
DB_HOST=mysql<br>
DB_PORT=3306<br>
DB_DATABASE=laravel<br>
DB_USERNAME=laravel<br>
DB_PASSWORD=root<br>

Alterar a variável
QUEUE_CONNECTION=database para QUEUE_CONNECTION=rabbitmq



Gerar chave:

```bash
docker-compose exec app php artisan key:generate
```

---

### 5 Rodar migrations

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

## RabbitMQ

Painel:

```
http://localhost:15672
```

Usuário: guest  
Senha: guest  

---

## Acessando o Banco de Dados (phpMyAdmin)

Para facilitar a visualização dos dados persistidos no MySQL, o projeto disponibiliza o phpMyAdmin via Docker.

### Acesso

```
http://localhost:8080
```

### Credenciais

- Servidor: `mysql`
- Usuário: `root`
- Senha: `laravel`
- Banco de dados: `laravel`

---

### Verificando os itens processados

1. Acesse o phpMyAdmin.
2. Selecione o banco `laravel`.
3. Abra a tabela `items`.
4. Verifique os registros inseridos após a execução do Job.

Os dados são atualizados caso o processamento seja executado novamente

---

## Estrutura do Projeto

O projeto foi organizado separando claramente responsabilidades entre Services, Jobs e Controllers.

### Services

Localização:

```
app/Services/
```

Responsáveis pela integração com APIs externas.

#### AuthService
- Responsável por obter o access token via API Meli-Auth.
- Valida `inactive_token`.
- Não permite uso de token inválido.

#### SearchService
- Consulta a API Search do Mercado Livre.
- Utiliza Bearer Token.
- Implementa paginação (offset 0 → 25, limit 5).

#### ItemService
- Consulta a API Items para obter detalhes de cada anúncio.
- Retorna dados estruturados para persistência.

#### TokenValidator
- Centraliza a validação do token.
- Evita duplicação de regra de negócio.

Essa separação permite:

- Baixo acoplamento
- Testabilidade
- Clareza de responsabilidade
- Facilidade de manutenção

---

### Jobs

Localização:

```
app/Jobs/
```

A aplicação utiliza processamento assíncrono com RabbitMQ.

#### CaptureItemsJob

Responsável por:

1. Obter token via AuthService
2. Consultar IDs via SearchService
3. Disparar ProcessItemJob para cada ID retornado

Esse Job atua como orquestrador do fluxo.

---

#### ProcessItemJob

Responsável por:

1. Consultar detalhes do anúncio via ItemService
2. Persistir no banco MySQL
3. Atualizar registros existentes

## Tratamento de Erros

- Tokens inválidos são detectados e enviados para o laravel.log
- APIs externas tratam 429
- Jobs não derrubam worker
- Logs apresentados no terminal

---

## Idempotência

Executar múltiplas vezes não gera duplicação de registros.

---

## Observações

- Implementação preparada para cenários de token inválido
- Arquitetura desacoplada
- Separação clara entre serviços e jobs

---

## Sobre o Estudo de caso (opcional)

Levando em consideração a trativa que fiz para o desafio que e somente 30 anuncios e vamos supor que agora passa a ser cerca de 50 mil anúncios, eu ajustaria principalmente a forma de paginação e processamento, hoje a aplicação busca uma quantidade pequena e fixa de itens, mas para 50 mil eu faria a busca paginada de forma incremental, processando página por página, em vez de tentar enfileirar tudo de uma vez, também manteria o uso de jobs, mas distribuiria o processamento com múltiplos workers rodando em paralelo. Em um ambiente real, isso poderia ser feito com múltiplos containers ou pods em Kubernetes, permitindo escalar horizontalmente conforme a demanda. No RabbitMQ, configuraria prefetch para evitar que um único worker consuma muitas mensagens ao mesmo tempo, garantindo melhor balanceamento. Em relação ao banco, para um volume maior eu avaliaria substituir operações individuais por processamento em lote, reduzindo a quantidade de queries e melhorando a eficiência. A ideia principal seria manter o processamento desacoplado, escalável e controlado, sem sobrecarregar nem a fila nem o banco.
