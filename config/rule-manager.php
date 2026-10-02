<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tabelas da base de dados
    |--------------------------------------------------------------------------
    */
    'table_names' => [
        // A aplicação possui ACL legado. O Rule Manager opera em tabelas
        // próprias para permitir validação e rollback sem sobrepor os dados.
        'roles'                  => 'rule_manager_roles',
        'permissions'            => 'rule_manager_permissions',
        'model_has_roles'        => 'rule_manager_model_has_roles',
        'model_has_permissions'  => 'rule_manager_model_has_permissions',
        'role_has_permissions'   => 'rule_manager_role_has_permissions',
        'empresas'               => 'rule_manager_empresas',
        'permission_audit_logs'  => 'permission_audit_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Coluna de chave primária do model de utilizador
    |--------------------------------------------------------------------------
    */
    'column_names' => [
        'model_morph_key' => 'model_id',
        'empresa_foreign_key' => 'empresa_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plano / licença ativa
    |--------------------------------------------------------------------------
    | 'basico'    -> ideal para pequenos negócios (1 empresa, papéis simples)
    | 'corporativo' -> multi-empresa/filiais, auditoria, hierarquia de cargos
    | 'premium'   -> tudo do corporativo + motor de sugestão e otimização
    |
    | Isto controla apenas quais funcionalidades ficam disponíveis nesta
    | instalação; o pacote não faz nenhuma verificação externa de licença.
    */
    'plano' => env('RULE_MANAGER_PLANO', 'corporativo'), // basico|corporativo|premium

    /*
    |--------------------------------------------------------------------------
    | Multi-empresa (grupos económicos, holdings, filiais)
    |--------------------------------------------------------------------------
    */
    'multi_empresa' => [
        'ativo' => env('RULE_MANAGER_MULTI_EMPRESA', false),
        'validar_nif' => true, // valida o NIF angolano (9 dígitos + letra, formato AGT)
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache das permissões
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ativo' => true,
        'ttl_segundos' => 60 * 60, // 1 hora
        'chave' => 'angotech.rule_manager.cache',
        'store' => env('RULE_MANAGER_CACHE_STORE', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auditoria (recomendado para o plano corporativo/premium)
    |--------------------------------------------------------------------------
    */
    'auditoria' => [
        'ativo' => true,
        'reter_dias' => 365, // conservação alinhada à boa prática de retenção de logs
    ],

    /*
    |--------------------------------------------------------------------------
    | Motor de sugestão e otimização (módulo Premium)
    |--------------------------------------------------------------------------
    */
    'otimizacao' => [
        // similaridade mínima (Jaccard, 0-1) entre dois papéis para sugerir fusão
        'limiar_similaridade_papeis' => 0.80,

        // percentagem mínima de sobreposição para sugerir agrupar permissões
        'limiar_coocorrencia_permissoes' => 0.90,

        // nº de dias sem uso registado para considerar uma permissão "não utilizada"
        'dias_inatividade_permissao' => 90,

        // nº mínimo de utilizadores para o motor gerar sugestão de novo papel
        'minimo_utilizadores_para_cluster' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Guard de autenticação por omissão
    |--------------------------------------------------------------------------
    */
    'guard_padrao' => 'web',
];
