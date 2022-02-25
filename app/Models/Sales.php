<?php 
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    class Sales extends Model
    {
        protected $fillable = [
            'org_id','period_from','period_to','invoice_number','invoice_date','source','reference','description','tax','tax_rate','tax_rate_name','gross','net','batch_number','status','contact_name'
        ];
        protected $table = 'sales';
    }
?>