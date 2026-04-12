<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = true;
}
