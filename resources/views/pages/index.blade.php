@extends($layout)

@section($section)

    <h1>{{$resource->label()}}</h1>

    <div class="the-box">

        @include('ResourceViewer::partials.notifications')

        {!! $resource->renderIndexTable() !!}

    </div>

@endsection