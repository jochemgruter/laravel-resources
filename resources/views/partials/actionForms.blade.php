@foreach($actions as $action)
    @if($action->hasForm())
        <div class="action-form-dialog" data-name="{{$action->name()}}" title="{{$action->label()}}"
             data-autoOpen="{{Session::get('actionFailed') == $action->name() ? 'true' : 'false'}}">

            {!! $action->makeForm()->render() !!}

        </div>
    @else
        <div class="action-form" data-name="{{$action->name()}}">
            {!! $action->makeForm()->render() !!}
        </div>
    @endif
@endforeach