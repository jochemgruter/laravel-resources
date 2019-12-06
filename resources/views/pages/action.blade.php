@extends($layout)

@section($section)

    <h1>{{$action->label()}}</h1>

    <div class="the-box">

        @include('ResourceViewer::partials.notifications')

        Run this action over {{$models->count()}}
        {{strtolower($models->count() == 1 ? $resource->labelSingular() : $resource->label())}}<br/><br/>

        {!! $form->render() !!}

    </div>

@endsection