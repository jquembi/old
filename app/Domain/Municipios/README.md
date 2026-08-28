# 📍 Módulo de Municípios

## Overview

Gerencia os 11 municípios da Huíla com regras de autorização, auditoria e validações de negócio.

---

## Estrutura

```
app/Domain/Municipios/
├── Models/           # Municipio (Entity)
├── Actions/          # CreateMunicipioAction, UpdateMunicipioAction, DeleteMunicipioAction
├── Queries/          # GetMunicipiosQuery (futuro)
├── Policies/         # MunicipioPolicy (autorização)
├── DTOs/             # CreateMunicipioDTO, UpdateMunicipioDTO, ListMunicipiosDTO
├── Exceptions/       # MunicipioNotFoundException, MunicipioAlreadyExistsException
└── Events/           # MunicipioCreatedEvent, MunicipioUpdatedEvent, MunicipioDeletedEvent

app/Application/Services/
└── MunicipioService  # Orquestra Actions
```

---

## Como Usar

### Criando um Município

```php
use App\Domain\Municipios\DTOs\CreateMunicipioDTO;
use App\Domain\Municipios\Actions\CreateMunicipioAction;

$dto = CreateMunicipioDTO::fromArray([
    'nome' => 'Lubango',
    'sede' => 'Lubango',
    'populacao_est' => 500000,
]);

$action = new CreateMunicipioAction();
$municipio = $action->execute($dto); // Lança MunicipioAlreadyExistsException se existe
```

### Via Service

```php
use App\Application\Services\MunicipioService;

app(MunicipioService::class)->create($dto);
```

### Em um Componente Livewire

```php
use App\Application\Services\MunicipioService;
use App\Domain\Municipios\DTOs\ListMunicipiosDTO;

class MunicipiosList extends Component
{
    public function mount()
    {
        $filters = ListMunicipiosDTO::fromArray([
            'page' => 1,
            'per_page' => 15,
            'sort_by' => 'nome',
        ]);

        $municipios = app(MunicipioService::class)->list($filters);
    }
}
```

---

## Autorização

### Policies

```php
// MunicipioPolicy define autorização:

// INOTU: Pode ver, criar, atualizar, deletar TUDO
// Governo Provincial: Pode ver tudo
// Técnico Municipal: Pode ver e atualizar apenas seu município
```

### No Componente

```php
public function render()
{
    // Verifica se o usuário pode ver este município
    $this->authorize('view', $this->municipio);
    
    return view('...');
}
```

### No Template Blade

```blade
@can('delete', $municipio)
    <button wire:click="delete({{ $municipio->id }})">Deletar</button>
@endcan
```

---

## Testes

### Unit Tests

```bash
php artisan test tests/Unit/Domain/Municipios/CreateMunicipioActionTest.php
```

```php
// Testando a Action
public function test_it_creates_municipio_with_valid_data()
{
    $dto = CreateMunicipioDTO::fromArray(['nome' => 'Lubango', 'sede' => 'Lubango']);
    $result = (new CreateMunicipioAction())->execute($dto);
    
    $this->assertDatabaseHas('municipios', ['nome' => 'Lubango']);
}
```

### Feature Tests

```bash
php artisan test tests/Feature/Municipios/
```

```php
// Testando autorização
public function test_inotu_can_delete_municipio()
{
    $inotu = $this->createINOTUUser();
    $municipio = Municipio::factory()->create();
    
    $this->assertTrue($inotu->can('delete', $municipio));
}
```

---

## Eventos

Quando um município é criado/atualizado/deletado, os seguintes eventos são disparados:

```php
// app/Domain/Municipios/Events/

MunicipioCreatedEvent   -> AuditMunicipioListener logs
MunicipioUpdatedEvent   -> AuditMunicipioListener logs
MunicipioDeletedEvent   -> AuditMunicipioListener logs
```

### Adicionando um Novo Listener

```php
// Listener que envia email quando município é criado
class NotifyMunicipioCreated
{
    public function handle(MunicipioCreatedEvent $event)
    {
        Mail::to('admin@inotu.ao')->send(
            new MunicipioCreatedMail($event->municipio)
        );
    }
}

// Registrar em EventServiceProvider.php
protected $listen = [
    MunicipioCreatedEvent::class => [
        AuditMunicipioListener::class,
        NotifyMunicipioCreated::class,  // <-- Novo
    ],
];
```

---

## Validações

### De Negócio (em CreateMunicipioAction)

- ✅ Não permite duplicação de nomes
- ✅ Slug gerado automaticamente
- ✅ Campos obrigatórios verificados

### De Dados (em Form Request, se houver API)

- ✅ Nome obrigatório, string, max 255
- ✅ Sede obrigatória
- ✅ Populacao_est é integer

---

## Logging e Auditoria

Todas as operações são registradas:

```log
[INFO] municipio.created
{
  "municipio_id": 1,
  "municipio_nome": "Lubango",
  "user_id": 5,
  "ip_address": "127.0.0.1",
  "created_at": "2026-03-07T10:30:00Z"
}
```

Consultável com:

```bash
# Via Artisan (se implementado)
php artisan audit:municipios

# Ou ver diretamente nos logs
tail -f storage/logs/laravel.log | grep municipio
```

---

## Checklist para Adicionar Nova Feature

- [ ] **Models**: Adicionar relacionamento em Municipio
- [ ] **DTOs**: CreateDTO, UpdateDTO, ListDTO
- [ ] **Actions**: CreateAction, UpdateAction, DeleteAction
- [ ] **Policy**: Adicionar métodos em MunicipioPolicy
- [ ] **Events**: Criar events se necessário
- [ ] **Listener**: Registrar audit listener
- [ ] **Service**: Adicionar methods em MunicipioService
- [ ] **Tests**: Unit tests + Feature tests
- [ ] **Documentation**: Atualizar este README

---

## Próximas Implementações

- [ ] GetMunicipiosQuery (filtros complexos)
- [ ] MunicipioRepository (abstração de persistência)
- [ ] API REST Controllers (se necessário)
- [ ] Soft Deletes (cascade)
- [ ] Audit detailed logs (tabela `audit_logs`)

---

## Referências

- [Estrutura Geral: ARCHITECTURE.md](../../ARCHITECTURE.md)
- [Decisões Arquiteturais: DECISIONS.md](../../DECISIONS.md)
- [Plano de Profissionalização: PLANO-PROFISSIONALIZACAO.md](../../PLANO-PROFISSIONALIZACAO.md)
- [Checklist de Implementação: CHECKLIST-IMPLEMENTACAO.md](../../CHECKLIST-IMPLEMENTACAO.md)
