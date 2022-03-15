<?php

namespace App\Http\Controllers;

use File;
use ZipArchive;
use DB;
use Auth;
use Carbon\Carbon;
use App\Models\Sales;
use App\Models\Purchase;
use App\Imports\SalesImport;
use App\Exports\SalesExport;
use App\Exports\PurchaseExport;
use App\Exports\QuarterlySLSPExport;
use App\Imports\PeriodDates;
use App\Models\Organizations;
use App\Models\Reports2307;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf;
use LangleyFoxall\XeroLaravel\XeroApp;
use League\OAuth2\Client\Token\AccessToken;
use LangleyFoxall\XeroLaravel\OAuth2;
use Barryvdh\DomPDF\Facade as PDF;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getOAuth2()
    {
        // This will use the 'default' app configuration found in your 'config/xero-laravel-lf.php` file.
        // If you wish to use an alternative app configuration you can specify its key (e.g. `new OAuth2('other_app')`).
        return new OAuth2();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getOrganizations(){

        //$xeroOrgs = DB::table('user_organizations')->select('user_organizations.*','organizations.org_id')->leftJoin('organizations', 'organizations.org_id','=','user_organizations.id')->where(['user_id' => auth()->user()->id])->get();
        // return $xeroOrgs;

        $xeroOrgs = DB::table('user_to_organizations')->select('user_to_organizations.*','organizations.org_id', 'user_organizations.*')->leftJoin('organizations', 'organizations.org_id','=','user_to_organizations.org_id')->leftJoin('user_organizations','user_organizations.id','=','user_to_organizations.org_id')->where(['user_to_organizations.user_id' => auth()->user()->id])->get();
        return $xeroOrgs;
    }

    public function dashboard(Request $request, $id){
        $request->session()->forget('xeroOrg');

        $xeroOrg = DB::table('user_organizations')->where(['tenant_id' => $id])->first();
        $request->session()->put('xeroOrg', $xeroOrg);
        $request->session()->put('access_token', $xeroOrg->xero_access_token);

        //return view('dashboard');
        return redirect('/sales-summary');
    }

    public function switchOrg(Request $request){
        $request->session()->forget('xeroOrg');
        $request->session()->forget('access_token');
        return redirect('/home');
    }

    public function purchase(Request $request){
        $this->refreshXeroToken($request);
        return view('purchase');
    }   

    public function sales(Request $request){
        if(collect($request->session()->get('access_token'))->isEmpty() ){
            return redirect('/home');
        }else{
            $this->refreshXeroToken($request);
        }
        return view('dashboard');
    }  

    public function report2307(Request $request){
        if(collect($request->session()->get('access_token'))->isEmpty() ){
            return redirect('/home');
        }else{
            $this->refreshXeroToken($request);
        }
        return view('report2307');
    }    

    public function quarterlySLSPSummary(Request $request){
        if(collect($request->session()->get('access_token'))->isEmpty() ){
            return redirect('/home');
        }else{
            $this->refreshXeroToken($request);
        }
        return view('quarterly-slsp-summary');
    }    

    public function refreshXeroToken(Request $request){
        
        //refresh token if necessary
        $accessToken = new AccessToken(collect(json_decode($request->session()->get('access_token')))->toArray());
        //dd($accessToken->hasExpired());
        if ($accessToken->hasExpired()) {
            $accessTokens = $this->getOAuth2()->refreshAccessToken($accessToken);
            
            $request->session()->forget('access_token');
            $request->session()->put('access_token', json_encode($accessTokens));
            //dd($request->session()->get('access_token'));
            DB::table('user_organizations')->where('tenant_id', $request->session()->get('xeroOrg')->tenant_id)->update([
                'xero_access_token' => json_encode($accessTokens)
            ]);
        }
    }

    public function getRDOCodes(){
        $rdoCodes = DB::table('rdo_codes')->get();
        return $rdoCodes;
    }

    public function saveOrgInfo(Request $request){
        $input = $request->all();
        //dd($input);

        $input = $request->all();
        //dd($input);
        $org = new Organizations;
        $org->tin_number = $input['tinNumber'];
        $org->branch_code = $input['branchCode'];
        $org->trade_name = $input['tradeName'];
        $org->email = $input['email'];
        $org->rdo_code = $input['rdoCode'];
        $org->reporting_cycle = $input['reportingCycle'];
        $org->fiscal_calendar_end = $input['fiscalCalendar'];
        $org->sub_street = $input['subStreet'];
        $org->street = $input['street'];
        $org->barangay = $input['barangay'];
        $org->city = $input['city'];
        $org->province = $input['province'];
        $org->classification = $input['classification'];
        $org->first_name = ($input['firstName']) ? $input['firstName'] : '';
        $org->last_name = ($input['lastName']) ? $input['lastName'] : '';
        $org->middle_name = ($input['middleName']) ? $input['middleName'] : '';
        $org->zip_code = $input['zipCode'];
        $org->org_id = $input['orgId'];
        $org->tenant_id = $input['tenantId'];
        $org->save();

        // DB::table('user_to_organizations')->insert([
        //     'user_id' => auth()->user()->id,
        //     'org_id' => $input['orgId'],
        //     'role' => 'owner'
        // ]);

        return 'success';
    }

    public function uploadExcel(Request $request){
        $input = $request->all();
        //dd($input);
        $results = \Excel::toArray(new PeriodDates, $input['excel-file']);
        $getSalesBatchNumber = DB::table('sales')->where('org_id', $input['org_id'])->orderBy('created_at', 'desc')->first();
        // dd(((collect($getSalesBatchNumber)->isEmpty()) ? 1 : ($getSalesBatchNumber->batch_number+1)));
        // dd($getSalesBatchNumber);
        // if(str_contains($results[0][2][0],'For the period')){
        //     $fullstring = $results[0][2][0];
        //     $periodFrom = $this->get_string_between($fullstring, 'For the period ', ' to');

        //     $prefix = ' to';
        //     $periodTo = substr($fullstring, strpos($fullstring, "to ") + 3);   
        //     $parsedPeriodFrom = Carbon::parse($periodFrom)->format('Y-m-d');
        //     $parsedPeriodTo = Carbon::parse($periodTo)->format('Y-m-d');
            
        // }
        $year = $input['datepicker'];
        $now = Carbon::now();
        $fromQuarter = $now->lastOfQuarter();

        switch ($input['quarter']) {
            case 1:
                $now = new Carbon('first day of January '.$year, 'Asia/Manila');
                break;
            case 2:
                $now = new Carbon('first day of April '.$year, 'Asia/Manila');
                break;
            case 3:
                $now = new Carbon('first day of July '.$year, 'Asia/Manila');
                break;
            default:
                $now = new Carbon('first day of October '.$year, 'Asia/Manila');
        }
        $fromQuarter = Carbon::parse($now)->firstOfQuarter()->toDateTimeString();
        $lastQuarter = Carbon::parse($now)->lastOfQuarter()->toDateTimeString();
        
        $getRecords = DB::table('sales')->where('org_id', '=', $input['org_id'])->whereBetween('invoice_date', [$fromQuarter,$lastQuarter])->get();

        if(collect($getRecords)->isNotEmpty()){
            return redirect('/sales-summary')->with('status','You have an existing record for that period.');
        }

        //dd($getRecords);
        //dd($results[0]);

        if($results[0][0][0] !== 'Receivable Invoice Detail'){
            return redirect('/sales-summary')->with('status','Error encountered you are uploading the wrong file.');;
        }
        foreach($results[0] as $key => $rows){
            // dd($rows[0]);
            // if($rows[0] !== 'Receivable Invoice Detail'){
            //     return redirect('/sales-summary')->with('status','Error encountered you are uploading the wrong file.');;
            // }
            if ($key < 5) continue;
            if($rows[0] != 'Total' && collect($rows[0])->isNotEmpty()){
                Sales::create([
                    'batch_number' => ((collect($getSalesBatchNumber)->isEmpty()) ? 1 : ($getSalesBatchNumber->batch_number+1)),
                    'org_id' => $input['org_id'],
                    'period_from' => $fromQuarter,
                    'period_to' => $lastQuarter,
                    'contact_name' => $rows[0],
                    'invoice_number' => $rows[1],
                    'invoice_date' => ((is_float($rows[2]) || is_int($rows[2])) ? Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($rows[2])))->format('Y-m-d') : Carbon::parse($rows[2])->format('Y-m-d') ),
                    'source' => $rows[3],
                    'reference' => $rows[4],
                    'description' => $rows[5],
                    'tax' => (floatval(str_replace( ',', '', $rows[6] ))),
                    'tax_rate' => $rows[7],
                    'tax_rate_name' => $rows[8],
                    'gross' => (floatval(str_replace( ',', '', $rows[9] ))),
                    'net' => (floatval(str_replace( ',', '', $rows[10] ))),
                    'status' => $rows[11]
                ]);
            }
        }
        return redirect('/sales-summary')->with('success_status','You have successfully uploaded the record.');
        //return redirect('/select-organization/'.$input['tenant_id'])->with('success_status','You have successfully uploaded the record.');
    }

    public function upload2307Report(Request $request){
        $input = $request->all();
        //dd($input);
        $results = \Excel::toArray(new PeriodDates, $input['excel-file']);
        $getSalesBatchNumber = DB::table('reports_2307')->where('org_id', $input['org_id'])->orderBy('created_at', 'desc')->first();
        // dd(((collect($getSalesBatchNumber)->isEmpty()) ? 1 : ($getSalesBatchNumber->batch_number+1)));
        // dd($getSalesBatchNumber);
        if(str_contains($results[0][2][0],'For the period')){
            $fullstring = $results[0][2][0];
            $periodFrom = $this->get_string_between($fullstring, 'For the period ', ' to');

            $prefix = ' to';
            $periodTo = substr($fullstring, strpos($fullstring, "to ") + 3);   
            $parsedPeriodFrom = Carbon::parse($periodFrom)->format('Y-m-d');
            $parsedPeriodTo = Carbon::parse($periodTo)->format('Y-m-d');
            
        }
        //dd($parsedPeriodTo);
        foreach($results[0] as $key => $rows){
            if ($key < 7) continue;
            if($rows[0] != 'Total' && collect($rows[0])->isNotEmpty()){
                //dd(Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($rows[2])))->format('Y-m-d'));
                Reports2307::create([
                    'batch_number' => ((collect($getSalesBatchNumber)->isEmpty()) ? 1 : ($getSalesBatchNumber->batch_number+1)),
                    'org_id' => $input['org_id'],
                    'period_from' => $parsedPeriodFrom,
                    'period_to' => $parsedPeriodTo,
                    'invoice_date' => ((is_float($rows[2]) || is_int($rows[2])) ? Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($rows[0])))->format('Y-m-d') : Carbon::parse($rows[0])->format('Y-m-d') ),
                    'contact_name' => $rows[1],
                    'source' => $rows[2],
                    'reference' => $rows[3],
                    'item_code' => $rows[4],
                    'description' => $rows[5],
                    'quantity' => (floatval(str_replace( ',', '', $rows[6] ))),
                    'unit_price' => abs($rows[7]),
                    'gross' => (floatval(str_replace( ',', '', $rows[8] ))),
                    'account' => $rows[9],
                    'account_code' => $rows[10]
                ]);
            }
        }
        return redirect('/reports-2307-summary');
    }

    public function uploadPurchases(Request $request){
        $input = $request->all();

        $results = \Excel::toArray(new PeriodDates, $input['excel-file']);
        $getPurchaseBatchNumber = DB::table('purchases')->where('org_id', $input['org_id'])->orderBy('created_at', 'desc')->first();

        $year = $input['datepicker'];
        $now = Carbon::now();
        $fromQuarter = $now->lastOfQuarter();

        switch ($input['quarter']) {
            case 1:
                $now = new Carbon('first day of January '.$year, 'Asia/Manila');
                break;
            case 2:
                $now = new Carbon('first day of April '.$year, 'Asia/Manila');
                break;
            case 3:
                $now = new Carbon('first day of July '.$year, 'Asia/Manila');
                break;
            default:
                $now = new Carbon('first day of October '.$year, 'Asia/Manila');
        }
        $fromQuarter = Carbon::parse($now)->firstOfQuarter()->toDateTimeString();
        $lastQuarter = Carbon::parse($now)->lastOfQuarter()->toDateTimeString();
        
        $getRecords = DB::table('purchases')->where('org_id', '=', $input['org_id'])->whereBetween('invoice_date', [$fromQuarter,$lastQuarter])->get();
        //dd($getRecords);
        if(collect($getRecords)->isNotEmpty()){
            return redirect('/purchases-summary')->with('status','You have an existing record for that period.');
        }
        
        // if(str_contains($results[0][2][0],'For the period')){
        //     $fullstring = $results[0][2][0];
        //     $periodFrom = $this->get_string_between($fullstring, 'For the period ', ' to');

        //     $prefix = ' to';
        //     $periodTo = substr($fullstring, strpos($fullstring, "to ") + 3);   
        //     $parsedPeriodFrom = Carbon::parse($periodFrom)->format('Y-m-d');
        //     $parsedPeriodTo = Carbon::parse($periodTo)->format('Y-m-d');
            
        // }
        //dd($results[0][0][0]);
        //dd($results[0][0][0] );
        if($results[0][0][0] !== 'Payable Invoice Detail'){
            return redirect('/purchases-summary')->with('status','Error encountered you are uploading the wrong file.');;
        }
        foreach($results[0] as $key => $rows){
            //dd($rows);
            if ($key < 5) continue;
            if($rows[0] != 'Total' && collect($rows[0])->isNotEmpty()){
                //dd(Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($rows[2])))->format('Y-m-d'));
                Purchase::create([
                    'batch_number' => ((collect($getPurchaseBatchNumber)->isEmpty()) ? 1 : ($getPurchaseBatchNumber->batch_number+1)),
                    'org_id' => $input['org_id'],
                    'period_from' => $fromQuarter,
                    'period_to' => $lastQuarter,
                    'contact_name' => $rows[0],
                    'invoice_date' =>((is_float($rows[1]) || is_int($rows[1])) ? Carbon::parse(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($rows[1])))->format('Y-m-d') : Carbon::parse($rows[1])->format('Y-m-d') ),
                    'source' => $rows[2],
                    'reference' => $rows[3],
                    'description' => $rows[4],
                    'tax_rate' => $rows[5],
                    'tax_rate_name' => $rows[6],
                    'tax' => (floatval(str_replace( ',', '', $rows[7] ))),
                    'net' => (floatval(str_replace( ',', '', $rows[8] ))),
                    'gross' => (floatval(str_replace( ',', '', $rows[9] ))),
                    'status' => $rows[10]
                ]);
            }
        }
        return redirect('/purchases-summary')->with('success_status','You have successfully uploaded the record.');
    }

    public function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public function getSalesRecords(Request $request){ 
        $salesRecords = Sales::where('org_id', $request->session()->get('xeroOrg')->id)->get();
        $records = [];
        foreach($salesRecords as $key => $record){
            $records[$record['batch_number']]['id'] = $record['batch_number'];
            $records[$record['batch_number']]['created_at'] = $record['created_at'];
            $records[$record['batch_number']]['period_from'] = $record['period_from'];
            $records[$record['batch_number']]['period_to'] = $record['period_to'];
            $records[$record['batch_number']]['data'][] = $record;
        }
        return collect($records)->values()->toArray();
    }

    public function get2307Records(Request $request){ 
        $records2307 = Reports2307::where('org_id', $request->session()->get('xeroOrg')->id)->get();
        $records = [];
        foreach($records2307 as $key => $record){
            $records[$record['batch_number']]['id'] = $record['batch_number'];
            $records[$record['batch_number']]['created_at'] = $record['created_at'];
            $records[$record['batch_number']]['period_from'] = $record['period_from'];
            $records[$record['batch_number']]['period_to'] = $record['period_to'];
            $records[$record['batch_number']]['data'][] = $record;
        }
        return collect($records)->values()->toArray();
    }

    public function getPurchasesRecords(Request $request){ 
        $purchaseRecords = Purchase::where('org_id', $request->session()->get('xeroOrg')->id)->get();
        $records = [];
        foreach($purchaseRecords as $key => $record){
            $records[$record['batch_number']]['id'] = $record['batch_number'];
            $records[$record['batch_number']]['created_at'] = $record['created_at'];
            $records[$record['batch_number']]['period_from'] = $record['period_from'];
            $records[$record['batch_number']]['period_to'] = $record['period_to'];
            $records[$record['batch_number']]['data'][] = $record;
        }
        return collect($records)->values()->toArray();
    }

    public function downloadSales(Request $request, $id){
        //dd($request->session()->get('access_token'));
        $this->refreshXeroToken($request);
        //return
        $sales = Sales::where('batch_number', $id)->get();
        $orgInfo = DB::table('organizations')->where('org_id',$request->session()->get('xeroOrg')->id)->first();
        //dd($orgInfo);
        $xero = new XeroApp(
            new AccessToken(collect(json_decode($request->session()->get('access_token')))->toArray() ),
            $request->session()->get('xeroOrg')->tenant_id
        );

        $contacts = $xero->contacts()->get();
        $records = [];
        $grossGrandTotal = 0;
        $taxTotal = 0;
        $zeroRatedGrandTotal = 0;
        $taxExemptGrandTotal = 0;
        $grandTotalNet = 0;
        foreach($sales as $sale){
            $contactInfo = collect($contacts)->where('Name', $sale['contact_name'])->first();
            if(collect($contactInfo)->isEmpty()){
                return redirect('/sales-summary')->with('status','Error: '.$sale['contact_name'].' Contact not found.');
            }else{
                //dd($contactInfo['TaxNumber']);
                $sale['tin_number'] = ($contactInfo['TaxNumber']) ? (strpos($contactInfo['TaxNumber'], '-') == false ) ? $this->hyphenate(str_pad($contactInfo['TaxNumber'],9,"0")) : $contactInfo['TaxNumber'] : '--';
                $sale['contact_name'] = $contactInfo['Name'];
                $sale['first_name'] = $contactInfo['FirstName'];
                $sale['last_name'] = $contactInfo['LastName'];
                $sale['taxable_month'] = Carbon::parse($sale['invoice_date'])->endOfMonth()->format('d F Y');

                if(strpos($sale['tax_rate_name'], 'Tax Exempt Sales') == false){
                    $grandTotalNet += $sale['net']; 
                }

                if(strpos($sale['tax_rate_name'], 'Tax Exempt Sales') !== false){
                    $taxExemptGrandTotal += $sale['net']; 
                }

                if(strpos($sale['tax_rate_name'], 'Zero Rated Sales') !== false){
                    $zeroRatedGrandTotal += $sale['net']; 
                }

                if($contactInfo['Addresses'][0]){
                    $sale['address'] = $contactInfo['Addresses'][0]['AddressLine1'].' '.$contactInfo['Addresses'][0]['City'].' '.$contactInfo['Addresses'][0]['Region'].' '.$contactInfo['Addresses'][0]['PostalCode'];
                }

                $taxTotal += $sale['tax']; 

                $grossGrandTotal += $sale['gross']; 

                $records[] = $sale;
            }
        }

        $data['name'] = $xero->organisations()->first()->Name;
        $data['trade_name'] = $xero->organisations()->first()->LegalName;
        $data['tin_number'] = $xero->organisations()->first()->TaxNumber;
        $data['records'] = $records;
        $data['grossGrandTotal'] = $grossGrandTotal;
        $data['taxTotal'] = $taxTotal;
        $data['zeroRatedGrandTotal'] = $zeroRatedGrandTotal;
        $data['taxExemptGrandTotal'] = $taxExemptGrandTotal;
        $data['grandTotalNet'] = $grandTotalNet;

            //dd($data);

        if(collect($orgInfo)->isNotEmpty()){
            $data['address'] = $orgInfo->street.' '.$orgInfo->barangay.' '.$orgInfo->city.' '.$orgInfo->province.' '.$orgInfo->zip_code;
        }

        //return \Excel::download(new SalesExport($sales), 'sales.xlsx');
        $export = new SalesExport(collect($data)->toArray(),$orgInfo);
        return \Excel::download($export, 'sales.xlsx');
    }

    public function hyphenate($str) {
        return implode("-", str_split($str, 3));
    }

    public function downloadPurchase(Request $request, $id){
        //dd($request->session()->get('access_token'));
        $this->refreshXeroToken($request);
        //return
        $purchases = Purchase::where('batch_number', $id)->get();
        $orgInfo = DB::table('organizations')->where('org_id',$request->session()->get('xeroOrg')->id)->first();
        //dd($orgInfo);
        $xero = new XeroApp(
            new AccessToken(collect(json_decode($request->session()->get('access_token')))->toArray() ),
            $request->session()->get('xeroOrg')->tenant_id
        );

        $contacts = $xero->contacts()->get();
        $records = [];
        $grossGrandTotal = 0;
        $taxTotal = 0;
        $zeroRatedGrandTotal = 0;
        $taxExemptGrandTotal = 0;
        $grandTotalNet = 0;
        $purchaseGoodsOtherThanCapitalGoodsTotal = 0;
        $vatOnPurchaseCapitalGoodsTotal = 0;
        $vatOnPurchaseServicesTotal = 0;
        $grandTotalTaxablePurchase = 0;

        foreach($purchases as $purchase){
            $contactInfo = collect($contacts)->where('Name', $purchase['contact_name'])->first();
            if(collect($contactInfo)->isEmpty()){
                return redirect('/sales-summary')->with('status','Error: '.$sale['contact_name'].' Contact not found.');
            }else{
                $purchase['tin_number'] = ($contactInfo['TaxNumber']) ? (strpos($contactInfo['TaxNumber'], '-') == false ) ? $this->hyphenate(str_pad($contactInfo['TaxNumber'],9,"0")) : $contactInfo['TaxNumber'] : '--';
                $purchase['contact_name'] = $contactInfo['Name'];
                $purchase['first_name'] = $contactInfo['FirstName'];
                $purchase['last_name'] = $contactInfo['LastName'];
                $purchase['taxable_month'] = Carbon::parse($purchase['invoice_date'])->endOfMonth()->format('d F Y');

                if(strpos($purchase['tax_rate_name'], 'Tax on Purchases') == false){
                    $grandTotalNet += $purchase['net']; 
                }

                if(strpos($purchase['tax_rate_name'], 'Tax on Purchases') !== false){
                    $grandTotalTaxablePurchase += $purchase['net']; 
                }

                if(strpos($purchase['tax_rate_name'], 'Tax Exempt') !== false){
                    $taxExemptGrandTotal += $purchase['net']; 
                }

                if(strpos($purchase['tax_rate_name'], 'Zero Rated') !== false){
                    $zeroRatedGrandTotal += $purchase['net']; 
                }

                if(strpos($purchase['tax_rate_name'], 'VAT on Purchases (Goods)') !== false){
                    $purchaseGoodsOtherThanCapitalGoodsTotal += $purchase['net']; 
                }

                if(strpos($purchase['tax_rate_name'], 'VAT on Purchases (Capital Goods)') !== false){
                    $vatOnPurchaseCapitalGoodsTotal += $purchase['net']; 
                }

                if(strpos($purchase['tax_rate_name'], 'VAT on Purchases (Services)') !== false){
                    $vatOnPurchaseServicesTotal += $purchase['net']; 
                }

                if($contactInfo['Addresses'][0]){
                    $purchase['address'] = $contactInfo['Addresses'][0]['AddressLine1'].' '.$contactInfo['Addresses'][0]['City'].' '.$contactInfo['Addresses'][0]['Region'].' '.$contactInfo['Addresses'][0]['PostalCode'];
                }

                $taxTotal += $purchase['tax']; 

                $grossGrandTotal += $purchase['gross']; 

                $records[] = $purchase;
            }
        }

        $data['name'] = $xero->organisations()->first()->Name;
        $data['trade_name'] = $xero->organisations()->first()->LegalName;
        $data['tin_number'] = $xero->organisations()->first()->TaxNumber;
        $data['records'] = $records;
        $data['grossGrandTotal'] = $grossGrandTotal;
        $data['taxTotal'] = $taxTotal;
        $data['zeroRatedGrandTotal'] = $zeroRatedGrandTotal;
        $data['taxExemptGrandTotal'] = $taxExemptGrandTotal;
        $data['grandTotalNet'] = $grandTotalNet;
        $data['purchaseGoodsOtherThanCapitalGoodsTotal'] = $purchaseGoodsOtherThanCapitalGoodsTotal;
        $data['vatOnPurchaseCapitalGoodsTotal'] = $vatOnPurchaseCapitalGoodsTotal;
        $data['vatOnPurchaseServicesTotal'] = $vatOnPurchaseServicesTotal;
        $data['grandTotalTaxablePurchase'] = $grandTotalTaxablePurchase;


        if(collect($orgInfo)->isNotEmpty()){
            $data['address'] = $orgInfo->street.' '.$orgInfo->barangay.' '.$orgInfo->city.' '.$orgInfo->province.' '.$orgInfo->zip_code;
        }

            //dd($data);


        //return \Excel::download(new SalesExport($sales), 'sales.xlsx');
        $export = new PurchaseExport(collect($data)->toArray(),$orgInfo);
        return \Excel::download($export, 'purchases.xlsx');
    }

    public function removeSalesRecord(Request $request){
        $input = $request->all();
        DB::table('sales')->delete($input['id']);
        return 'success';
    }

    public function removePurchaseRecord(Request $request){
        $input = $request->all();
        DB::table('puchases')->delete($input['id']);
        return 'success';
    }

    public function deletePurchases(Request $request){
        $input = $request->all();

        $records = DB::table('purchases')->whereIn('id',$input)->delete();
        return 'success';
    }

    public function deleteSalesRecords(Request $request){
        $input = $request->all();

        $records = DB::table('sales')->whereIn('id',$input)->delete();
        return 'success';
    }

    public function delete2307Records(Request $request){
        $input = $request->all();

        $records = DB::table('reports_2307')->whereIn('id',$input)->delete();
        return 'success';
    }
    
    public function remove2307Record(Request $request){
        $input = $request->all();
        DB::table('reports_2307')->delete($input['id']);
        return 'success';
    }

    public function getQuarterlySLSPSummary(Request $request){
        $year = '2022';
        $now = Carbon::now();
        $fromQuarter = $now->lastOfQuarter();

        $input['quarter'] = 1;

        switch ($input['quarter']) {
            case 1:
                $now = new Carbon('first day of January '.$year, 'Asia/Manila');
                break;
            case 2:
                $now = new Carbon('first day of April '.$year, 'Asia/Manila');
                break;
            case 3:
                $now = new Carbon('first day of July '.$year, 'Asia/Manila');
                break;
            default:
                $now = new Carbon('first day of October '.$year, 'Asia/Manila');
        }
        $fromQuarter = Carbon::parse($now)->firstOfQuarter()->toDateTimeString();
        $lastQuarter = Carbon::parse($now)->lastOfQuarter()->toDateTimeString();

        $salesRecords = DB::table('sales')->select('sales.*',  DB::raw('YEAR(invoice_date) year, MONTH(invoice_date) month, MONTHNAME(invoice_date) month_name'))->whereBetween('invoice_date',[$fromQuarter,$lastQuarter])->get();

        $purchasesRecords = DB::table('purchases')->select('purchases.*',  DB::raw('YEAR(invoice_date) year, MONTH(invoice_date) month, MONTHNAME(invoice_date) month_name'))->whereBetween('invoice_date',[$fromQuarter,$lastQuarter])->get();

        //dd(collect($purchasesRecords)->sortBy('month')->groupBy('month_name')->toArray());
        return ['salesRecords' => collect($salesRecords)->sortBy('month')->groupBy('month_name')->toArray(), 'purchasesRecords' => collect($purchasesRecords)->sortBy('month')->groupBy('month_name')->toArray()];

    }

    public function downloadQuarterlySLSPSummary(Request $request){
        $input = $request->all();
        //dd($input);
        $this->refreshXeroToken($request);
        //$year = '2022';
        $year = $input['year'];
        $now = Carbon::now();
        $fromQuarter = $now->lastOfQuarter();

        //$input['quarter'] = 1;

        switch ($input['quarter']) {
            case 1:
                $now = new Carbon('first day of January '.$year, 'Asia/Manila');
                break;
            case 2:
                $now = new Carbon('first day of April '.$year, 'Asia/Manila');
                break;
            case 3:
                $now = new Carbon('first day of July '.$year, 'Asia/Manila');
                break;
            default:
                $now = new Carbon('first day of October '.$year, 'Asia/Manila');
        }
        $fromQuarter = Carbon::parse($now)->firstOfQuarter()->toDateTimeString();
        $lastQuarter = Carbon::parse($now)->lastOfQuarter()->toDateTimeString();

        $salesRecords = DB::table('sales')->select('sales.*',  DB::raw('YEAR(invoice_date) year, MONTH(invoice_date) month, MONTHNAME(invoice_date) month_name'))->whereBetween('invoice_date',[$fromQuarter,$lastQuarter])->get();

        $purchasesRecords = DB::table('purchases')->select('purchases.*',  DB::raw('YEAR(invoice_date) year, MONTH(invoice_date) month, MONTHNAME(invoice_date) month_name'))->whereBetween('invoice_date',[$fromQuarter,$lastQuarter])->get();

        //dd(collect($purchasesRecords)->sortBy('month')->groupBy('month_name')->toArray());
        $orgInfo = DB::table('organizations')->where('org_id',$request->session()->get('xeroOrg')->id)->first();

        if(collect($orgInfo)->isNotEmpty()){
            $orgInfo->address = $orgInfo->street.' '.$orgInfo->barangay.' '.$orgInfo->city.' '.$orgInfo->province.' '.$orgInfo->zip_code;
        }
        //$salesRecords = collect($salesRecords)->sortBy('month')->groupBy('month_name')->values()->toArray();

        $xero = new XeroApp(
            new AccessToken(collect(json_decode($request->session()->get('access_token')))->toArray() ),
            $request->session()->get('xeroOrg')->tenant_id
        );

        $contacts = $xero->contacts()->get();

        $salesRecordsResults = [];
        $purchasesRecordsResults = [];
        $grossGrandTotalSales = 0;
        $taxTotalSales = 0;
        $zeroRatedGrandTotalSales = 0;
        $taxExemptGrandTotalSales = 0;
        $grandTotalNetSales = 0;

        $grossGrandTotalPurchase = 0;
        $taxTotalPurchase = 0;
        $zeroRatedGrandTotalPurchase = 0;
        $taxExemptGrandTotalPurchase = 0;
        $grandTotalNetPurchase = 0;
        $grandTotalTaxablePurchase = 0;

        $salesMonths = [];
        $purchaseMonths = [];
        $salesRecordConverted = collect($salesRecords)->values()->toArray();
        foreach(($salesRecordConverted) as $key => $data){
            $contactInfo = collect($contacts)->where('Name', $data->contact_name)->first();
            $data->tin_number = ($contactInfo->TaxNumber) ? (strpos($contactInfo->TaxNumber, '-') == false ) ? $this->hyphenate(str_pad($contactInfo->TaxNumber,9,"0")) : $contactInfo->TaxNumber : '--';
            
            $data->contact_name = $contactInfo->Name;
            $data->first_name = $contactInfo->FirstName;
            $data->last_name = $contactInfo->LastName;

            $data->taxable_month = Carbon::parse($data->invoice_date)->endOfMonth()->format('d F Y');

            if(strpos($data->tax_rate_name, 'Tax Exempt') == false){
                $grandTotalNetSales += $data->net; 
            }

            if(strpos($data->tax_rate_name, 'Tax Exempt') !== false){
                $taxExemptGrandTotalSales += $data->net; 
            }

            if(strpos($data->tax_rate_name, 'Zero Rated') !== false){
                $zeroRatedGrandTotalSales += $data->net; 
            }

            if($contactInfo['Addresses'][0]){
                $data->address = $contactInfo['Addresses'][0]['AddressLine1'].' '.$contactInfo['Addresses'][0]['City'].' '.$contactInfo['Addresses'][0]['Region'].' '.$contactInfo['Addresses'][0]['PostalCode'];
            }

            $taxTotalSales += $data->tax; 

            $grossGrandTotalSales += $data->gross; 

            $salesRecordsResults[] = $data;
            $salesMonths[] = $data->month;
        }

        $purchaseRecordConverted = collect($purchasesRecords)->values()->toArray();
        foreach(($purchaseRecordConverted) as $key => $data){
            $contactInfo = collect($contacts)->where('Name', $data->contact_name)->first();
            //dd($contactInfo['Addresses'][0]);
            $data->tin_number = ($contactInfo->TaxNumber) ? (strpos($contactInfo->TaxNumber, '-') == false ) ? $this->hyphenate(str_pad($contactInfo->TaxNumber,9,"0")) : $contactInfo->TaxNumber : '--';
            
            $data->contact_name = $contactInfo->Name;
            $data->first_name = $contactInfo->FirstName;
            $data->last_name = $contactInfo->LastName;

            $data->taxable_month = Carbon::parse($data->invoice_date)->endOfMonth()->format('d F Y');

            if(strpos($data->tax_rate_name, 'Tax Exempt') == false){
                $grandTotalNetPurchase += $data->net; 
            }

            if(strpos($data->tax_rate_name, 'Tax Exempt') !== false){
                $taxExemptGrandTotalPurchase += $data->net; 
            }

            if(strpos($data->tax_rate_name, 'Zero Rated') !== false){
                $zeroRatedGrandTotalPurchase += $data->net; 
            }

            if($contactInfo['Addresses'][0]){
                $data->address = $contactInfo['Addresses'][0]['AddressLine1'].' '.$contactInfo['Addresses'][0]['City'].' '.$contactInfo['Addresses'][0]['Region'].' '.$contactInfo['Addresses'][0]['PostalCode'];
            }

            $taxTotalPurchase += $data->tax; 

            $grossGrandTotalPurchase += $data->gross; 

            $purchasesRecordsResults[] = $data;
            $purchaseMonths[] = $data->month;
        }

        $sales = collect($salesRecordsResults)->sortBy('month')->groupBy('month_name')->values()->toArray();
        $purchases = collect($purchasesRecordsResults)->sortBy('month')->groupBy('month_name')->values()->toArray();

        $salesMontResults = collect($salesMonths)->unique()->sort()->map(function ($item){
            $dateObj   =  \DateTime::createFromFormat('!m', $item);
            return $dateObj->format('F');
        })->values()->toArray();

        $purchaseMontResults = collect($purchaseMonths)->unique()->sort()->map(function ($item){
            $dateObj   =  \DateTime::createFromFormat('!m', $item);
            return $dateObj->format('F');
        })->values()->toArray();
        
        //$export = new QuarterlySLSPExport($purchases, $orgInfo, $purchaseMontResults);
        $export = new QuarterlySLSPExport($sales, $purchases, $orgInfo, $salesMontResults, $purchaseMontResults, $year);
        return \Excel::download($export, 'sales.xlsx');
    }

    public function generate2307(Request $request, $id){
        $this->refreshXeroToken($request);
        $xero = new XeroApp(
            new AccessToken(collect(json_decode($request->session()->get('access_token')))->toArray() ),
            $request->session()->get('xeroOrg')->tenant_id
        );
        $contacts = $xero->contacts()->get();
        $record = Reports2307::where('id', $id)->first();
        //dd($records);
        $orgInfo = DB::table('organizations')->where('org_id',$request->session()->get('xeroOrg')->id)->first();
        $pdf = new Fpdi();
        $pagecount = $pdf->setSourceFile(public_path().'/files/form-2307.pdf');  

        // import page 1  
        $tpl = $pdf->importPage(1);
        $pdf->AddPage();
        // Use the imported page as the template
        $size['width'] = 300;
        //$pdf->useTemplate($tpl, null, null, $size['width'], null, true);
        $pdf->useTemplate($tpl, null, null, null, null, true);

        // Set the default font to use
        $pdf->SetFont('Helvetica');
        $pdf->SetFontSize('13'); 

        /////////////////////////Period Date//////////////////////////////////////////////
        //dd($records[0]['period_from']);
        $periodDate = Carbon::parse($record['period_from'])->format('m-d-Y');

        $explodedPeriodDate = explode('-', $periodDate);

        foreach ($explodedPeriodDate as $key => $data) {
            $periodDateResult[] = str_split(($data));
        }
        // Date Month
        $pdf->SetXY(54, 35.5);
        $pdf->Cell(0, 10, $periodDateResult[0][0], 0, 0, 'L'); 

        $pdf->SetXY(58, 35.5); 
        $pdf->Cell(0, 10, $periodDateResult[0][1], 0, 0, 'L'); 

        // Date Day
        $pdf->SetXY(63, 35.5);
        $pdf->Cell(0, 10, $periodDateResult[1][0], 0, 1, 'L'); 

        $pdf->SetXY(67, 35.5); 
        $pdf->Cell(0, 10, $periodDateResult[1][1], 0, 1, 'L'); 

        // Date Year
        $pdf->SetXY(72, 35.5); 
        $pdf->Cell(0, 10, $periodDateResult[2][0], 0, 1, 'L'); 
        $pdf->SetXY(76.2, 35.5); 
        $pdf->Cell(0, 10, $periodDateResult[2][1], 0, 1, 'L'); 
        $pdf->SetXY(81, 35.5); 
        $pdf->Cell(0, 10, $periodDateResult[2][2], 0, 1, 'L'); 
        $pdf->SetXY(85.5, 35.5); 
        $pdf->Cell(0, 10, $periodDateResult[2][3], 0, 1, 'L'); 
        /////////////////////////END Period Date//////////////////////////////////////////////

        /////////////////////////Due Date//////////////////////////////////////////////
        $dueDate = Carbon::parse($record['period_to'])->format('m-d-Y');

        $explodedDueDate = explode('-', $dueDate);

        foreach ($explodedDueDate as $key => $data) {
            $dueDateResult[] = str_split(($data));
        }
        // Date Month
        $pdf->SetXY(141, 35.5);
        $pdf->Cell(0, 10, $dueDateResult[0][0], 0, 0, 'L'); 

        $pdf->SetXY(145, 35.5); 
        $pdf->Cell(0, 10, $dueDateResult[0][1], 0, 0, 'L'); 

        // Date Day
        $pdf->SetXY(150, 35.5);
        $pdf->Cell(0, 10, $dueDateResult[1][0], 0, 1, 'L'); 

        $pdf->SetXY(155, 35.5); 
        $pdf->Cell(0, 10, $dueDateResult[1][1], 0, 1, 'L'); 

        // Date Year
        $pdf->SetXY(160, 35.5); 
        $pdf->Cell(0, 10, $dueDateResult[2][0], 0, 1, 'L'); 
        $pdf->SetXY(164.5, 35.5); 
        $pdf->Cell(0, 10, $dueDateResult[2][1], 0, 1, 'L'); 
        $pdf->SetXY(169.5, 35.5); 
        $pdf->Cell(0, 10, $dueDateResult[2][2], 0, 1, 'L'); 
        $pdf->SetXY(174, 35.5); 
        $pdf->Cell(0, 10, $dueDateResult[2][3], 0, 1, 'L'); 
        /////////////////////////END Due Date/////////////////////////////////////////////////

        /////////////////////////PAYEE INFO//////////////////////////////////////////////////
        //$contactInfo = $xero->getContact($this->xeroTenantId, $result['data']['journal']['paymentData']['Contact']['ContactID']);
        $collectedContactInfo = $orgInfo;
        //dd($collectedContactInfo);

        $payeeInfo = $collectedContactInfo;
        if(isset($collectedContactInfo->tin_number) && !empty($collectedContactInfo->tin_number)){
            $taxNumber = $collectedContactInfo->tin_number;

            $explodedTaxNumber = explode('-', $taxNumber);
            foreach ($explodedTaxNumber as $key => $data) {
                $taxNumberResult[] = str_split(($data));
            }
            $pdf->SetXY(73, 46.3);
            $pdf->Cell(0, 10, $taxNumberResult[0][0], 0, 0, 'L'); 

            $pdf->SetXY(78, 46.3); 
            $pdf->Cell(0, 10, $taxNumberResult[0][1], 0, 0, 'L'); 
            $pdf->SetXY(83, 46.3);
            $pdf->Cell(0, 10, $taxNumberResult[0][2], 0, 1, 'L'); 
            $pdf->SetXY(91, 46.3); 
            $pdf->Cell(0, 10, $taxNumberResult[1][0], 0, 1, 'L'); 
            $pdf->SetXY(95, 46.3); 
            $pdf->Cell(0, 10, $taxNumberResult[1][1], 0, 1, 'L'); 
            $pdf->SetXY(100, 46.3); 
            $pdf->Cell(0, 10, $taxNumberResult[1][2], 0, 1, 'L'); 
            $pdf->SetXY(109, 46.3); 
            $pdf->Cell(0, 10, $taxNumberResult[2][0], 0, 1, 'L'); 
            $pdf->SetXY(114, 46.3); 
            $pdf->Cell(0, 10, $taxNumberResult[2][1], 0, 1, 'L'); 
            $pdf->SetXY(119, 46.3); 
            $pdf->Cell(0, 10, $taxNumberResult[2][2], 0, 1, 'L'); 
            $pdf->SetXY(128, 46.3); 
            $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
            $pdf->SetXY(133, 46.3); 
            $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
            $pdf->SetXY(138, 46.3); 
            $pdf->Cell(0, 10, '0', 0, 1, 'L'); 

        }
        $pdf->SetXY(14, 56.3);
        $pdf->Cell(0, 10, $payeeInfo->trade_name, 0, 0, 'L'); 

        //if(isset($payeeInfo['Addresses'][0]) && !empty($payeeInfo['Addresses']) && !empty($payeeInfo['Addresses'][0]['AddressLine1']) ){
            $pdf->SetXY(14, 66.3);
            $pdf->Cell(0, 10, $payeeInfo->street.', '.$payeeInfo->barangay.', '.$payeeInfo->city.', '.$payeeInfo->province, 0, 0, 'L'); 

            $zipResult = str_split(($payeeInfo->zip_code));

            $pdf->SetXY(191, 66.3);
            $pdf->Cell(0, 10, $zipResult[0], 0, 0, 'L'); 
            $pdf->SetXY(195.5, 66.3);
            $pdf->Cell(0, 10, $zipResult[1], 0, 0, 'L'); 
            $pdf->SetXY(200, 66.3);
            $pdf->Cell(0, 10, $zipResult[2], 0, 0, 'L'); 
            $pdf->SetXY(205, 66.3);
            $pdf->Cell(0, 10, $zipResult[3], 0, 0, 'L'); 
        //}

        

        /////////////////////////END PAYEE INFO//////////////////////////////////////////////////

        /////////////////////////PAYOR INFO///////////////////////////////////////////////////////
            //dd($businessName);
        $result = collect($contacts)->where('Name', $record['contact_name'])->first();
        if(collect($result['TaxNumber'])->isNotEmpty()){
            $payorTaxNumber = $result['TaxNumber'];
            //dd($payorTaxNumber);
            $explodedPayorTaxNumber = explode('-', $payorTaxNumber);

            foreach ($explodedPayorTaxNumber as $key => $data) {
                $payorTaxNumberResult[] = str_split(($data));
            }
            $pdf->SetXY(73, 87.4);
            $pdf->Cell(0, 10, $payorTaxNumberResult[0][0], 0, 0, 'L'); 

            $pdf->SetXY(78, 87.4); 
            $pdf->Cell(0, 10, $payorTaxNumberResult[0][1], 0, 0, 'L'); 
            $pdf->SetXY(83, 87.4);
            $pdf->Cell(0, 10, $payorTaxNumberResult[0][2], 0, 1, 'L'); 
            $pdf->SetXY(91, 87.4); 
            $pdf->Cell(0, 10, $payorTaxNumberResult[1][0], 0, 1, 'L'); 
            $pdf->SetXY(95, 87.4); 
            $pdf->Cell(0, 10, $payorTaxNumberResult[1][1], 0, 1, 'L'); 
            $pdf->SetXY(100, 87.4); 
            $pdf->Cell(0, 10, $payorTaxNumberResult[1][2], 0, 1, 'L'); 
            $pdf->SetXY(109, 87.4); 
            $pdf->Cell(0, 10, $payorTaxNumberResult[2][0], 0, 1, 'L'); 
            $pdf->SetXY(114, 87.4); 
            $pdf->Cell(0, 10, $payorTaxNumberResult[2][1], 0, 1, 'L'); 
            $pdf->SetXY(119, 87.4); 
            $pdf->Cell(0, 10, $payorTaxNumberResult[2][2], 0, 1, 'L'); 
            $pdf->SetXY(128, 87.4); 
            $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
            $pdf->SetXY(133, 87.4); 
            $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
            $pdf->SetXY(138, 87.4); 
            $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
        }
        
        $payor = $result;
        //dd($payor);
        $pdf->SetXY(14, 96.3);
        $pdf->Cell(0, 10, $payor['Name'], 0, 0, 'L'); 

        //if(isset($payor['Addresses'][0]) && !empty($payor['Addresses']) && !empty($payor['Addresses'][0]['AddressLine1']) ){
            $pdf->SetXY(14, 106.3);
            $pdf->Cell(0, 10, $payor['Addresses'][0]['City'].' '.$payor['Addresses'][0]['Region'].' '.$payor['Addresses'][0]['Country'], 0, 0, 'L'); 

            if(collect($payor['Addresses'][0]['PostalCode'])->isNotEmpty()){
            $zipResult = str_split(($payor['Addresses'][0]['PostalCode']));
                if(collect($zipResult)->isNotEmpty()){
                    $pdf->SetXY(191, 106.3);
                    $pdf->Cell(0, 10, $zipResult[0], 0, 0, 'L'); 
                    $pdf->SetXY(195.5, 106.3);
                    $pdf->Cell(0, 10, $zipResult[1], 0, 0, 'L'); 
                    $pdf->SetXY(200, 106.3);
                    $pdf->Cell(0, 10, $zipResult[2], 0, 0, 'L'); 
                    $pdf->SetXY(205, 106.3);
                    $pdf->Cell(0, 10, $zipResult[3], 0, 0, 'L'); 
                }
            }
        //}

        /////////////////////////END PAYOR INFO//////////////////////////////////////////////////

        /////////////////////////PAYMENT INFO////////////////////////////////////////////////////

        //determine which month quarter

        //dd($businessName);
        $totalQuantity = 0;
        $total = 0;
        $totalTax = 0;
        $totalLineAmount = 0;
        $paymentTotalFirstQuarter = 0;
        $paymentTotalSecondQuarter = 0;
        $paymentTotalThirdQuarter = 0;

        $firstCoordinateX = 86;
        $secondCoordinateX = 111;
        $thirdCoordinateX = 141;
        $str = $record['invoice_date'];
        $totalTax += $record->gross;
        $current_month = Carbon::parse($str)->format('m');
        $current_year = Carbon::parse($str)->format('Y');

        if($current_month>=1 && $current_month<=3)
        {
            $start_date = strtotime('1-October-'.($current_year-1));  // timestamp or 1-October Last Year 12:00:00 AM
            $end_date = strtotime('1-January-'.$current_year);  // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
        } 
        else if($current_month>=4 && $current_month<=6)
        {
            $start_date = strtotime('1-January-'.$current_year);  // timestamp or 1-Januray 12:00:00 AM
            $end_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
        }
        else  if($current_month>=7 && $current_month<=9)
        {
            $start_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
            $end_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
        }
        else  if($current_month>=10 && $current_month<=12)
        {
            $start_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
            $end_date = strtotime('1-October-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
        }

        $previousQuarter = Carbon::parse($start_date)->endOfQuarter()->format('m');
        $currentQuarter = Carbon::parse($str)->format('m');
        if($previousQuarter == 12){
            $previousQuarter = 0;
            $sumQuarter = ($currentQuarter - $previousQuarter);
        }else{
            $sumQuarter = ($currentQuarter - $previousQuarter);
        }

        $text = explode(' ',$record['description']);

        $pdf->SetFontSize('6'); 

        $pdf->SetXY(60, 129.3);
        $pdf->Cell(0, 5, $record['item_code'], 0, 0, 'L'); 

        if($sumQuarter == 1){
            $paymentTotalFirstQuarter += $record['quantity'];
            $pdf->SetXY($firstCoordinateX, 129.3);
            $pdf->Cell(0, 5, $record['quantity'], 0, 0, 'L'); 
        }elseif($sumQuarter == 2){
            $paymentTotalSecondQuarter += $record['quantity'];
            $pdf->SetXY($secondCoordinateX, 129.3);
            $pdf->Cell(0, 5, $record['quantity'], 0, 0, 'L'); 
        }else{
            $paymentTotalThirdQuarter += $payment['quantity'];
            $pdf->SetXY($thirdCoordinateX, 129.3);
            $pdf->Cell(0, 5, $record['quantity'], 0, 0, 'L'); 
        }

        $pdf->SetXY(166, 129.3);
        $pdf->Cell(0, 5, str_replace('-','',$record['quantity']), 0, 0, 'L'); 

        $pdf->SetXY(193, 129.3);
        $pdf->Cell(0, 5, str_replace('-','',$record['gross']), 0, 0, 'L'); 

        $pdf->SetXY(7, 129.3);
        $current_y = $pdf->GetY();
        $current_x = $pdf->GetX();

        $cell_width = 50;
        $pdf->MultiCell($cell_width, 2, $record['description']); 
        $pdf->SetXY($current_x + $cell_width, 129.3);
          

        $pdf->SetXY(90, 177.7);
        $pdf->Cell(0, 5, str_replace('-','',$paymentTotalFirstQuarter), 0, 0, 'L');      

        $pdf->SetXY(120, 177.7);
        $pdf->Cell(0, 5, str_replace('-','',$paymentTotalSecondQuarter), 0, 0, 'L'); 

        $pdf->SetXY(143, 177.7);
        $pdf->Cell(0, 5, str_replace('-','',$paymentTotalThirdQuarter), 0, 0, 'L');     

        $pdf->SetXY(165, 177.7);
        $pdf->Cell(0, 5, str_replace('-','',($paymentTotalFirstQuarter + $paymentTotalSecondQuarter + $paymentTotalThirdQuarter)), 0, 0, 'L');   

        $pdf->SetXY(195, 177.7);
        $pdf->Cell(0, 5, str_replace('-','',$totalTax), 0, 0, 'L');      

        /////////////////////////END PAYMENT INFO////////////////////////////////////////////////////
        //return Zip::create('zipFileName.zip', $pdf->Output() );
        //dd($pdf->Output("mypdf.pdf","F"));

        
       //return $pdf->Output();

        $pdf->Output($record['contact_name'].".pdf","D");

        // $zip = new ZipArchive;

        // $fileName = 'zipFileName.zip';

        // if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
        // {
        //     $files = File::files(public_path($path));
        
        //     // loop the files result
        //     foreach ($files as $key => $value) {
        //         $relativeNameInZipFile = basename($value);
        //         $zip->addFile($value, $relativeNameInZipFile);
        //     }
             
        //     $zip->close();
        // }
    //File::deleteDirectory($path);
    return \Response::download($path)->deleteFileAfterSend(true);
    }

    public function generateMultiple2307(Request $request){
        $input = $request->all();
        //dd($input);
        //$input = [1,2];
        $this->refreshXeroToken($request);
        $xero = new XeroApp(
            new AccessToken(collect(json_decode($request->session()->get('access_token')))->toArray() ),
            $request->session()->get('xeroOrg')->tenant_id
        );
        $contacts = $xero->contacts()->get();
        $records = Reports2307::whereIn('id', $input)->get();
        //dd($records);
        $orgInfo = DB::table('organizations')->where('org_id',$request->session()->get('xeroOrg')->id)->first();
        
        foreach($records as $record){
            $pdf = new Fpdi();
            $pagecount = $pdf->setSourceFile(public_path().'/files/form-2307.pdf');  
            // import page 1  
            $tpl = $pdf->importPage(1);
            $pdf->AddPage();
            // Use the imported page as the template
            $size['width'] = 300;
            //$pdf->useTemplate($tpl, null, null, $size['width'], null, true);
            $pdf->useTemplate($tpl, null, null, null, null, true);

            // Set the default font to use
            $pdf->SetFont('Helvetica');
            $pdf->SetFontSize('13'); 

            /////////////////////////Period Date//////////////////////////////////////////////
            //dd($records[0]['period_from']);
            $periodDate = Carbon::parse($record['period_from'])->format('m-d-Y');

            $explodedPeriodDate = explode('-', $periodDate);

            foreach ($explodedPeriodDate as $key => $data) {
                $periodDateResult[] = str_split(($data));
            }
            // Date Month
            $pdf->SetXY(54, 35.5);
            $pdf->Cell(0, 10, $periodDateResult[0][0], 0, 0, 'L'); 

            $pdf->SetXY(58, 35.5); 
            $pdf->Cell(0, 10, $periodDateResult[0][1], 0, 0, 'L'); 

            // Date Day
            $pdf->SetXY(63, 35.5);
            $pdf->Cell(0, 10, $periodDateResult[1][0], 0, 1, 'L'); 

            $pdf->SetXY(67, 35.5); 
            $pdf->Cell(0, 10, $periodDateResult[1][1], 0, 1, 'L'); 

            // Date Year
            $pdf->SetXY(72, 35.5); 
            $pdf->Cell(0, 10, $periodDateResult[2][0], 0, 1, 'L'); 
            $pdf->SetXY(76.2, 35.5); 
            $pdf->Cell(0, 10, $periodDateResult[2][1], 0, 1, 'L'); 
            $pdf->SetXY(81, 35.5); 
            $pdf->Cell(0, 10, $periodDateResult[2][2], 0, 1, 'L'); 
            $pdf->SetXY(85.5, 35.5); 
            $pdf->Cell(0, 10, $periodDateResult[2][3], 0, 1, 'L'); 
            /////////////////////////END Period Date//////////////////////////////////////////////

            /////////////////////////Due Date//////////////////////////////////////////////
            $dueDate = Carbon::parse($record['period_to'])->format('m-d-Y');

            $explodedDueDate = explode('-', $dueDate);

            foreach ($explodedDueDate as $key => $data) {
                $dueDateResult[] = str_split(($data));
            }
            // Date Month
            $pdf->SetXY(141, 35.5);
            $pdf->Cell(0, 10, $dueDateResult[0][0], 0, 0, 'L'); 

            $pdf->SetXY(145, 35.5); 
            $pdf->Cell(0, 10, $dueDateResult[0][1], 0, 0, 'L'); 

            // Date Day
            $pdf->SetXY(150, 35.5);
            $pdf->Cell(0, 10, $dueDateResult[1][0], 0, 1, 'L'); 

            $pdf->SetXY(155, 35.5); 
            $pdf->Cell(0, 10, $dueDateResult[1][1], 0, 1, 'L'); 

            // Date Year
            $pdf->SetXY(160, 35.5); 
            $pdf->Cell(0, 10, $dueDateResult[2][0], 0, 1, 'L'); 
            $pdf->SetXY(164.5, 35.5); 
            $pdf->Cell(0, 10, $dueDateResult[2][1], 0, 1, 'L'); 
            $pdf->SetXY(169.5, 35.5); 
            $pdf->Cell(0, 10, $dueDateResult[2][2], 0, 1, 'L'); 
            $pdf->SetXY(174, 35.5); 
            $pdf->Cell(0, 10, $dueDateResult[2][3], 0, 1, 'L'); 
            /////////////////////////END Due Date/////////////////////////////////////////////////

            /////////////////////////PAYEE INFO//////////////////////////////////////////////////
            //$contactInfo = $xero->getContact($this->xeroTenantId, $result['data']['journal']['paymentData']['Contact']['ContactID']);
            $collectedContactInfo = $orgInfo;
            //dd($collectedContactInfo);

            $payeeInfo = $collectedContactInfo;
            if(isset($collectedContactInfo->tin_number) && !empty($collectedContactInfo->tin_number)){
                $taxNumber = $collectedContactInfo->tin_number;

                $explodedTaxNumber = explode('-', $taxNumber);
                foreach ($explodedTaxNumber as $key => $data) {
                    $taxNumberResult[] = str_split(($data));
                }
                $pdf->SetXY(73, 46.3);
                $pdf->Cell(0, 10, $taxNumberResult[0][0], 0, 0, 'L'); 

                $pdf->SetXY(78, 46.3); 
                $pdf->Cell(0, 10, $taxNumberResult[0][1], 0, 0, 'L'); 
                $pdf->SetXY(83, 46.3);
                $pdf->Cell(0, 10, $taxNumberResult[0][2], 0, 1, 'L'); 
                $pdf->SetXY(91, 46.3); 
                $pdf->Cell(0, 10, $taxNumberResult[1][0], 0, 1, 'L'); 
                $pdf->SetXY(95, 46.3); 
                $pdf->Cell(0, 10, $taxNumberResult[1][1], 0, 1, 'L'); 
                $pdf->SetXY(100, 46.3); 
                $pdf->Cell(0, 10, $taxNumberResult[1][2], 0, 1, 'L'); 
                $pdf->SetXY(109, 46.3); 
                $pdf->Cell(0, 10, $taxNumberResult[2][0], 0, 1, 'L'); 
                $pdf->SetXY(114, 46.3); 
                $pdf->Cell(0, 10, $taxNumberResult[2][1], 0, 1, 'L'); 
                $pdf->SetXY(119, 46.3); 
                $pdf->Cell(0, 10, $taxNumberResult[2][2], 0, 1, 'L'); 
                $pdf->SetXY(128, 46.3); 
                $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
                $pdf->SetXY(133, 46.3); 
                $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
                $pdf->SetXY(138, 46.3); 
                $pdf->Cell(0, 10, '0', 0, 1, 'L'); 

            }
            $pdf->SetXY(14, 56.3);
            $pdf->Cell(0, 10, $payeeInfo->trade_name, 0, 0, 'L'); 

            //if(isset($payeeInfo['Addresses'][0]) && !empty($payeeInfo['Addresses']) && !empty($payeeInfo['Addresses'][0]['AddressLine1']) ){
                $pdf->SetXY(14, 66.3);
                $pdf->Cell(0, 10, $payeeInfo->street.', '.$payeeInfo->barangay.', '.$payeeInfo->city.', '.$payeeInfo->province, 0, 0, 'L'); 

                $zipResult = str_split(($payeeInfo->zip_code));

                $pdf->SetXY(191, 66.3);
                $pdf->Cell(0, 10, $zipResult[0], 0, 0, 'L'); 
                $pdf->SetXY(195.5, 66.3);
                $pdf->Cell(0, 10, $zipResult[1], 0, 0, 'L'); 
                $pdf->SetXY(200, 66.3);
                $pdf->Cell(0, 10, $zipResult[2], 0, 0, 'L'); 
                $pdf->SetXY(205, 66.3);
                $pdf->Cell(0, 10, $zipResult[3], 0, 0, 'L'); 
            //}

            

            /////////////////////////END PAYEE INFO//////////////////////////////////////////////////

            /////////////////////////PAYOR INFO///////////////////////////////////////////////////////
                //dd($businessName);
            $result = collect($contacts)->where('Name', $record['contact_name'])->first();
            if(collect($result['TaxNumber'])->isNotEmpty()){
                $payorTaxNumber = $result['TaxNumber'];
                //dd($payorTaxNumber);
                $explodedPayorTaxNumber = explode('-', $payorTaxNumber);

                foreach ($explodedPayorTaxNumber as $key => $data) {
                    $payorTaxNumberResult[] = str_split(($data));
                }
                $pdf->SetXY(73, 87.4);
                $pdf->Cell(0, 10, $payorTaxNumberResult[0][0], 0, 0, 'L'); 

                $pdf->SetXY(78, 87.4); 
                $pdf->Cell(0, 10, $payorTaxNumberResult[0][1], 0, 0, 'L'); 
                $pdf->SetXY(83, 87.4);
                $pdf->Cell(0, 10, $payorTaxNumberResult[0][2], 0, 1, 'L'); 
                $pdf->SetXY(91, 87.4); 
                $pdf->Cell(0, 10, $payorTaxNumberResult[1][0], 0, 1, 'L'); 
                $pdf->SetXY(95, 87.4); 
                $pdf->Cell(0, 10, $payorTaxNumberResult[1][1], 0, 1, 'L'); 
                $pdf->SetXY(100, 87.4); 
                $pdf->Cell(0, 10, $payorTaxNumberResult[1][2], 0, 1, 'L'); 
                $pdf->SetXY(109, 87.4); 
                $pdf->Cell(0, 10, $payorTaxNumberResult[2][0], 0, 1, 'L'); 
                $pdf->SetXY(114, 87.4); 
                $pdf->Cell(0, 10, $payorTaxNumberResult[2][1], 0, 1, 'L'); 
                $pdf->SetXY(119, 87.4); 
                $pdf->Cell(0, 10, $payorTaxNumberResult[2][2], 0, 1, 'L'); 
                $pdf->SetXY(128, 87.4); 
                $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
                $pdf->SetXY(133, 87.4); 
                $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
                $pdf->SetXY(138, 87.4); 
                $pdf->Cell(0, 10, '0', 0, 1, 'L'); 
            }
            
            $payor = $result;
            //dd($payor);
            $pdf->SetXY(14, 96.3);
            $pdf->Cell(0, 10, $payor['Name'], 0, 0, 'L'); 

            //if(isset($payor['Addresses'][0]) && !empty($payor['Addresses']) && !empty($payor['Addresses'][0]['AddressLine1']) ){
                $pdf->SetXY(14, 106.3);
                $pdf->Cell(0, 10, $payor['Addresses'][0]['City'].' '.$payor['Addresses'][0]['Region'].' '.$payor['Addresses'][0]['Country'], 0, 0, 'L'); 

                if(collect($payor['Addresses'][0]['PostalCode'])->isNotEmpty()){
                $zipResult = str_split(($payor['Addresses'][0]['PostalCode']));
                    if(collect($zipResult)->isNotEmpty()){
                        $pdf->SetXY(191, 106.3);
                        $pdf->Cell(0, 10, $zipResult[0], 0, 0, 'L'); 
                        $pdf->SetXY(195.5, 106.3);
                        $pdf->Cell(0, 10, $zipResult[1], 0, 0, 'L'); 
                        $pdf->SetXY(200, 106.3);
                        $pdf->Cell(0, 10, $zipResult[2], 0, 0, 'L'); 
                        $pdf->SetXY(205, 106.3);
                        $pdf->Cell(0, 10, $zipResult[3], 0, 0, 'L'); 
                    }
                }
            //}

            /////////////////////////END PAYOR INFO//////////////////////////////////////////////////

            /////////////////////////PAYMENT INFO////////////////////////////////////////////////////

            //determine which month quarter

            //dd($businessName);
            $totalQuantity = 0;
            $total = 0;
            $totalTax = 0;
            $totalLineAmount = 0;
            $paymentTotalFirstQuarter = 0;
            $paymentTotalSecondQuarter = 0;
            $paymentTotalThirdQuarter = 0;

            $firstCoordinateX = 86;
            $secondCoordinateX = 111;
            $thirdCoordinateX = 141;
            $str = $record['invoice_date'];
            $totalTax += $record->gross;
            $current_month = Carbon::parse($str)->format('m');
            $current_year = Carbon::parse($str)->format('Y');

            if($current_month>=1 && $current_month<=3)
            {
                $start_date = strtotime('1-October-'.($current_year-1));  // timestamp or 1-October Last Year 12:00:00 AM
                $end_date = strtotime('1-January-'.$current_year);  // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
            } 
            else if($current_month>=4 && $current_month<=6)
            {
                $start_date = strtotime('1-January-'.$current_year);  // timestamp or 1-Januray 12:00:00 AM
                $end_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
            }
            else  if($current_month>=7 && $current_month<=9)
            {
                $start_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
                $end_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
            }
            else  if($current_month>=10 && $current_month<=12)
            {
                $start_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
                $end_date = strtotime('1-October-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
            }

            $previousQuarter = Carbon::parse($start_date)->endOfQuarter()->format('m');
            $currentQuarter = Carbon::parse($str)->format('m');
            if($previousQuarter == 12){
                $previousQuarter = 0;
                $sumQuarter = ($currentQuarter - $previousQuarter);
            }else{
                $sumQuarter = ($currentQuarter - $previousQuarter);
            }

            $text = explode(' ',$record['description']);

            $pdf->SetFontSize('6'); 

            $pdf->SetXY(60, 129.3);
            $pdf->Cell(0, 5, $record['item_code'], 0, 0, 'L'); 

            if($sumQuarter == 1){
                $paymentTotalFirstQuarter += $record['quantity'];
                $pdf->SetXY($firstCoordinateX, 129.3);
                $pdf->Cell(0, 5, $record['quantity'], 0, 0, 'L'); 
            }elseif($sumQuarter == 2){
                $paymentTotalSecondQuarter += $record['quantity'];
                $pdf->SetXY($secondCoordinateX, 129.3);
                $pdf->Cell(0, 5, $record['quantity'], 0, 0, 'L'); 
            }else{
                $paymentTotalThirdQuarter += $payment['quantity'];
                $pdf->SetXY($thirdCoordinateX, 129.3);
                $pdf->Cell(0, 5, $record['quantity'], 0, 0, 'L'); 
            }

            $pdf->SetXY(166, 129.3);
            $pdf->Cell(0, 5, str_replace('-','',$record['quantity']), 0, 0, 'L'); 

            $pdf->SetXY(193, 129.3);
            $pdf->Cell(0, 5, str_replace('-','',$record['gross']), 0, 0, 'L'); 

            $pdf->SetXY(7, 129.3);
            $current_y = $pdf->GetY();
            $current_x = $pdf->GetX();

            $cell_width = 50;
            $pdf->MultiCell($cell_width, 2, $record['description']); 
            $pdf->SetXY($current_x + $cell_width, 129.3);
              

            $pdf->SetXY(90, 177.7);
            $pdf->Cell(0, 5, str_replace('-','',$paymentTotalFirstQuarter), 0, 0, 'L');      

            $pdf->SetXY(120, 177.7);
            $pdf->Cell(0, 5, str_replace('-','',$paymentTotalSecondQuarter), 0, 0, 'L'); 

            $pdf->SetXY(143, 177.7);
            $pdf->Cell(0, 5, str_replace('-','',$paymentTotalThirdQuarter), 0, 0, 'L');     

            $pdf->SetXY(165, 177.7);
            $pdf->Cell(0, 5, str_replace('-','',($paymentTotalFirstQuarter + $paymentTotalSecondQuarter + $paymentTotalThirdQuarter)), 0, 0, 'L');   

            $pdf->SetXY(195, 177.7);
            $pdf->Cell(0, 5, str_replace('-','',$totalTax), 0, 0, 'L');      

            /////////////////////////END PAYMENT INFO////////////////////////////////////////////////////
            //return Zip::create('zipFileName.zip', $pdf->Output() );
            //dd($pdf->Output("mypdf.pdf","F"));

            
           //return $pdf->Output();
            $path = $record['org_id'].'-'.$record['batch_number'];
            if (!file_exists($path)) {
                File::makeDirectory($path);
            }
           $pdf->Output($path.'/'.$record['contact_name'].".pdf","F");

            $zip = new ZipArchive;

            $fileName = 'zipFileName.zip';

            if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
            {
                $files = File::files(public_path($path));
            
                // loop the files result
                foreach ($files as $key => $value) {
                    $relativeNameInZipFile = basename($value);
                    $zip->addFile($value, $relativeNameInZipFile);
                }
                 
                $zip->close();
            }

            // $path = $payment['org_id'].'-'.$payment['batch_number'];
        

            // $pdf->Output($path.'/'.$businessName.".pdf","F");

            // $zip = new ZipArchive;

            // $fileName = 'zipFileName.zip';

            // if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
            // {
            //     $files = File::files(public_path($path));
            
            //     // loop the files result
            //     foreach ($files as $key => $value) {
            //         $relativeNameInZipFile = basename($value);
            //         $zip->addFile($value, $relativeNameInZipFile);
            //     }
                 
            //     $zip->close();
            // }
    }
        File::deleteDirectory($path);
        //return \Response::download($fileName)->deleteFileAfterSend(true);
        //return \Response::download($fileName)->deleteFileAfterSend(true);
        return response()->download($fileName)->deleteFileAfterSend(true);
    }

    public function removeSalesBatchRecord(Request $request){
        $input = $request->all();
        DB::table('sales')->where('batch_number', $input['id'])->delete();
        return 'success';
    }

    public function removePurchasesBatchRecord(Request $request){
        $input = $request->all();
        DB::table('purchases')->where('batch_number', $input['id'])->delete();
        return 'success';
    }

    public function getSLSPRecords(Request $request){
        $input = $request->all();

        $year = $input['year'];
        $now = Carbon::now();
        $fromQuarter = $now->lastOfQuarter();

        switch ($input['quarter']) {
            case 1:
                $now = new Carbon('first day of January '.$year, 'Asia/Manila');
                break;
            case 2:
                $now = new Carbon('first day of April '.$year, 'Asia/Manila');
                break;
            case 3:
                $now = new Carbon('first day of July '.$year, 'Asia/Manila');
                break;
            default:
                $now = new Carbon('first day of October '.$year, 'Asia/Manila');
        }
        $fromQuarter = Carbon::parse($now)->firstOfQuarter()->toDateTimeString();
        $lastQuarter = Carbon::parse($now)->lastOfQuarter()->toDateTimeString();
        
        $getSalesRecords = DB::table('sales')->where('org_id', '=', $request->session()->get('xeroOrg')->id)->whereBetween('invoice_date', [$fromQuarter,$lastQuarter])->get();

        $salesRecords = [];
        foreach($getSalesRecords as $key => $record){
            $salesRecords[$record->batch_number]['id'] = $record->batch_number;
            $salesRecords[$record->batch_number]['created_at'] = $record->created_at;
            $salesRecords[$record->batch_number]['period_from'] = $record->period_from;
            $salesRecords[$record->batch_number]['period_to'] = $record->period_to;
            $salesRecords[$record->batch_number]['data'][] = $record;
        }

        $getPurchasesRecords = DB::table('purchases')->where('org_id', '=', $request->session()->get('xeroOrg')->id)->whereBetween('invoice_date', [$fromQuarter,$lastQuarter])->get();

        $purchasesRecords = [];
        foreach($getPurchasesRecords as $key => $record){
            $purchasesRecords[$record->batch_number]['id'] = $record->batch_number;
            $purchasesRecords[$record->batch_number]['created_at'] = $record->created_at;
            $purchasesRecords[$record->batch_number]['period_from'] = $record->period_from;
            $purchasesRecords[$record->batch_number]['period_to'] = $record->period_to;
            $purchasesRecords[$record->batch_number]['data'][] = $record;
        }

        return ['sales' => collect($salesRecords)->values()->toArray(), 'purchases' => collect($purchasesRecords)->values()->toArray()];
    }

    public function downloadQuarterlySLSPViaPDF(Request $request){
        $this->refreshXeroToken($request);
        $input = $request->all();
        //dd($input);
        $year = $input['year'];
        // $year = "2022";
        // $input['quarter'] = 1;
        $now = Carbon::now();
        $fromQuarter = $now->lastOfQuarter();

        switch ($input['quarter']) {
            case 1:
                $now = new Carbon('first day of January '.$year, 'Asia/Manila');
                break;
            case 2:
                $now = new Carbon('first day of April '.$year, 'Asia/Manila');
                break;
            case 3:
                $now = new Carbon('first day of July '.$year, 'Asia/Manila');
                break;
            default:
                $now = new Carbon('first day of October '.$year, 'Asia/Manila');
        }
        $fromQuarter = Carbon::parse($now)->firstOfQuarter()->toDateTimeString();
        $lastQuarter = Carbon::parse($now)->lastOfQuarter()->toDateTimeString();
        
        $getSalesRecords = DB::table('sales')->where('org_id', '=', $request->session()->get('xeroOrg')->id)->whereBetween('invoice_date', [$fromQuarter,$lastQuarter])->get();

        $salesRecords = collect($getSalesRecords)->groupBy(function($d) {
             return Carbon::parse($d->invoice_date)->format('m');
        });

        $getPurchasesRecords = DB::table('purchases')->where('org_id', '=', $request->session()->get('xeroOrg')->id)->whereBetween('invoice_date', [$fromQuarter,$lastQuarter])->get();

        $purchasesRecords = collect($getPurchasesRecords)->groupBy(function($d) {
             return Carbon::parse($d->invoice_date)->format('m');
        });

        $orgInfo = DB::table('organizations')->where('org_id',$request->session()->get('xeroOrg')->id)->first();

        if(collect($orgInfo)->isNotEmpty()){
            $orgInfo->address = $orgInfo->street.' '.$orgInfo->barangay.' '.$orgInfo->city.' '.$orgInfo->province.' '.$orgInfo->zip_code;
        }

        $xero = new XeroApp(
            new AccessToken(collect(json_decode($request->session()->get('access_token')))->toArray() ),
            $request->session()->get('xeroOrg')->tenant_id
        );

        $contacts = $xero->contacts()->get();
        $path = \Str::random(40);
        if (!file_exists($path)) {
            File::makeDirectory($path);
        }

        foreach(collect($salesRecords)->values()->toArray() as $key => $data){
            $salesData = collect($data)->values()->toArray();
            $fileName = \Str::random(40);
            $pdf = \PDF::loadView('pdf.sales', ['salesData' => $salesData, 'org' => $orgInfo, 'contacts' => $contacts])->setPaper('a4', 'landscape')->save($path.'/'.$fileName.'.pdf');
            //return $pdf->stream();            
        }

        foreach(collect($purchasesRecords)->values()->toArray() as $key => $data){
            $purchasesData = collect($data)->values()->toArray();
            $fileName = \Str::random(40);
            $pdf = \PDF::loadView('pdf.purchases', ['purchasesData' => $purchasesData, 'org' => $orgInfo, 'contacts' => $contacts])->setPaper('a4', 'landscape')->save($path.'/'.$fileName.'.pdf');
            //return $pdf->stream();            
        }
        $zip = new ZipArchive;

        $fileName = 'zipFileName.zip';

        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
        {
            $files = File::files(public_path($path));
        
            // loop the files result
            foreach ($files as $key => $value) {
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }
             
            $zip->close();
        }

        File::deleteDirectory($path);

        return response()->download($fileName)->deleteFileAfterSend(true);
    }
}
