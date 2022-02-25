<?php

namespace App\Exports;

use App\Models\Sales;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PurchaseExport implements FromView,WithColumnWidths,WithTitle
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
    protected $purchase;

    public function __construct(array $purchase)
    {
        $this->purchase = $purchase;
    }

    public function array(): array
    {
        dd($this->purchase);
        return $this->purchase;
    }

    public function view(): View
    {
        return view('exports.purchases', [
            'purchase' => $this->purchase
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
            'L' => 35,      
            'M' => 35,        
            'N' => 35,         
        ];
    }

    public function title(): string
    {
        return 'Payable Invoice Detail';
    }
}
