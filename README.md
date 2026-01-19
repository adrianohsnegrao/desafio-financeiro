# Desafio Financeiro (Etapa Técnica)

API REST para transferências entre usuários (comuns e lojistas), com validações de saldo, autorização externa e notificação externa.

## Regras de negócio implementadas

- Usuários comuns podem transferir para outros usuários comuns e para lojistas
- Lojistas **não podem** realizar transferências (apenas recebem)
- Validação de saldo antes da transferência
- Consulta a serviço autorizador externo antes de efetivar a operação
- Transferência é executada dentro de transação (atomicidade)
- Notificação externa é disparada após a criação, mas falhas de notificação **não** quebram a transferência
- Idempotência:
    - Endpoint principal: via `idempotency_key` no body
    - Endpoint compatível (enunciado): via header `Idempotency-Key`
## Endpoints

### Endpoint compatível com o enunciado do desafio
`POST /api/transfer`

## Headers (recomendado):
- `Content-Type: application/json`
- `Idempotency-Key: <uuid>` (opcional; se não enviar, o sistema gera uma chave)

Body:
```json
{
  "value": 100.0,
  "payer": 4,
  "payee": 15
}
```

Endpoint principal do projeto
POST /api/transfers

Body:
```
{
  "payer_id": 4,
  "payee_id": 15,
  "amount": 100.0,
  "idempotency_key": "11111111-1111-1111-1111-111111111111"
}
```
### Serviços externos (mocks do desafio)
- Authorizer (GET): https://util.devi.tools/api/v2/authorize

- Notifier (POST): https://util.devi.tools/api/v1/notify

### Configuráveis via .env:

```
TRANSFER_AUTHORIZER_URL

TRANSFER_NOTIFIER_URL
```

## Rodando localmente (sem Docker)
1. #### Instalar dependências:

```
composer install
```

2. #### Configurar ambiente:
```
cp .env.example .env
php artisan key:generate
```
3. #### Banco (sqlite recomendado para simplicidade):

    #### No .env:
```
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

#### Criar arquivo e rodar migrations:

```
touch database/database.sqlite
php artisan migrate
```

4. #### Subir API:
```
php artisan serve
```

### Testes
#### Rodar testes:
```
php artisan test
```

## Rodando com Docker
1. ### Build e subir:
```
docker compose up --build
```
2. ### Rodar testes no container:
```
docker compose exec app php artisan test
```

## Postman
Importe a collection em ``postman_collection.json`` (inclusa/recomendada) e ajuste a variável ``base_url`` se necessário.
