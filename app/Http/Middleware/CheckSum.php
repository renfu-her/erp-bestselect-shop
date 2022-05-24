<?php

namespace App\Http\Middleware;

use App\Enums\Globals\ResponseParam;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckSum
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $validator = Validator::make($request->all(), [
            'check_date' => 'required',
            'checksum' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }

        $d = $request->all();
        $key = "aabbcccdd";

        $checksum = substr(md5($d['check_date'] . env("CHECKSUM_KEY")), 2, 10);

        if ($d['checksum'] != $checksum) {
            return response()->json([
                ResponseParam::status()->key => 'T02',
                ResponseParam::msg()->key => "checksum error ($checksum)",
            ], 401);
        }

        return $next($request);
    }
}
