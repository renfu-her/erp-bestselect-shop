<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $permission = Permission::create([
            'guard_name' => 'user',
            'name'       => 'cms.user.index',
            'title'      => '瀏覽',
            'group_id'   => '5'
        ]);

        Role::create([
            'guard_name' => 'user',
            'name'       => 'TestRole',
            'title'      => '內部測試員'
        ])->givePermissionTo($permission->name);

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_it_can_assign_role_and_confirm_role_is_assigned()
    {
        $new_user = User::factory()->create();
        $new_user->assignRole('TestRole');
        $user = User::find($new_user->id);

        $this->assertTrue($user->hasRole('TestRole'));
        $this->assertTrue($user->hasPermissionTo('cms.user.index'));
        $this->assertTrue($user->can('cms.user.index'));
    }
}
