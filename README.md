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

## Evoluções sugeridas (próximos passos)

Abaixo estão melhorias que eu implementaria numa próxima iteração para aproximar o projeto de um cenário real de produção:

### 1) Notificação assíncrona (mensageria / fila)
- Publicar um evento `TransferApproved` após a transferência ser confirmada.
- Processar o envio de notificação em background via fila (Laravel Queue), com:
    - retentativas configuráveis (retry/backoff)
    - dead-letter (DLQ) / falhas persistidas
    - worker separado do processo web
- Benefício: melhora resiliência, reduz latência do endpoint e isola instabilidades do serviço externo de notificação.

### 2) Camada de integração mais robusta para serviços externos
- Adicionar retries com backoff e circuit breaker para o Authorizer/Notifier.
- Timeouts configuráveis por ambiente e métricas para monitorar falhas.
- Padronizar respostas e erros de integração (ex.: exceptions específicas por serviço).

### 3) Observabilidade
- Logs estruturados (correlation id / idempotency key).
- Métricas (ex.: taxa de transferências aprovadas/negadas, tempo médio do authorize/notify).
- Tracing (ex.: OpenTelemetry) para rastrear chamadas externas.

### 4) Ferramentas de qualidade e padronização
- Automatizar PSR-12 com PHP-CS-Fixer.
- Análise estática com PHPStan.
- Regras de complexidade e smells com PHPMD.
- Rodar tudo em CI (ex.: `composer quality`).

### 5) Concorrência e consistência
- Reforçar o comportamento idempotente em cenários de concorrência extrema (múltiplas requisições simultâneas com a mesma chave).
- Garantias adicionais via constraints/índices (ex.: `unique` para `idempotency_key`).
- Auditoria/ledger (registrar histórico de movimentos) para rastreabilidade financeira.

### 6) Segurança e validações adicionais
- Rate limiting no endpoint de transferência.
- Validações extras de request (ex.: impedir `payer == payee`).
- Sanitização e resposta consistente de erros (sem vazar stack trace em produção).

### 7) Documentação e DX
- Documentar a API com OpenAPI/Swagger.
- Collection oficial (Insomnia/Postman) versionada junto do projeto.
- Exemplos de payloads e cenários de teste no README.
