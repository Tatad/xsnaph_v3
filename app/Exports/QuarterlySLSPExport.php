<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuarterlySLSPExport implements FromArray, WithMultipleSheets
{
    protected $records;

    public function __construct(array $records, array $purchases,object $org, array $months, array $purchaseMonths, string $year)
    {
        $this->records = $records;
        $this->purchases = $purchases;
        $this->org = $org;
        $this->months = $months;
        $this->purchaseMonths = $purchaseMonths;
        $this->year = $year;
    }

    public function array(): array
    {
        return $this->records;
        return $this->purchases;
        return $this->org;
        return $this->months;
        return $this->purchaseMonths;
        return $this->year;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        //$sheets = ['January', 'February', 'March'];
        //dd($this->sales['January']);

        // foreach($this->records as $key => $data){
        //     dd(new FirstMonthExport($this->records[0], $this->org, $this->months[0], $this->year));
        //     dd($data);
        // }
        $sheetArray = [];
        $firstSale = '';
        if(isset($this->records[0])){
            $firstSale = new SalesQuarterlyExport($this->records[0], $this->org, $this->months[0], $this->year);
            $sheetArray[] = $firstSale;
        }

        $secondSale = '';
        if(isset($this->records[1])){
            $secondSale = new SalesQuarterlyExport($this->records[1], $this->org, $this->months[1], $this->year);
            $sheetArray[] = $secondSale;
        }

        $thirdSale = '';
        if(isset($this->records[2])){
            $thirdSale = new SalesQuarterlyExport($this->records[2], $this->org, $this->months[2], $this->year);
            $sheetArray[] = $thirdSale;
        }

        $firstPurchase = '';
        if(isset($this->purchases[0])){
            $firstPurchase = new PurchasesQuarterlyExport($this->purchases[0], $this->org, $this->purchaseMonths[0], $this->year);
            $sheetArray[] = $firstPurchase;
        }

        $secondPurchase = '';
        if(isset($this->purchases[1])){
            $secondPurchase = new PurchasesQuarterlyExport($this->purchases[1], $this->org, $this->purchaseMonths[1], $this->year);
            $sheetArray[] = $secondPurchase;
        }
        $thirdPurchase = '';
        if(isset($this->purchases[2])){
            $thirdPurchase = new PurchasesQuarterlyExport($this->purchases[2], $this->org, $this->purchaseMonths[2], $this->year);
            $sheetArray[] = $thirdPurchase;
        }

        $sheets = $sheetArray;

        return $sheets;
    }
}
