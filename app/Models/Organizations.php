<?php 
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    class Organizations extends Model
    {
        protected $table = 'organizations';

        protected $fillable = [
	        'tenant_id','tax_payer','special_tax_relief','rdo_codes','tax_year','category'
	    ];

		public function getRdoCodeAttribute($value) {
		  return json_encode($value);
		}
    }
?>