<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class Export1601EQ implements FromView,WithColumnWidths,WithTitle
{
    protected $records;

    public function __construct(array $records, object $org,  string $year, string $lastQuarter)
    {
        $this->records = $records;
        $this->org = $org;
        $this->year = $year;
        $this->lastQuarter = $lastQuarter;
    }

    public function array(): array
    {
        return $this->records;
        return $this->org;
        return $this->year;
        return $this->lastQuarter;
    }

    public function view(): View
    {
        //dd($this->records);
        return view('exports.quarterly1601', [
            'records' => $this->records,
            'org' => $this->org,
            'year' => $this->year,
            'lastQuarter' => $this->lastQuarter
        ]);
    }

    public function title(): string
    {
        return '1601 E-Q for '.$this->year;
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
            'O' => 35,     
            'P' => 35,     
            'Q' => 35,    
            'R' => 35,     
            'S' => 35,          
        ];
    }
}
