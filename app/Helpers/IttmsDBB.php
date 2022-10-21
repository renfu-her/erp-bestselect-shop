<?php
namespace App\Helpers;

use Closure;
use Illuminate\Support\Facades\DB;

class IttmsDBB {
    public static function transaction(Closure $callback) {
        DB::beginTransaction();
        try {
            $result = $callback->__invoke();
            if ($result['success'] == 1) {
                DB::commit();
            }
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }
}
