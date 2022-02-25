<table>
    <tbody>
        <tr>
            <td >
                <b>PURCHASE TRANSACTION</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>RECONCILIATION OF LISTING FOR ENFORCEMENT</b>
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>

        <tr>
            <td>
                <b>TIN: {{$purchase['tin_number']}}</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>OWNER'S NAME: {{$purchase['name']}}</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>OWNER'S TRADE NAME: {{$purchase['trade_name']}}</b>
            </td>
        </tr>
        <tr>
            <td >
                <b>OWNER'S ADDRESS: {{$purchase['address']}}</b>
            </td>
        </tr>

        <tr>
            
        </tr>

        <tr>
            <td><b>TAXABLE</b></td>    
            <td><b>TAXPAYER</b></td>    
            <td><b>REGISTERED NAME</b></td>    
            <td><b>NAME OF SUPPLIER</b></td>    
            <td><b>SUPPLIER's ADDRESS</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
        </tr>
        <tr>
            <td><b>MONTH</b></td>    
            <td><b>IDENTIFICATION</b></td>    
            <td><b></b></td>    
            <td><b>(Last Name, First Name, Middle Name)</b></td>    
            <td><b></b></td>    
            <td><b>GROSS PURCHASE</b></td>    
            <td><b>EXEMPT PURCHASE</b></td>    
            <td><b>ZERO RATED PURCHASE</b></td>    
            <td><b>TAXABLE PURCHASE</b></td>   
            <td><b>PURCHASE OF SERVICES</b></td>    
            <td><b>PURCHASE OF CAPITAL GOODS</b></td>    
            <td><b>PURCHASE OF GOODS OTHER THAN CAPITAL GOODS</b></td>    
            <td><b>INPUT TAX</b></td>    
            <td><b>GROSS TAXABLE PURCHASE</b></td>    
        </tr>
        <tr>
            <td><b></b></td>    
            <td><b>NUMBER</b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>  
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
        </tr>
        <tr>
            <td><b>(1)</b></td>    
            <td><b>(2)</b></td>    
            <td><b>(3)</b></td>    
            <td><b>(4)</b></td>    
            <td><b>(5)</b></td>    
            <td><b>(6)</b></td>    
            <td><b>(7)</b></td>    
            <td><b>(8)</b></td>    
            <td><b>(9)</b></td>    
            <td><b>(10)</b></td>    
            <td><b>(11)</b></td>    
            <td><b>(12)</b></td>    
            <td><b>(13)</b></td>    
            <td><b>(14)</b></td>   
        </tr>
        @foreach($purchase['records'] as $key => $data)
            <tr>
                <td>{{$data['taxable_month']}}</td>
                <td>{{($data['tin_number']) ? $data['tin_number'] : '--' }}</td>
                <td>{{($data['contact_name']) ? $data['contact_name'] : '--' }}</td>
                <td>{{($data['first_name']) ? $data['first_name'].' '.$data['last_name'] : '' }}</td>
                <td>{{($data['address']) ? $data['address'] : '' }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Tax on Purchases') == false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Tax Exempt') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Zero Rated') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Tax on Purchases') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'VAT on Purchases (Services)') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'VAT on Purchases (Capital Goods)') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'VAT on Purchases (Goods)') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['tax'] ) ? $data['tax'] : 0.00 }}</td>
                <td>{{($data['gross'] ) ? $data['gross'] : 0.00 }}</td>
            </tr>
        @endforeach
        <tr></tr>
        <tr>
            <td>Grand Total</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{$purchase['grandTotalNet']}}</td>
            <td>{{$purchase['taxExemptGrandTotal']}}</td>
            <td>{{$purchase['zeroRatedGrandTotal']}}</td>
            <td>{{$purchase['grandTotalTaxablePurchase']}}</td>
            <td>{{$purchase['vatOnPurchaseServicesTotal']}}</td>
            <td>{{$purchase['vatOnPurchaseCapitalGoodsTotal']}}</td>
            <td>{{$purchase['purchaseGoodsOtherThanCapitalGoodsTotal']}}</td>
            <td>{{$purchase['taxTotal']}}</td>
            <td>{{$purchase['grossGrandTotal']}}</td>
        </tr>
        <tr></tr>
        <tr>
            <td>END OF REPORT</td>
        </tr>
    </tbody>
</table>