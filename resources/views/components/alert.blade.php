@if($message = Session::get('error'))
    <div class="alert-warning alert-dismissible fade show" role="alert">
        <strong> Whoops!</strong> {{$message}}

        <button type="button" class="btn-close" data-dismiss="alert" aria-label="close"></button>
    </div>
@endif