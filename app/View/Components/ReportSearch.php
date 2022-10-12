<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ReportSearch extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {

        $sYear = 2022;
        $year = [];
        for ($i = 0; $i < Date("Y") - $sYear + 1; $i++) {
            $year[] = $sYear + $i;
        }

        return view('components.report-search', [
            'cond' => [
                'month' => '',
                'year' => '',
                'sDate' => '',
                'eDate' => '',
            ],
            'type' => ['year' => "整年度", "season" => "季", "month" => "月份"],
            'year' => $year,
            'season' => [1 => 'ㄧ', 2 => '二', 3 => '三', 4 => '四']
        ]);
    }
}
