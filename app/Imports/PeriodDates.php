<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Sales;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;

class PeriodDates implements OnEachRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $rows = 0;

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        if($rowIndex == 2){
            dd($row);
        }
    }

}
