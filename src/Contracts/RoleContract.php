<?php

namespace EragPermission\Contracts;

interface RoleContract
{
    public function permissions();

    public function users();
}
