@extends($layout)

@section($section)

    <h1>Create {{$resource->labelSingular()}}</h1>

    <div class="the-box">

        @include('ResourceViewer::partials.notifications')

        {!! $resource->makeCreationForm()->render() !!}

    </div>

@endsection