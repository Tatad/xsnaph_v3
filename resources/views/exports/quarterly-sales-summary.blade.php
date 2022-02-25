{{--dd($org)--}}
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
                <b>TIN: {{$org->tin_number}}</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>OWNER'S NAME: {{$org->trade_name}}</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>OWNER'S TRADE NAME: {{$org->trade_name}}</b>
            </td>
        </tr>
        <tr>
            <td >
                <b>OWNER'S ADDRESS: {{$org->address}}</b>
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
        <?php
            $grandTotalNet = 0;
            $taxExemptGrandTotal = 0;
            $zeroRatedGrandTotal = 0;
            $taxTotal = 0;
            $grossGrandTotal = 0;
        ?>
        @foreach($records as $key => $data)
            <?php

                $grossGrandTotal += $data->gross; 
                $taxTotal += $data->tax; 
                if(strpos($data->tax_rate_name, 'Tax Exempt') !== false){
                    $taxExemptGrandTotal += $data->net; 
                }

                if(strpos($data->tax_rate_name, 'Zero Rated') !== false){
                    $zeroRatedGrandTotal += $data->net; 
                }

                if(strpos($data->tax_rate_name, 'Tax Exempt') == false){
                    $grandTotalNet += $data->net; 
                }
            ?>
            <tr>
                <td>{{$data->taxable_month}}</td>
                <td>{{($data->tin_number) ? $data->tin_number : '--' }}</td>
                <td>{{($data->contact_name) ? $data->contact_name : '--' }}</td>
                <td>{{($data->first_name) ? $data->first_name.' '.$data->last_name : '' }}</td>
                <td>{{($data->address) ? $data->address : '' }}</td>
                <td>{{($data->net && strpos($data->tax_rate_name, 'Tax Exempt') == false ) ? $data->net : 0.00 }}</td>
                <td>{{($data->net && strpos($data->tax_rate_name, 'Tax Exempt') !== false ) ? $data->net : 0.00 }}</td>
                <td>{{($data->net && strpos($data->tax_rate_name, 'Zero Rated') !== false ) ? $data->net : 0.00 }}</td>
                <td>{{($data->net && strpos($data->tax_rate_name, 'Tax Exempt') == false ) ? $data->net : 0.00 }}</td>
                <td>{{($data->tax ) ? $data->tax : 0.00 }}</td>
                <td>{{($data->gross ) ? $data->gross : 0.00 }}</td>
            </tr>
        @endforeach
        <tr></tr>
        <tr>
            <td>Grand Total</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{$grandTotalNet}}</td>
            <td>{{$taxExemptGrandTotal}}</td>
            <td>{{$zeroRatedGrandTotal}}</td>
            <td>{{$grandTotalNet}}</td>
            <td>{{$taxTotal}}</td>
            <td>{{$grossGrandTotal}}</td>
        </tr>
        <tr></tr>
        <tr>
            <td>END OF REPORT</td>
        </tr>
    </tbody>
</table>