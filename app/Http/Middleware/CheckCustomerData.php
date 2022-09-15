<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\UnauthorizedException;

/**
 * 處理能否看到消費者會員專區的資料
 */
class CheckCustomerData
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            throw UnauthorizedException::forPermissions(['登入']);
        }

        //帳號的「角色」「直接」是否有權限瀏覽會員資料
        $customerPermissionExist = false;
        //是否是瀏覽自己的綁定帳號
        $isLinkedCustomerId = false;

        //綁定的消費者ID
        $linkedCustomerId = $user->customer_id ?? null;
        //請求的消費者ID
        $requestCustomerId = '';
        if (preg_match("/cms\\/customer\\/(\d)\\/order/", $request->path(), $match)) {
            $requestCustomerId = $match[1];
        }
        if ($linkedCustomerId == $requestCustomerId) {
            $isLinkedCustomerId = true;
        }

        $customerPermissionId = DB::table('per_permissions')
            ->where('name', 'cms.customer.address')
            ->select('id')
            ->get()
            ->first()
            ->id;
        $roleNames = User::find($user->id)->getRoleNames()->toArray();
        //是否有「直接」權限瀏覽會員
        if (User::find($user->id)->hasPermissionTo('cms.customer.address')) {
            $customerPermissionExist = true;
        } elseif (in_array('Super Admin', $roleNames)) {
            $customerPermissionExist = true;
        } else {
            //檢查：角色的權限是否可以瀏覽會員,這裡別用Spatie套件的function，會有問題
            foreach ($roleNames as $roleName) {
                $roleId = DB::table('per_roles')->where('name', $roleName)->get()->first()->id;
                if (
                    DB::table('per_role_has_permissions')->where([
                        'role_id' => $roleId,
                        'permission_id' => $customerPermissionId,
                    ])->exists()
                ) {
                    $customerPermissionExist = true;
                    break;
                }
            }
        }

        if ($isLinkedCustomerId || $customerPermissionExist) {
            return $next($request);
        } else {
            throw UnauthorizedException::forPermissions(['會員專區', '綁定該消費者的會員帳號']);
        }
    }
}
