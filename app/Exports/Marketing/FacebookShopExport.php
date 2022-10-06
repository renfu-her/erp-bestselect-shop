<?php

namespace App\Exports\Marketing;

use Maatwebsite\Excel\Concerns\FromArray;

//匯出臉書商店CSV
class FacebookShopExport implements FromArray
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * use array to export
     */
    public function array(): array
    {
        return $this->data;
    }
}
