<?php

declare(strict_types=1);

$migration = __DIR__.'/../vendor/jquembi/rule-manager/database/migrations/2024_01_01_000001_create_empresas_table.php';

if (! is_file($migration)) {
    return;
}

$contents = file_get_contents($migration);
$original = "Schema::create(\$tableNames['empresas'], function (Blueprint \$table) {";
$fixed = "Schema::create(\$tableNames['empresas'], function (Blueprint \$table) use (\$tableNames) {";

if (str_contains($contents, $original)) {
    file_put_contents($migration, str_replace($original, $fixed, $contents));
}
