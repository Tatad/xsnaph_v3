<?php 
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    class UserOrganization extends Model
    {
        protected $fillable = [
            'user_id','tenant_id','xero_access_token','org_name','created_at','updated_at'
        ];
        protected $table = 'user_organizations';
    }
?>