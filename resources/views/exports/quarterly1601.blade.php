{{--dd($lastQuarter)--}}
<table>
    <tbody>
        <tr>
            <td >
                <b>Attachment to BIR Form 1601-EQ</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>QUARTERLY ALPHABETICAL LIST OF PAYEES SUBJECTED TO EXPANDED WITHHOLDING TAX AND PAYEES WHOSE INCOME PAYMENTS ARE EXEMPT</b>
            </td>
        </tr>
        <tr>
            <td >
                <b>FOR THE QUARTER ENDING {{$lastQuarter}}, {{$year}}</b>
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
                <b>WITHHOLDING AGENT'S NAME: {{$org->trade_name}}</b>
            </td>
        </tr>

        <tr>
            
        </tr>

        <tr>
            
        </tr>

        <tr>
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b></b></td>    
            <td><b>1ST MONTH OF THE QUARTER</b></td>    
            <td><b></b></td>    
            <td><b></b></td> 
            <td><b>2ND MONTH OF THE QUARTER</b></td> 
            <td><b></b></td>    
            <td><b></b></td> 
            <td><b>3RD MONTH OF THE QUARTER</b></td> 
            <td><b></b></td>    
            <td><b></b></td> 
            <td><b></b></td>    
            <td><b></b></td> 
        </tr>

        <tr>
            <td><b>SEQ</b></td>    
            <td><b>TAXPAYER</b></td>    
            <td><b>CORPORATION</b></td>    
            <td><b>INDIVIDUAL</b></td>    
            <td><b>ATC CODE</b></td>    
            <td><b>NATURE OF PAYMENT</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>TAX RATE</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>TAX RATE</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td> 
            <td><b>TAX RATE</b></td>    
            <td><b>AMOUNT OF</b></td>    
            <td><b>AMOUNT OF</b></td> 
            <td><b>TOTAL</b></td>    
        </tr>
        <tr>
            <td><b>NO</b></td>    
            <td><b>IDENTIFICATION</b></td>    
            <td><b>(Registered Name)</b></td>    
            <td><b>(Last Name, First Name, Middle Name)</b></td>    
            <td><b></b></td>    
            <td><b></b></td>
            <td><b>INCOME PAYMENT</b></td>     
            <td><b></b></td>    
            <td><b>TAX WITHHELD</b></td>    
            <td><b>INCOME PAYMENT</b></td>   
            <td><b></b></td>     
            <td><b>TAX WITHHELD</b></td>    
            <td><b>INCOME PAYMENT</b></td>     
            <td><b></b></td>     
            <td><b>TAX WITHHELD</b></td>    
            <td><b>INCOME PAYMENT</b></td>     
            <td><b>TAX WITHHELD</b></td>    
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
            <td><b></b></td>     
            <td><b>(6)</b></td>    
            <td><b>(7)</b></td>    
            <td><b>(8)</b></td>    
            <td><b>(9)</b></td>    
            <td><b>(10)</b></td>    
            <td><b>(11)</b></td>    
            <td><b>(12)</b></td>    
            <td><b>(13)</b></td>    
            <td><b>(14)</b></td>    
            <td><b>(15)</b></td>    
            <td><b>(16)</b></td>   
        </tr>

        {{--dd($records)--}}
        <?php
            $grandTotal = 0;
            $grandTotalTax = 0;
            $month1Quantity = 0;
            $month1Tax = 0;
            $month2Quantity = 0;
            $month2Tax = 0;
            $month3Quantity = 0;
            $month3Tax = 0;
        ?>
        @foreach($records as $key => $data)
            <?php
                $grandTotal += $data->quantity; 
                $grandTotalTax += abs($data->gross); 
                if($data->month == 1 || $data->month == 4 || $data->month == 7 || $data->month == 10){
                    $month1Quantity += $data->quantity;
                    $month1Tax += abs($data->gross);
                }

                if($data->month == 2 || $data->month == 5 || $data->month == 8 || $data->month == 11){
                    $month2Quantity += $data->quantity;
                    $month2Tax += abs($data->gross);
                }

                if($data->month == 3 || $data->month == 6 || $data->month == 9 || $data->month == 12){
                    $month3Quantity += $data->quantity;
                    $month3Tax += abs($data->gross);
                }
            ?>
            <tr>
                <td>1</td>
                <td>{{($data->tin_number) ? $data->tin_number : '--' }}</td>
                <td>{{($data->contact_name) ? $data->contact_name : '--' }}</td>
                <td>{{($data->first_name) ? $data->first_name.' '.$data->last_name : '' }}</td>
                <td>{{($data->item_code) ? $data->item_code : '' }}</td>
                <td>{{($data->description) ? $data->description : 0.00 }}</td>
                <td>{{($data->month == 1 || $data->month == 4 || $data->month == 7 || $data->month == 10) ? $data->quantity : 0.00 }}</td>
                <td></td>
                <td>{{($data->month == 1 || $data->month == 4 || $data->month == 7 || $data->month == 10) ? abs($data->gross) : 0.00 }}</td>
                <td>{{($data->month == 2 || $data->month == 5 || $data->month == 8 || $data->month == 11) ? $data->quantity : 0.00 }}</td>
                <td></td>
                <td>{{($data->month == 2 || $data->month == 5 || $data->month == 8 || $data->month == 11) ? abs($data->gross) : 0.00 }}</td>
                <td>{{($data->month == 3 || $data->month == 6 || $data->month == 9 || $data->month == 12) ? $data->quantity : 0.00 }}</td>
                <td></td>
                <td>{{($data->month == 3 || $data->month == 6 || $data->month == 9 || $data->month == 12) ? abs($data->gross) : 0.00 }}</td>
                <td>{{$data->quantity }}</td>
                <td>{{abs($data->gross) }}</td>
            </tr>
        @endforeach
            <tr>
                <td>Grand Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{$month1Quantity}}</td>
                <td></td>
                <td>{{$month1Tax}}</td>
                <td>{{$month2Quantity}}</td>
                <td></td>
                <td>{{$month2Tax}}</td>
                <td>{{$month3Quantity}}</td>
                <td></td>
                <td>{{$month3Tax}}</td>
                <td>{{$grandTotal}}</td>
                <td>{{$grandTotalTax}}</td>
            </tr>
    </tbody>
</table>