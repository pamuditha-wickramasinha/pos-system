@extends('layouts.app')
@php($activeMenu = 'printers')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>{{ $printer->name }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('printers.index') }}">Printers List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Set up the Print Agent on the counter PC</h3>
                </div>
                <div class="box-body">
                    @if ($printer->connection_type !== 'local_agent')
                        <div class="callout callout-warning">
                            <p><strong>{{ $printer->name }}</strong> is a Network printer, so it does not use the
                            Print Agent &mdash; the server prints to it directly over the network.</p>
                        </div>
                    @else
                        <ol>
                            <li>Copy the <code>agent</code> folder to the counter PC, e.g. <code>C:\pos-agent</code>.</li>
                            <li>Create a file called <code>agent-config.json</code> inside it, containing exactly this:</li>
                        </ol>

                        <pre id="agent_config" style="padding:15px;">{{ $config }}</pre>

                        <button type="button" class="btn btn-primary btn-sm" onclick="copy_agent_config()">
                            <i class="fa fa-copy"></i> Copy to clipboard
                        </button>

                        <div class="callout callout-danger" style="margin-top:15px;">
                            <h4>Keep this token private</h4>
                            <p>Anyone holding it can queue and read print jobs for this printer. It is
                            per-printer &mdash; do not reuse it on another till, give that printer its own record.</p>
                        </div>

                        <ol start="3">
                            <li>Edit <code>Start POS.cmd</code> and set <code>POS_URL</code> to
                                <code>{{ rtrim(url('/'), '/') }}/pos</code>.</li>
                            <li>Double-click <code>Start POS.cmd</code>. The agent window should say
                                <em>Connected. Printing for '{{ $printer->name }}'.</em></li>
                            <li>Come back here and use <strong>Test Print</strong> on the printers list.</li>
                        </ol>

                        <p class="help-block">
                            The agent only makes outbound requests to this server, so nothing needs to be
                            opened or forwarded on the shop's network.
                        </p>
                    @endif
                </div>
                <div class="box-footer">
                    <a href="{{ route('printers.index') }}" class="btn btn-warning">Back to Printers</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function copy_agent_config() {
    var text = document.getElementById('agent_config').innerText;

    // Older browsers (and any non-secure context) have no navigator.clipboard, so fall
    // back to selecting the block and letting the user copy it themselves.
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () {
            toastr['success']('Copied. Paste it into agent-config.json.');
        }, function () {
            toastr['warning']('Could not copy automatically - select the text and copy it.');
        });
        return;
    }

    var range = document.createRange();
    range.selectNode(document.getElementById('agent_config'));
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
    toastr['info']('Selected - press Ctrl+C to copy.');
}
</script>
@endpush
