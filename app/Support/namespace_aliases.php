<?php

/**
 * Soft namespace aliases so controllers can reference canonical classes
 * (App\Middleware\AuthMiddleware, App\Helpers\DB, App\Helpers\Csrf)
 * even if the actual classes live under different namespaces in your project.
 *
 * Include this early in bootstrap (public/index.php) before routing/dispatch.
 */

/**
 * Create a class alias to the first existing FQCN in $candidates.
 *
 * @param string[] $candidates
 * @param string   $alias
 * @return void
 */
function _alias_first_existing(array $candidates, $alias)
{
    // If alias already resolved/loaded, do nothing
    if (class_exists($alias, false) || interface_exists($alias, false) || trait_exists($alias, false)) {
        return;
    }

    foreach ($candidates as $fqcn) {
        if (class_exists($fqcn) || interface_exists($fqcn) || trait_exists($fqcn)) {
            class_alias($fqcn, $alias);
            return;
        }
    }
}

/**
 * AuthMiddleware
 * Canonical alias: App\Middleware\AuthMiddleware
 * Add/remove candidates to match your project if needed.
 */
_alias_first_existing([
    'App\Middleware\AuthMiddleware',
    'App\Helpers\AuthMiddleware',
    'App\Core\AuthMiddleware',
    'App\Security\AuthMiddleware',
], 'App\Middleware\AuthMiddleware');

/**
 * DB
 * Canonical alias: App\Helpers\DB
 */
_alias_first_existing([
    'App\Helpers\DB',
    'App\Core\DB',
    'App\Database\DB',
    'App\Support\DB',
], 'App\Helpers\DB');

/**
 * Csrf
 * Canonical alias: App\Helpers\Csrf
 */
_alias_first_existing([
    'App\Helpers\Csrf',
    'App\Security\Csrf',
    'App\Support\Csrf',
    'App\Core\Csrf',
], 'App\Helpers\Csrf');
