<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SupplierSheet implements FromArray, WithTitle
{
    public $quarter, $data;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($quarter, $data)
    {
        //
      //  dd(explode(',',$data));
        $this->quarter = $quarter;
        $this->data = explode(',',$data);
    }

    public function array(): array
    {
        
        
        $data = array_map(function ($n) {     
            return [$n];
        }, $this->data);
        
        array_unshift($data, ['廠商名稱']);

      
        return $data;
    }

    public function title(): string
    {
        return $this->quarter . '季';
    }
}
