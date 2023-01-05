@if ($message = Session::get('success'))
    <div class="alert alert-success alert dimissible fade show" role="alert">
        <strong>Yahoo!</strong> {{$message}}
        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($message = Session::get('error'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Whoops!!</strong> {{$message}}
        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
    </div>
@endif