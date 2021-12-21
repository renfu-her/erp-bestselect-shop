<?php

namespace Tests\Feature;

use App\Models\PermissionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    const TEST_ACCOUNT = 'test_account';
    const TEST_NAME = 'test_name';
    const TEST_EMAIL = 'test_email';
    const TEST_PASSWORD = '9999';
    const NO_PERMISSION_ROLE = 'NoPermissionRole';
    const SUPER_ADMIN = 'Super Admin';

    public function setUp(): void
    {
        parent::setUp();
        $pageAuth = include database_path('seeders/pageAuths.php');

        $all_per = array();
        foreach ($pageAuth as $v) {
            $id = PermissionGroup::create([
                'title' => $v['unit'], 'guard_name' => 'user'
            ])->id;
            foreach ($v['permissions'] as $p) {
                $all_per[] = Permission::create([
                    'guard_name' => 'user', 'name' => $p[0], 'title' => $p[1],
                    'group_id'   => $id
                ]);
            }
        }

        Role::create([
            'guard_name' => 'user',
            'name'       => self::SUPER_ADMIN,
            'title'      => '超級使用角色'
        ])->givePermissionTo($all_per);

        Role::create([
            'guard_name' => 'user',
            'name'       => self::NO_PERMISSION_ROLE,
            'title'      => '無權限角色'
        ])->givePermissionTo([]);

        User::create([
            'name'      => self::TEST_NAME,
            'email'     => self::TEST_EMAIL,
            'account'   => self::TEST_ACCOUNT,
            'password'  => Hash::make(self::TEST_PASSWORD),
            'uuid'      => Str::uuid(),
            'api_token' => Str::random(80),
        ]);

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_all_pageAuth_is_forbidden()
    {
        $user = User::where('name', '=', self::TEST_NAME)->get()->first();
        $user->assignRole(self::NO_PERMISSION_ROLE);
        $this->actingAs(User::find($user->id))
            ->post('/login', [
                'account'  => self::TEST_ACCOUNT,
                'password' => Hash::make(self::TEST_PASSWORD)
            ]);

        $pageAuth = include database_path('seeders/pageAuths.php');

        $this->followingRedirects();
        foreach ($pageAuth as $v) {
            foreach ($v['permissions'] as $p) {
                $this->assertMatchesRegularExpression('/^cms\..*\..*/', $p[0]);
                preg_match_all('/^cms\.(.*)\.(.*)/', $p[0], $matches);
                $action = $matches[1][0];
                $view = $matches[2][0];
                if ($view == 'index') {
                    $view = '';
                } elseif ($view == 'edit') {
                    $view = 'edit/1';
                } elseif ($view == 'delete') {
                    $view = 'delete/1';
                }

//              Only Super Admin could access *.permission.* page
//              there is no route user.permit
                if ($action != 'user' &&
                    $action != 'permission' &&
                    $view != 'permit'
                    ) {
                    $this->get('cms/'.$action.'/'.$view)->assertForbidden();
                    $this->get('cms/'.$action.'/'.$view)
                        ->assertSeeText('使用者沒有適當的權限');
                }
            }
        }
    }
}
