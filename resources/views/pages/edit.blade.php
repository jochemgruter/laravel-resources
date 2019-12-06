@extends($layout)

@section($section)

    <h1>Edit {{ $resource->labelSingular() }} {{ $model->{$resource::$title} }}</h1>

    <div class="the-box">

        @include('ResourceViewer::partials.notifications')

        {!! $resource->makeUpdateForm($model)->render() !!}

    </div>

@endsection