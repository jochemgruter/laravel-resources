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

    @include('ResourceViewer::partials.notifications')

    {!! $resource->renderDetails($model) !!}

@endsection