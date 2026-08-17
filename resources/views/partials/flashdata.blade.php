<div class="col-md-12">
    @if (session('success'))
        <div class="alert alert-success alert-dismissable text-center">
            <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong>{{ session('success') }}</strong>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissable text-center">
            <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong>{{ session('error') }}</strong>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissable text-center">
            <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong>{{ session('warning') }}</strong>
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissable text-center">
            <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong>{{ session('info') }}</strong>
        </div>
    @endif
</div>
