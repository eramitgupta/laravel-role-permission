<?php

/*
* Author: eramitgupta
* Email: info.eramitgupta@gmail.com
*
* Copyright (c) 2024 by eramitgupta.
* All rights reserved.
*
* You cannot steal and copy my code, I have the copyright, I do not give authority.
*/

if (! function_exists('hasRole')) {
    function hasRole($role): bool
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }
}

if (! function_exists('hasPermissions')) {
    function hasPermissions(string $permission): bool
    {
        return auth()->check() && auth()->user()->hasPermissions($permission);
    }
}

if (! function_exists('getPermissions')) {
    function getPermissions(): array
    {
        return auth()->check()
            ? auth()->user()?->roles->flatMap(fn ($role) => $role?->permissions?->pluck('name'))
                ->unique()->values()->toArray()
            : [];
    }
}

if (! function_exists('getRoles')) {
    function getRoles(): array
    {
        return auth()->check()
            ? auth()->user()?->roles->pluck('name')->unique()->values()->toArray()
            : [];
    }
}
