# Patch local: Rule Manager 1.0.0

O ficheiro `2024_01_01_000001_create_empresas_table.php` da versão 1.0.0
referencia `$tableNames` dentro de uma closure sem o declarar com `use`.
Foi aplicada a correção mínima `use ($tableNames)` para permitir criar a
chave estrangeira auto-referente de `rule_manager_empresas`.

Este patch deve ser incorporado numa próxima versão do pacote. Enquanto isso,
`scripts/patch-rule-manager.php` é executado em cada `composer install` e
`composer update`, tornando a correção reproduzível.
