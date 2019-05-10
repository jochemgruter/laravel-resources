<form method="POST" action="{{$form->action}}" class="form-horizontal">
    <input type="hidden" name="_method" value="{{$form->method}}" />
    <input type="hidden" name="_token" value="{{csrf_token()}}" />

    @foreach($form->getFields() as $field)

        <div class="form-group {{$errors->has($field->attribute()) ? 'has-feedback has-error' : ''}}">
            <div class="col-lg-3">

                <label for="" class="">{{$field->label()}}</label>

                @if($field->help != null)
                    <p class="help-block">{{$field->help}}</p>
                @endif
            </div>
            <div class="col-lg-9">

                @if($field->displayOnlyOnUpdate && $form->mode == \Gruter\ResourceViewer\Resource::MODE_UPDATE)
                    {{ $field->display($form->model) }}
                @else
                    {!! $field->render() !!}
                @endif

                @if($errors->has($field->attribute()))
                    <small class="help-block">{{$errors->first($field->attribute())}}</small>
                @endif
            </div>
        </div>
    @endforeach

    @if($form->actionButton != null)
        <div class="form-group">
            <div class="col-lg-3"></div>

            <div class="col-lg-9">
                <input type="submit" value="{{$form->actionButton}}" class="btn btn-primary">
            </div>
        </div>
    @endif

</form>