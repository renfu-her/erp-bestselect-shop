<?php

namespace App\Models;

use App\Enums\Accounting\GradeModelClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 主要用來做「總帳會計」->「會計科目」下的CRUD
 * table acc_all_grades的資料會在 AllGrade model 處理
 */
class GeneralLedger extends Model
{
    use HasFactory;

    public const GRADE_TABALE_NAME_ARRAY = [
            '1' => 'acc_first_grade',
            '2' => 'acc_second_grade',
            '3' => 'acc_third_grade',
            '4' => 'acc_fourth_grade',
        ];

    public static function getAllFirstGrade()
    {
        $stdResult = DB::table('acc_first_grade')
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->join('acc_all_grades', function ($join) {
                $join->on('acc_first_grade.id', '=', 'acc_all_grades.grade_id')
                    ->where('acc_all_grades.grade_type', '=', GradeModelClass::getDescription(GradeModelClass::FirstGrade));
            })
            ->select(
                'acc_all_grades.id as primary_id',
                DB::raw(GradeModelClass::FirstGrade .' as grade_num'),
                'acc_first_grade.id',
                'acc_first_grade.code',
                'acc_first_grade.has_next_grade',
                'acc_first_grade.name',
                'acc_first_grade.note_1',
                'acc_first_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }

        return json_decode(json_encode($stdResult), true);
    }

    public static function getSecondGradeById($firstGradeId)
    {
        $stdResult = DB::table('acc_second_grade')
            ->where('first_grade_fk', '=', $firstGradeId)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->join('acc_all_grades', function ($join) {
                $join->on('acc_second_grade.id', '=', 'acc_all_grades.grade_id')
                    ->where('acc_all_grades.grade_type', '=', GradeModelClass::getDescription(GradeModelClass::SecondGrade));
            })
            ->select(
                'acc_all_grades.id as primary_id',
                DB::raw(GradeModelClass::SecondGrade . ' as grade_num'),
                'acc_second_grade.id',
                'acc_second_grade.code',
                'acc_second_grade.name',
                'acc_second_grade.note_1',
                'acc_second_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }

        return json_decode(json_encode($stdResult), true);
    }

    public static function getThirdGradeById($secondGradeId)
    {
        $stdResult = DB::table('acc_third_grade')
            ->where('second_grade_fk', '=', $secondGradeId)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->join('acc_all_grades', function ($join) {
                $join->on('acc_third_grade.id', '=', 'acc_all_grades.grade_id')
                    ->where('acc_all_grades.grade_type', '=', GradeModelClass::getDescription(GradeModelClass::ThirdGrade));
            })
            ->select(
                'acc_all_grades.id as primary_id',
                DB::raw(GradeModelClass::ThirdGrade . ' as grade_num'),
                'acc_third_grade.id',
                'acc_third_grade.code',
                'acc_third_grade.name',
                'acc_third_grade.has_next_grade',
                'acc_third_grade.note_1',
                'acc_third_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }
        return json_decode(json_encode($stdResult), true);
    }

    public static function getFourthGradeById($thirdGradeId)
    {
         $stdResult = DB::table('acc_fourth_grade')
            ->where('third_grade_fk', '=', $thirdGradeId)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
             ->join('acc_all_grades', function ($join) {
                 $join->on('acc_fourth_grade.id', '=', 'acc_all_grades.grade_id')
                     ->where('acc_all_grades.grade_type', '=', GradeModelClass::getDescription(GradeModelClass::FourthGrade));
             })
            ->select(
                'acc_all_grades.id as primary_id',
                DB::raw(GradeModelClass::FourthGrade . ' as grade_num'),
                'acc_fourth_grade.id',
                'acc_fourth_grade.code',
                'acc_fourth_grade.name',
                'acc_fourth_grade.note_1',
                'acc_fourth_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }
        return json_decode(json_encode($stdResult), true);
    }

    /**
     * @param  int|string  $id  層級科目table 的 primary_id
     * @param  string  $grade  1,2,3,4 層級科目
     * @param  bool  $all  是否取得該層級科目的所有資料?
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getDataByGrade($id = 1, string $table = self::GRADE_TABALE_NAME_ARRAY[1], bool $all = false)
    {
        $query = DB::table($table);

        if (!$all) {
            $query = $query->where($table . '.id', '=', $id);
        }

        return $query->leftJoin('acc_company', $table . '.acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', $table . '.acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->select(
                $table . '.id',
                $table . '.code',
                $table . '.name',
                $table . '.has_next_grade',
                $table . '.note_1',
                $table . '.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();
    }

    /**
     * @
     * @param  string  $currentCode 現有的科目代碼
     * @param  string  $newGrade  新的科目代碼是第幾級？ [1, 2, 3, 4]
     * 產生新的科目代碼
     *
     * @return int 回傳新的科目代碼
     */
    public static function generateCode(string $currentCode, string $newGrade)
    {
        $newGradeNum = $newGrade;
        $isGenerateInSameGrade = self::getGradeByCode($currentCode) === $newGradeNum;

        $result = '';
        if ($isGenerateInSameGrade) {
            if ($newGradeNum === '1') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '2') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', substr($currentCode, 0, 1) . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '3') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', substr($currentCode, 0, 2) . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '4') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', substr($currentCode, 0, 4) .'%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            }
        } else {
            if ($newGradeNum === '2') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', $currentCode . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '3') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', $currentCode . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '4') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', $currentCode . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            }
        }

        if (is_null($result)) {
            if ($newGradeNum === '1') {
                return intval('1');
            } elseif ($newGradeNum === '2') {
                return intval($currentCode . '1');
            } elseif ($newGradeNum === '3') {
                return intval($currentCode . '01');
            } elseif ($newGradeNum === '4') {
                return intval($currentCode . '0001');
            }
        }

        return intval($result->code) + 1;
    }

    public static function getGradeByCode(string $code)
    {
        $codeLength = strlen($code);
        $currentGrade = '';
        if ($codeLength === 1) {
            $currentGrade = '1';
        } elseif ($codeLength === 2) {
            $currentGrade = '2';
        } elseif ($codeLength === 4) {
            $currentGrade = '3';
        } elseif ($codeLength === 8) {
            $currentGrade = '4';
        }
        return $currentGrade;
    }

    public static function storeGradeData(array $req, string $grade)
    {
        $newCode = self::generateCode($req['code'], $grade);
        $tableName = self::GRADE_TABALE_NAME_ARRAY[$grade];

        if (strlen($newCode) > 1) {
            $prevGrade  = strval(intval($grade) - 1);
            $prevTableName = self::GRADE_TABALE_NAME_ARRAY[$prevGrade];
        }
        $FOREIGN_KEY_ARRAY = [
            '1' => 'first_grade_fk',
            '2' => 'second_grade_fk',
            '3' => 'third_grade_fk'
        ];

        $prevCode = '';
        if (strlen($newCode) === 2) {
            $prevCode = substr($newCode, 0, 1);
        } elseif (strlen($newCode) === 4) {
            $prevCode = substr($newCode, 0, 2);
        } elseif (strlen($newCode) === 8) {
            $prevCode = substr($newCode, 0, 4);
        }

        $insertData = [
            'name' => $req['name'],
            'code' => $newCode,
            'has_next_grade' => $req['has_next_grade'],
            'acc_company_fk' => $req['acc_company_fk'],
            'acc_income_statement_fk' => $req['acc_income_statement_fk'],
            'note_1' => $req['note_1'],
            'note_2' => $req['note_2'],
        ];

        if (strlen($newCode) > 1) {
            $prevGradeFk = DB::table($prevTableName)
                ->where('code', '=', $prevCode)
                ->select('id')
                ->get();
            $insertData[$FOREIGN_KEY_ARRAY[$prevGrade]] = $prevGradeFk[0]->id;
        }

        $newGradeId = DB::table($tableName)
            ->insertGetId($insertData);
        $gradeType = GradeModelClass::getDescription(intval($grade));

        AllGrade::create([
            'grade_type' => $gradeType,
            'grade_id' => $newGradeId,
        ]);
    }


    public static function total_grade_list()
    {
        $first_grade = self::getAllFirstGrade();
        $list = [];

        foreach ($first_grade as $first_v) {
            $list[] = $first_v;
            foreach (self::getSecondGradeById($first_v['id']) as $second_v) {
                $list[] = $second_v;
                foreach (self::getThirdGradeById($second_v['id']) as $third_v) {
                    $list[] = $third_v;
                    foreach (self::getFourthGradeById($third_v['id']) as $fourth_v) {
                        $list[] = $fourth_v;
                    }
                }
            }
        }

        return $list;
    }


    // $parameter['type'] in r = received_orders, p = paying_orders
    // $parameter['d_type'] in received, payable, product, logistics, discount
    public static function classification_processing(&$debit = [], &$credit = [], $parameter = [])
    {
        $code = $parameter['account_code'] ? $parameter['account_code'][0] : null;
        $name = $parameter['name'] ?? '';
        $price = $parameter['price'] ?? 0;
        $type = $parameter['type'] ?? null;
        $d_type = $parameter['d_type'] ?? null;

        $account_code = $parameter['account_code'] ? $parameter['account_code'] : null;
        $account_name = $parameter['account_name'] ? $parameter['account_name'] : '';
        $method_name = $parameter['method_name'] ? $parameter['method_name'] : '';
        $summary = $parameter['summary'] ? $parameter['summary'] : '';
        $note = $parameter['note'] ? $parameter['note'] : '';
        $product_title = $parameter['product_title'] ? $parameter['product_title'] : '';
        $del_even = $parameter['del_even'] ? $parameter['del_even'] : '';
        $del_category_name = $parameter['del_category_name'] ? $parameter['del_category_name'] : '';
        $product_price = $parameter['product_price'] ? $parameter['product_price'] : '';
        $product_qty = $parameter['product_qty'] ? $parameter['product_qty'] : '';
        $product_owner = $parameter['product_owner'] ? $parameter['product_owner'] : '';
        $discount_title = $parameter['discount_title'] ? $parameter['discount_title'] : '';
        $payable_type = $parameter['payable_type'] ? $parameter['payable_type'] : '0';
        $received_info = $parameter['received_info'] ? $parameter['received_info'] : null;

        if($type == 'r'){
            if(in_array($code, [1, 5]) && $price >= 0){
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>+$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];

                if( in_array($d_type, ['logistics', 'product'])){
                    // 貸方
                    array_push($credit, $tmp);

                } else {
                    // 借方
                    array_push($debit, $tmp);
                }

            } else if(in_array($code, [1, 5]) && $price < 0){
                // 貸方
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>+$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];
                array_push($credit, $tmp);

            } else if(in_array($code, [2, 3, 4]) && $price >= 0){
                // 貸方
                if( in_array($d_type, ['discount']) && $code == 4){
                    $price = (-$price);
                } else {
                    $price = (+$price);
                }

                $tmp = (object)[
                    'name'=>$name,
                    'price'=>$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];

                array_push($credit, $tmp);

            } else if(in_array($code, [2, 3, 4]) && $price < 0){
                // 借方
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>+$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];

                array_push($debit, $tmp);
            }

        } else if($type == 'p'){
            if(in_array($code, [1, 5]) && $price >= 0){
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>+$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];

                if(in_array($d_type, ['payable'])){
                    // 貸方
                    array_push($credit, $tmp);

                } else {
                    // 借方
                    array_push($debit, $tmp);
                }

            } else if(in_array($code, [1, 5]) && $price < 0){
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>+$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];

                if(in_array($d_type, ['logistics'])){
                    // 借方
                    array_push($debit, $tmp);
                } else {
                    // 貸方
                    array_push($credit, $tmp);
                }

                array_push($credit, $tmp);

            } else if(in_array($code, [2, 3, 4]) && $price >= 0){
                if( in_array($d_type, ['discount']) && $code == 4){
                    $price = (-$price);
                } else {
                    $price = (+$price);
                }
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];
                if( in_array($d_type, ['logistics', 'product', 'discount'])){
                    // 借方
                    array_push($debit, $tmp);

                } else {
                    // 貸方
                    array_push($credit, $tmp);
                }

            } else if(in_array($code, [2, 3, 4]) && $price < 0){
                // 借方
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>+$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];

                array_push($debit, $tmp);
            } else {
                // multiple po data - pcs_paying_orders && type == 2
                $tmp = (object)[
                    'name'=>$name,
                    'price'=>+$price,
                    'type'=>$type,
                    'd_type'=>$d_type,

                    'account_code'=>$account_code,
                    'account_name'=>$account_name,
                    'method_name'=>$method_name,
                    'summary'=>$summary,
                    'note'=>$note,
                    'product_title'=>$product_title,
                    'del_even'=>$del_even,
                    'del_category_name'=>$del_category_name,
                    'product_price'=>$product_price,
                    'product_qty'=>$product_qty,
                    'product_owner'=>$product_owner,
                    'discount_title'=>$discount_title,
                    'payable_type'=>$payable_type,
                    'received_info'=>$received_info,
                ];

                if(in_array($d_type, ['payable'])){
                    // 貸方
                    array_push($credit, $tmp);

                } else {
                    // 借方
                    array_push($debit, $tmp);
                }
            }
        }
    }

    public static function getAllGrade() {
        $arr_type = [
            [app(FirstGrade::class)->getTable(), (GradeModelClass::getDescription(GradeModelClass::FirstGrade))]
            , [app(SecondGrade::class)->getTable(), (GradeModelClass::getDescription(GradeModelClass::SecondGrade))]
            , [app(ThirdGrade::class)->getTable(), (GradeModelClass::getDescription(GradeModelClass::ThirdGrade))]
            , [app(FourthGrade::class)->getTable(), (GradeModelClass::getDescription(GradeModelClass::FourthGrade))]
        ];

        $query_first = null;
        for ($i = 0; $i < count($arr_type); $i++) {
            $table = $arr_type[$i][0];
            $type = $arr_type[$i][1];
            $query = DB::table('acc_all_grades')
                ->leftJoin($table. ' as child', function ($join) use($type) {
                    $join->on('child.id', '=', 'acc_all_grades.grade_id')
                        ->where('acc_all_grades.grade_type', '=', $type);
                })
                ->select(
                    'acc_all_grades.id as primary_id'
                    , 'acc_all_grades.grade_type'
                    , 'acc_all_grades.grade_id'
//                    , 'child.id'
                    , 'child.code'
                    , 'child.name'
                    , 'child.note_1'
                    , 'child.note_2'
                    , 'child.acc_company_fk'
                    , 'child.acc_income_statement_fk'
                )
                ->where('acc_all_grades.grade_type', '=', $type)
                ->whereNotNull('child.id')
                ->whereNull('acc_all_grades.deleted_at')
                ->whereNull('child.deleted_at');
            if (null == $query_first) {
                $query_first = $query;
            } else {
                $query_first->union($query);
            }
        }

        return $query_first;
    }
}
