<div class="the-box">

    <div class="form-horizontal">

        @foreach($fields as $field)
            @if($field instanceof \Gruter\ResourceViewer\Fields\Listable)
                @continue
            @endif

            <div class="form-group">
                <div class="col-lg-3">

                    <label for="" class="">{{$field->label()}}</label>

                    @if($field->help != null)
                        <p class="help-block">{{$field->help}}</p>
                    @endif
                </div>
                <div class="col-lg-9">
                    @if($field instanceof \Gruter\ResourceViewer\Fields\BelongsTo)
                        <td><a href="{{$field->getRelatedLink($model)}}">{!! $field->display($model) !!}</a></td>
                    @else
                        {!! $field->display($model) !!}
                    @endif
                </div>
            </div>
        @endforeach

    </div>
</div>

@foreach($fields as $field)
    @if(!($field instanceof \Gruter\ResourceViewer\Fields\Listable))
        @continue
    @endif


    <h2>{{$field->label()}}</h2>

    <div class="the-box">
        {!! $field->display($model) !!}
    </div>

@endforeach