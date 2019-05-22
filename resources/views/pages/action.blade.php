@extends($layout)

@section($section)

    <h1>{{$action->label()}}</h1>

    {!! $resource->renderInfo($models) !!}
    
    <div class="the-box">

        @include('ResourceViewer::partials.notifications')

        Run this action over {{$models->count()}} {{strtolower($resource->label())}}<br/><br/>

        {!! $form->render() !!}

    </div>

@endsection