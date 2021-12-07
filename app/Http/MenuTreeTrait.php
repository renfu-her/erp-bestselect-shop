<?php
namespace App\Http;

trait MenuTreeTrait {
    public function getMenuTree(bool $check_permission_role = true, array $menuTree =[]): array
    {
        if (!$check_permission_role) {
            return  $menuTree;
        }

        if (self::hasRole('Super Admin')) {
            return $menuTree;
        }

        return array_filter(
            array_map(function ($n){
                $n['child'] = array_filter($n['child'], function ($n2) {
                    if (self::can($n2['route_name']) &&
                        !preg_match('/.*\.permission\..*/', $n2['route_name']) &&
                        // test 託運費月報表 for Super Admin
                        !preg_match('/.*\.haulage-report\..*/', $n2['route_name'])
                    ) {
                        return true;
                    }
                    return false;
                });
                return $n;
            }, $menuTree),
            function ($n) {
                return count($n['child']) > 0;
            }
        );
    }

}
