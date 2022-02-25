<?php

namespace App\Exports;

use App\Models\Sales;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class SalesQuarterlyExport implements FromView,WithColumnWidths,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     return Sales::all();
    // }
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $records;
    protected $org;
    protected $month;
    protected $year;

    public function __construct(array $records, object $org, string $month, string $year)
    {
        $this->records = $records;
        $this->org = $org;
        $this->month = $month;
        $this->year = $year;
    }

    public function array(): array
    {
        return $this->records;
        return $this->org;
        return $this->month;
        return $this->year;
    }

    public function view(): View
    {
        //dd($this->records);
        return view('exports.quarterly-sales-summary', [
            'records' => $this->records,
            'org' => $this->org,
            'month' => $this->month
        ]);
    }

    public function title(): string
    {
        return 'Sales for '.$this->month.', '.$this->year;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 35,     
            'C' => 35,     
            'D' => 35,     
            'E' => 35,     
            'F' => 35,     
            'G' => 35,     
            'H' => 35,     
            'I' => 35,        
            'J' => 35,     
            'K' => 35,           
        ];
    }
}
