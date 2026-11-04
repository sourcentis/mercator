<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $fillable = ['name', 'value'];

    /**
     * Retrieve a parameter value by name, with an optional default.
     */
    public static function getValue(string $name, mixed $default = null): mixed
    {
        return static::where('name', $name)->value('value') ?? $default;
    }

    /**
     * Persist a parameter (insert or update).
     */
    public static function setValue(string $name, mixed $value): void
    {
        static::updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        );
    }
}
