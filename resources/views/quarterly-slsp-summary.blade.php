@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Quarterly SLSP Summary') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <!-- <excel-uploader></excel-uploader> -->
                    <quarterly-slsp-summary></quarterly-slsp-summary>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
