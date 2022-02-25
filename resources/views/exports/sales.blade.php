<table>
    <tbody>
        <tr>
            <td >
                <b>SALES TRANSACTION</b>
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
                <b>TIN: {{$sales['tin_number']}}</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>OWNER'S NAME: {{$sales['name']}}</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>OWNER'S TRADE NAME: {{$sales['trade_name']}}</b>
            </td>
        </tr>
        <tr>
            <td >
                <b>OWNER'S ADDRESS: {{$sales['address']}}</b>
            </td>
        </tr>

        <tr>
            
        </tr>

        <tr>
            <td><b>TAXABLE</b></td>    
            <td><b>TAXPAYER</b></td>    
            <td><b>REGISTERED NAME</b></td>    
            <td><b>NAME OF CUSTOMER</b></td>    
            <td><b>CUSTOMER's ADDRESS</b></td>    
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
            <td><b>GROSS SALES</b></td>    
            <td><b>EXEMPT SALES</b></td>    
            <td><b>ZERO RATED SALES</b></td>    
            <td><b>TAXABLE SALES</b></td>    
            <td><b>OUTPUT TAX</b></td>    
            <td><b>GROSS TAXABLE SALES</b></td>    
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
        </tr>
        @foreach($sales['records'] as $key => $data)
            <tr>
                <td>{{$data['taxable_month']}}</td>
                <td>{{($data['tin_number']) ? $data['tin_number'] : '--' }}</td>
                <td>{{($data['contact_name']) ? $data['contact_name'] : '--' }}</td>
                <td>{{($data['first_name']) ? $data['first_name'].' '.$data['last_name'] : '' }}</td>
                <td>{{($data['address']) ? $data['address'] : '' }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Tax Exempt Sales') == false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Tax Exempt Sales') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Zero Rated Sales') !== false ) ? $data['net'] : 0.00 }}</td>
                <td>{{($data['net'] && strpos($data['tax_rate_name'], 'Tax Exempt Sales') == false ) ? $data['net'] : 0.00 }}</td>
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
            <td>{{$sales['grandTotalNet']}}</td>
            <td>{{$sales['taxExemptGrandTotal']}}</td>
            <td>{{$sales['zeroRatedGrandTotal']}}</td>
            <td>{{$sales['grandTotalNet']}}</td>
            <td>{{$sales['taxTotal']}}</td>
            <td>{{$sales['grossGrandTotal']}}</td>
        </tr>
        <tr></tr>
        <tr>
            <td>END OF REPORT</td>
        </tr>
    </tbody>
</table>