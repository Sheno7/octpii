<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Passport\Client as BaseClient;
use Spatie\Permission\Traits\HasRoles;

class PassportClient extends BaseClient implements AuthorizableContract {
    use Authorizable, HasRoles;

    public function guardName() {
        return 'api';
    }
}
