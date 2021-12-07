<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Http\MenuTreeTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use MenuTreeTrait;

    protected $table = 'usr_users';
    public $userType = 'user';

    const USER_MENU_TREE =
        [
        [
            "title" => "test",
            "icon" => "bi-signpost-2",
            "menu_id" => "1",
            "child" => [
                [
                    "title" => "home",
                    "controller_name" => "DashboardCtrl",
                    "route_name" => "cms.dashboard",
                ],

            ],
        ],

    ];



    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function menuTree(): array
    {
        return $this->getMenuTree(true,self::USER_MENU_TREE);
    }

    public static function createUser($name, $account, $email, $password, $permission_id = [], $role_id = [])
    {
        $id = self::create([
            'name' => $name,
            'email' => $email,
            'account' => $account,
            'password' => Hash::make($password),
            'uuid' => Str::uuid(),
            'api_token' => Str::random(80),
        ])->id;

        self::where('id', '=', $id)->get()->first()->givePermissionTo($permission_id);
        self::where('id', '=', $id)->get()->first()->assignRole($role_id);

        return $id;
    }
}
