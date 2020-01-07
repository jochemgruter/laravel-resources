@foreach($actions as $action)
    @if($action->hasForm())
        <div class="action-form-dialog" data-action="{{$action->index()}}" title="{{$action->label()}}"
             data-autoOpen="{{Session::has('actionFailed') && Session::get('actionFailed') == $action->index() ?
                                    'true' : 'false'}}">

            {!! $action->makeForm()->render() !!}

        </div>

    @endif
@endforeach