<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use LangleyFoxall\XeroLaravel\OAuth2;
use LangleyFoxall\XeroLaravel\XeroApp;
use League\OAuth2\Client\Token\AccessToken;
use App\Models\UserOrganization;

class XeroController extends Controller
{
    private function getOAuth2()
    {
        // This will use the 'default' app configuration found in your 'config/xero-laravel-lf.php` file.
        // If you wish to use an alternative app configuration you can specify its key (e.g. `new OAuth2('other_app')`).
        return new OAuth2();
    }

    public function redirectUserToXero()
    {
        // Step 1 - Redirect the user to the Xero authorization URL.
        return $this->getOAuth2()->getAuthorizationRedirect();
    }

    public function handleCallbackFromXero(Request $request)
    {
        // Step 2 - Capture the response from Xero, and obtain an access token.
        $accessToken = $this->getOAuth2()->getAccessTokenFromXeroRequest($request);
        
        // Step 3 - Retrieve the list of tenants (typically Xero organisations), and let the user select one.
        $tenants = $this->getOAuth2()->getTenants($accessToken);
        $selectedTenant = $tenants[0]; // For example purposes, we're pretending the user selected the first tenant.
        //dd(collect($accessToken)->toArray());
        // Step 4 - Store the access token and selected tenant ID against the user's account for future use.
        // You can store these anyway you wish. For this example, we're storing them in the database using Eloquent.
        // $user = auth()->user();
        // $user->xero_access_token = json_encode($accessToken);
        // $user->tenant_id = $selectedTenant->tenantId;
        // $user->save();
        //dd($selectedTenant->tenantId);

        foreach($tenants as $key => $tenant){
            //$xeroCheck = DB::table('user_organizations')->where(['tenant_id' => $tenant->tenantId, ''])->first();
            $userOrgCheck = UserOrganization::where(['user_id' => auth()->user()->id, 'tenant_id' => $tenant->tenantId])->first();
            //dd($xeroCheck);
            if(collect($userOrgCheck)->isEmpty()){
                $xero = new XeroApp(
                    new AccessToken(collect($accessToken)->toArray()),
                    $tenant->tenantId
                );
                $userOrg = new UserOrganization();
                // $userOrg = DB::table('user_organizations')->insert([
                //     'user_id' => auth()->user()->id,
                //     'xero_access_token' => json_encode($accessToken),
                //     'tenant_id' => $tenant->tenantId,
                //     'org_name' => $xero->organisations->first()->Name
                // ]);
                $userOrg->user_id = auth()->user()->id;
                $userOrg->xero_access_token = json_encode($accessToken);
                $userOrg->tenant_id = $tenant->tenantId;
                $userOrg->org_name = $xero->organisations->first()->Name;
                $userOrg->save();
                
                DB::table('user_to_organizations')->insert([
                    'user_id' => auth()->user()->id,
                    'org_id' => $userOrg->id,
                    'role' => 'member'
                ]);
                
            }
        }
        return redirect('/home')->with('status', 'Organisation update success!');
    }

    public function refreshAccessTokenIfNecessary(Request $request)
    {
        // Step 5 - Before using the access token, check if it has expired and refresh it if necessary.
        $user = auth()->user();
        $accessToken = new AccessToken(collect(json_decode($request->session()->get('xeroOrg')->xero_access_token))->toArray());
        //dd($request->session()->get('xeroOrg')->tenant_id);
        if ($accessToken->hasExpired()) {
            $accessToken = $this->getOAuth2()->refreshAccessToken($accessToken);

            DB::table('user_organizations')->update([
                'xero_access_token' => json_encode($accessToken)
            ])->where('tenant_id', $request->session()->get('xeroOrg')->tenant_id);
        }
    }
}