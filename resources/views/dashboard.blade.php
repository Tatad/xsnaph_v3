@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Import Sales') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                          <strong>{{ session('status') }}</strong>
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                    @endif

                    @if (session('success_status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                          <strong>{{ session('success_status') }}</strong>
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                    @endif
                    <!-- <excel-uploader></excel-uploader> -->

                    <form action="/upload-excel" method="POST" class="form-inline" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="inputPassword6">Quarter Period</label>
                            <select class="form-control mx-sm-3" name="quarter" required>
                                <option value="1">1st Quater</option>
                                <option value="2">2nd Quater</option>
                                <option value="3">3rd Quater</option>
                                <option value="4">4th Quater</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="inputPassword6">Year Selection</label>
                            <!-- <input type="text" class="form-control mx-sm-3" id="datepicker" /> -->
                            <yearpicker></yearpicker>
                        </div>

                        
                        <label class="sr-only" for="inlineFormInputGroupUsername2">Username</label>
                        <div class="input-group mb-2 mr-sm-2">
                            <input type="file" class="custom-file-input" id="customFile" name="excel-file" @change="onFileChange" required>
                            <input type="hidden" value="{{session()->get('xeroOrg')->id}}" name="org_id">
                            <input type="hidden" value="{{session()->get('xeroOrg')->tenant_id}}" name="tenant_id">
                            <label class="custom-file-label" for="customFile" v-cloak>@{{fileName}}</label>
                        </div>

                        <button type="submit" class="btn btn-primary mb-2">Import File</button>
                    </form>
                    
                   
                    <br/>
                    <sales-records></sales-records>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
