<div class="form-horizontal">
    @foreach($form->getFields() as $field)

        <div class="form-group
            {{isset($errors) && $errors->has($field->attribute()) ? 'has-feedback has-error' : ''}}">

            <div class="col-lg-{{$form->columnsLabel}}">

                <label for="" class="">{{$field->label()}}</label>

                @if($field->help != null)
                    <p class="help-block">{{$field->help}}</p>
                @endif
            </div>
            <div class="col-lg-{{12 - $form->columnsLabel}}">

                @if($field->displayOnlyOnUpdate)
                    {{ $field->display($form->model) }}
                @else
                    {!! $field->render() !!}
                @endif

                @if(isset($errors) && $errors->has($field->attribute()))
                    <small class="help-block">{{$errors->first($field->attribute())}}</small>
                @endif
            </div>
        </div>
    @endforeach
</div>