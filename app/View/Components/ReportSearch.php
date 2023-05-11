<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ReportSearch extends Component
{
    public $q_type;
    public $q_season;
    public $q_year;
    public $q_month;
    public $q_sDate;
    public $q_eDate;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($type = '', $season = '', $year = '', $month = '', $sDate = '', $eDate = '')
    {
        $this->q_type = $type !== '' ? $type : 'year';
        $this->q_season = $season;
        $this->q_year = $year;
        $this->q_month = $month;
        $this->q_sDate = $sDate;
        $this->q_eDate = $eDate;
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
                'type' => $this->q_type,
                'season' => $this->q_season,
                'year' => $this->q_year,
                'month' => $this->q_month,
                'sDate' => $this->q_sDate,
                'eDate' => $this->q_eDate,
            ],
            'type' => ['year' => "整年度", "season" => "季", "month" => "月份"],
            'year' => $year,
            'season' => [1 => 'ㄧ', 2 => '二', 3 => '三', 4 => '四']
        ]);
    }
}
