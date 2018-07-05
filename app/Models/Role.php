<?php

namespace App\Models;

use MabeEnum\Enum;

/**
 * \App\Models\Role.
 *
 * @method static Role GUEST()
 * @method static Role USER()
 * @method static Role ADMIN()
 */
class Role extends Enum
{
    const GUEST = 0;
    const USER  = 1;
    const ADMIN = 2;
}
