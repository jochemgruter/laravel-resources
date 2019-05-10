@extends($layout)

@section($section)

    <div class="row">
        <div class="col-lg-7">
            <h1>{{ $resource->labelSingular() }} {{ $model->{$resource::$title} }}</h1>
        </div>
        <div class="col-lg-5 text-right">
            <br/>
            {!! $resource->renderActions($model) !!}
        </div>
    </div>

    <div class="the-box">

        @include('ResourceViewer::partials.notifications')

        <div class="form-horizontal">

            @foreach($fields as $field)

                <div class="form-group">
                    <div class="col-lg-3">

                        <label for="" class="">{{$field->label()}}</label>

                        @if($field->help != null)
                            <p class="help-block">{{$field->help}}</p>
                        @endif
                    </div>
                    <div class="col-lg-9">

                        {!! $field->display($model) !!}

                    </div>
                </div>
            @endforeach

        </div>
    </div>

@endsection