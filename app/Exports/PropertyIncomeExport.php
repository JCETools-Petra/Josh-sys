<?php

namespace App\Exports;

use App\Models\DailyIncome;
use Maatwebsite\Excel\Concerns\FromQuery;

class PropertyIncomeExport implements FromQuery
{
    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return DailyIncome::query();
    }
}
