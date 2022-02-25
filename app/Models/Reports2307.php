<?php 
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    class Reports2307 extends Model
    {
        protected $fillable = [
            'batch_number',
            'org_id',
            'invoice_date',
            'period_from',
            'period_to',
            'contact_name',
            'source',
            'reference',
            'description',
            'quantity',
            'unit_price',
            'gross',
            'account_code',
            'account',
            'item_code',
        ];
        protected $table = 'reports_2307';
    }
?>