<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\RefreshesPermissionCache;

class Role extends Model implements RoleContract
{
    use HasFactory;
    use HasPermissions;
    use RefreshesPermissionCache;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
        return config('permission.table_names.roles', parent::getTable());
    }

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions', 'permission_role'),
            'role_id',
            'permission_id'
        );
    }

    /**
     * A role may be assigned to various users.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard')),
            config('permission.table_names.model_has_roles', 'role_user'),
            'role_id',
            'model_id'
        );
    }

    /**
     * Find a role by its name and guard name.
     */
    public static function findByName(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::named($name);
        }

        return $role;
    }

    /**
     * Find a role by its id and guard name.
     */
    public static function findById(int|string $id, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    /**
     * Find or create a role by its name and guard name.
     */
    public static function findOrCreate(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            $role = static::create(['name' => $name, 'guard_name' => $guardName, 'slug' => \Str::slug($name)]);
        }

        return $role;
    }

    /**
     * Determine if the user may perform the given permission.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission, $guardName ?? $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = app(Permission::class)->findById($permission, $guardName ?? $this->getDefaultGuardName());
        }

        if (! $this->getGuardNames()->contains($permission->guard_name)) {
            throw GuardDoesNotMatch::create($permission->guard_name, $this->getGuardNames());
        }

        return $this->permissions->contains('id', $permission->id);
    }
}
