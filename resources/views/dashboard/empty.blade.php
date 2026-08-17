@extends('layouts.app')

@section('content')
<section class="content-header">
    <h1>Dashboard</h1>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-xs-12">
            <div class="box box-solid">
                <div class="box-body text-center">
                    You don't have permission to view the dashboard.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
