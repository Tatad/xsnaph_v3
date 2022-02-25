<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Sales;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $rows = 0;
    private $parsedPeriodTo;

    public function  __construct($org_id)
    {
        $this->org_id= $org_id;
    }   

    public function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public function model(array $rows)
    {
        ++$this->rows;
        // dd($rows);
        // if($this->rows == 3){
        // }
        if(str_contains($rows[0],'For the period')){
            $fullstring = $rows[0];
            $periodFrom = $this->get_string_between($fullstring, 'For the period ', ' to');

            $prefix = ' to';
            $periodTo = substr($fullstring, strpos($fullstring, "to ") + 3);   
            $parsedPeriodFrom = Carbon::parse($periodFrom)->format('Y-m-d');
            $this->parsedPeriodTo = Carbon::parse($periodTo)->format('Y-m-d');
            
        }

dd($this->parsedPeriodTo);
            

        if($this->rows > 5){
            //dd($rows);
            //dd($rows);
            if($rows[0] != 'Total'){
                return new Sales([
                    // 'org_id' => 1,
                    // 'period_from' => Carbon::now(),
                    // 'period_to' => Carbon::now(),
                    // 'invoice_number' => 'test',
                    // 'invoice_date' => Carbon::now(),
                    // 'source' => 'test',
                    // 'reference' => 'test'
                    'org_id' => $this->org_id,
                    'period_from' => Carbon::now(),
                    'period_to' => Carbon::now(),
                    'invoice_number' => $rows[0],
                    'invoice_date' => Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($rows[1])))->format('Y-m-d'),
                    'source' => $rows[2],
                    'reference' => $rows[3],
                    'item_code' => $rows[4],
                    'description' => $rows[5],
                    'quantity' => $rows[6],
                    'unit_price' => $rows[7],
                    'discount' => $rows[8],
                    'tax' => $rows[9],
                    'tax_rate' => $rows[10],
                    'tax_rate_name' => $rows[11],
                    'gross' => $rows[12],
                    'net' => $rows[13]
                ]);
            }
        }
        // return new Sales([
        //     //
            // 'org_id' => $row[0],
            // 'period_from' => $row[1],
            // 'period_to' => $row[2],
            // 'invoice_number' => $row[3],
            // 'invoice_date' => $row[4],
            // 'source' => $row[5],
            // 'reference' => $row[6],
            // 'item_code' => $row[7],
            // 'description' => $row[8],
            // 'quantity' => $row[9],
            // 'unit_price' => $row[10],
            // 'discount' => $row[11],
            // 'tax' => $row[12],
            // 'tax_rate' => $row[13],
            // 'tax_rate_name' => $row[14],
            // 'gross' => $row[15],
            // 'net' => $row[16]
        // ]);
        

        //dd($rows);
        
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
