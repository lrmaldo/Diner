<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\Traits\RefreshesPermissionCache;

class Permission extends Model implements PermissionContract
{
    use HasFactory;
    use RefreshesPermissionCache;

    protected $guarded = [];

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = [
            'name',
            'slug',
            'guard_name',
        ];
    }

    public function getTable()
    {
        return config('permission.table_names.permissions', parent::getTable());
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions', 'permission_role'),
            'permission_id',
            'role_id'
        );
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard')),
            config('permission.table_names.model_has_permissions', 'permission_user'),
            'permission_id',
            'model_id'
        );
    }

    /**
     * Find a permission by its name and guard name.
     */
    public static function findByName(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $permission = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }

    /**
     * Find a permission by its id and guard name.
     */
    public static function findById(int|string $id, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $permission = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id);
        }

        return $permission;
    }

    /**
     * Find or create permission by its name and guard name.
     */
    public static function findOrCreate(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $permission = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            $permission = static::create([
                'name' => $name,
                'guard_name' => $guardName,
                'slug' => \Str::slug($name)
            ]);
        }

        return $permission;
    }
}
