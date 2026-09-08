<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/auth.php';

function hospitalScopeWhere(string $alias = ''): array
{
    $prefix = $alias === '' ? '' : rtrim($alias, '.') . '.';
    return [$prefix . 'hospital_id = :hospital_id', ['hospital_id' => currentHospitalId()]];
}