@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- __('You are logged in!') --}}
                    <a href="/manage/xero"><button class="btn btn-primary">Sync xero accounts</button></a>
                    <br/><br/>
                    <register-organisation></register-organisation>
                        
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
