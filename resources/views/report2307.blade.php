@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Import 2307') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <!-- <excel-uploader></excel-uploader> -->
                    <form action="/upload-2307" method="POST" class="col-md-12" enctype="multipart/form-data">
                        @csrf
                        <!-- <input type="file" class="form-control" name="excel-file"> -->
                        <div class="row">
                            <div class="col-sm-6 custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="excel-file" @change="onFileChange">
                                <label class="custom-file-label" for="customFile" v-cloak>@{{fileName}}</label>
                            </div>
                            <input type="hidden" value="{{session()->get('xeroOrg')->id}}" name="org_id">
                            <input type="hidden" value="{{session()->get('xeroOrg')->tenant_id}}" name="tenant_id">
                            
                            <div class="col-sm-1 ">
                                <input type="submit" value="Import File" class="btn btn-primary">
                            </div>
                        </div>
                    </form>
                    <br/>
                    <report-2307-summary></report-2307-summary>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
