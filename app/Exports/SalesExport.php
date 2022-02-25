<?php

namespace App\Exports;

use App\Models\Sales;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class SalesExport implements FromView,WithColumnWidths
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
    protected $sales;

    public function __construct(array $sales)
    {
        $this->sales = $sales;
    }

    public function array(): array
    {
        dd($this->sales);
        return $this->sales;
    }

    public function view(): View
    {
        return view('exports.sales', [
            'sales' => $this->sales
        ]);
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
