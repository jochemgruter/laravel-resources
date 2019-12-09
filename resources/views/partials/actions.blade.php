<div class="form-inline" id="action-form" data-resource="{{$resource->name()}}" data-model="{{$model->getKey()}}">

    <input type="hidden" name="__ids[]" value="{{$model->getKey()}}" class="resource-model action-attribute">

    @if(!$hideSelect)
        <select class="form-control">
            <option></option>

            @foreach($actions as $action)

                @if(!$action->displayOnRow)
                    <option value="{{$action->name()}}"><div class="fa {{$action->icon}}"></div> {{$action->label()}}</option>
                @endif

            @endforeach

        </select>

        <button class="btn btn-primary"><div class="fa fa-play"></div> Run</button>

        |
    @endif

    @foreach($actions as $action)

        @if($action->displayOnRow)
            <a href="#" class="btn btn-primary resource-action" data-resource="{{$resource->name()}}" data-action="{{$action->name()}}">
                <div class="fa {{$action->icon}}"></div> {{$action->label()}}
            </a>
        @endif

    @endforeach

    @if($resource->canEdit($model))
        <a href="{{$resource->route('edit', $model->getKey())}}" class="btn btn-primary">
            <div class="fa fa-edit"></div> Edit
        </a>
    @endif

</div>

@include('ResourceViewer::partials.actionForms')