<?php

namespace App\Models;

use App\Factories\SecurityControlFactory;
use App\Traits\Auditable;
use App\Traits\Blameable;
use App\Traits\HasPerimeter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\SecurityControl
 */
class SecurityControl extends Model
{
    use Auditable, Blameable, HasFactory, SoftDeletes;
    use HasPerimeter;

    public $table = 'security_controls';

    public static array $searchable = [
        'name',
        'description',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'description',
    ];

    protected static function newFactory(): Factory
    {
        return SecurityControlFactory::new();
    }
}
