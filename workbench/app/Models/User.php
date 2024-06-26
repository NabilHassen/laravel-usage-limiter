<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as AuthUser;
use NabilHassen\LaravelUsageLimiter\Traits\HasLimits;
use Workbench\Database\Factories\UserFactory;

class User extends AuthUser
{
    use HasFactory, HasLimits;

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
