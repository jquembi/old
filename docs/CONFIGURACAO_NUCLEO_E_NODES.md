# Configuração do núcleo e nodes

Cada servidor executa uma instalação independente do mesmo código. Não use uma única instalação para hospedar bases de dados de províncias diferentes.

## Núcleo nacional

No servidor do núcleo, aponte `gterra.gov.ao` para a pasta `public/` e execute:

```bash
php artisan gterra:setup --mode=central --domain=gterra.gov.ao --database=gterra_central
php artisan config:clear
php artisan migrate --force
```

Use uma base de dados exclusiva para o núcleo. O núcleo recebe atualizações dos nodes e continua a operar quando qualquer um deles estiver indisponível.

## Node Huíla

No servidor da Huíla, aponte `huila.gterra.gov.ao` para a pasta `public/` e execute:

```bash
php artisan gterra:setup --mode=node --code=HUI --name="Província da Huíla" --domain=gterra.gov.ao --database=gterra_huila --central-url=https://gterra.gov.ao
php artisan config:clear
php artisan migrate --force
```

Defina `TENANT_SYNC_TOKEN` com um token Sanctum criado no núcleo para a conta de serviço da Huíla. Nunca reutilize a base de dados, chave de aplicação ou token de outro node.

## Resiliência

- Operações de utilizadores são sempre gravadas primeiro na base do node.
- Alterações para o núcleo entram em `node_sync_outbox` e são enviadas em segundo plano.
- Se `gterra.gov.ao` estiver indisponível, o node guarda o erro e tenta novamente; o atendimento provincial continua.
- O núcleo não abre conexões para as bases dos nodes: recebe somente requisições enviadas por eles.
- Programe `php artisan schedule:run` a cada minuto em cada node para processar a caixa de saída.

Exemplo de cron:

```cron
* * * * * cd /var/www/gterra-huila && php artisan schedule:run >> /dev/null 2>&1
```

Os modelos Nginx estão em `deploy/nginx/`. Configure TLS e os registos DNS A/CNAME antes de expor os domínios.
