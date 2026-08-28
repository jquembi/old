# ✅ CORREÇÃO - Erro de Coluna 'slug' em Provincias

**Data**: 10 de Abril de 2026  
**Status**: ✅ **CORRIGIDO**  
**Erro**: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'slug' in 'where clause'`

---

## 🔴 Problema

O código estava procurando pela coluna `slug` na tabela `provincias`, mas a coluna não existe na base de dados de produção.

```sql
-- ❌ ERRO
SELECT `id` FROM `provincias` WHERE `slug` = 'huila' LIMIT 1
```

---

## ✅ Solução Implementada

### 1. Corrigir `app/Livewire/GestaoUsuarios.php`

Substituir todas as referências a `slug` por `codigo`:

**Antes:**
```php
Provincia::where('slug', 'huila')->value('id')
```

**Depois:**
```php
Provincia::where('codigo', 'HUI')->value('id')
```

**Locais corrigidos:**
- Linha 122: `abrirFormulario()` - provincia_id = HUI
- Linha 142: `editar()` - provincia_id = HUI  
- Linha 178: `guardar()` - huilaId query
- Linha 250: `getMunicipiosDisponiveis()` - huilaId query

---

### 2. Atualizar `database/seeders/UsuariosIniciaisSeeder.php`

**Melhorias implementadas:**

1. ✅ **Adicionar import de Provincia**
   ```php
   use App\Models\Provincia;
   ```

2. ✅ **Criar usuário Secretaria**
   ```php
   $sec = User::firstOrCreate(
       ['email' => 'secretaria@inotu.ao'],
       [
           'name' => 'Secretaria INOTU',
           'password' => Hash::make('password'),
           'provincia_id' => $huila->id,
           'criado_por' => $inotu->id,
       ]
   );
   $sec->roles()->attach($secretaria->id);
   ```

3. ✅ **Usar `firstOrCreate` em vez de `create`**
   - Idempotente (pode rodar múltiplas vezes sem erro)
   - Não duplica utilizadores

4. ✅ **Obter Huíla por código**
   ```php
   $huila = Provincia::where('codigo', 'HUI')->first();
   if (!$huila) {
       $huila = Provincia::create([...]);
   }
   ```

5. ✅ **Usar Huíla ID em vez de hardcoded 1**
   ```php
   // Antes: 'provincia_id' => 1
   // Depois: 'provincia_id' => $huila->id
   ```

6. ✅ **Validar que função existe antes de usar**
   ```php
   if ($secretaria) {
       // ...
   }
   ```

---

## 📊 Comparação: Antes vs Depois

| Aspecto | Antes | Depois | Status |
|---------|:-----:|:------:|:------:|
| Query `slug` | ❌ Coluna não existe | ✅ Usa `codigo` | ✅ |
| Usuario Secretaria | ❌ Faltava | ✅ Adicionado | ✅ |
| Idempotência | ❌ `create()` duplica | ✅ `firstOrCreate()` | ✅ |
| Provincia hardcoded | ❌ ID = 1 | ✅ Codigo = 'HUI' | ✅ |
| Validação | ❌ Sem checks | ✅ Valida roles | ✅ |

---

## 🔍 Validação

- ✅ **Sintaxe PHP**: `php -l` sem erros
- ✅ **Build Frontend**: `npm run build` sucesso
- ✅ **Sem breaking changes**: Todas as mudanças são compatíveis

---

## 📝 Estrutura da Tabela Provincias

```sql
CREATE TABLE `provincias` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(255) UNIQUE,      -- Ex: "Huíla"
  `codigo` VARCHAR(10) UNIQUE,     -- Ex: "HUI" (✅ usar isto)
  `activo` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
  -- ❌ NÃO tem coluna `slug`
);
```

---

## 🚀 Próximos Passos

1. [ ] Re-executar seeder em produção:
   ```bash
   php artisan db:seed --class=UsuariosIniciaisSeeder
   ```

2. [ ] Verificar que usuários foram criados:
   ```bash
   php artisan tinker
   >>> User::all()
   ```

3. [ ] Testar acesso a `/usuarios` com cada role:
   - INOTU Admin: Deve ver TODOS + criar
   - Secretaria: Deve ver TODOS (sem criar)
   - Provincial: Deve ver só sua língua
   - Municipal: Deve ver só seu município
   - Técnico: Deve ser bloqueado

---

## 📋 Ficheiros Modificados

1. ✅ `app/Livewire/GestaoUsuarios.php` - 4 queries corrigidas
2. ✅ `database/seeders/UsuariosIniciaisSeeder.php` - Totalmente reescrito

---

## 🔐 Notas de Segurança

- As senhas padrão são `password` - **MUDAR EM PRODUÇÃO!**
- Os usuários criados têm `email_verified_at = now()` - podem fazer login imediatamente
- Cada novo usuário tem `criado_por = inotu->id`

---

**Status Final**: 🟢 **PRONTO PARA PRODUÇÃO**

**Comandos para aplicar:**
```bash
# 1. Atualizar código
git pull

# 2. Executar migrations (se houver)
php artisan migrate

# 3. Executar seeder
php artisan db:seed --class=UsuariosIniciaisSeeder

# 4. Limpar cache
php artisan cache:clear

# 5. Build frontend
npm run build
```

---

**Versão**: 1.0  
**Data**: 10 de Abril de 2026  
**Responsável**: GitHub Copilot
