<?php

// app/Contracts/HasPrefix.php

namespace App\Contracts;

interface HasPrefix
{
    public static function getPrefix(): string;
}
