<div class="the-box">
    @foreach ($models as $k=>$model)
        @foreach ($model as $key=>$value)
            <div class="row">
                <div class="col-sm-4"><p>{{ $key }}</p></div>
                <div class="col-sm-4"><p>{{ $value }}</p></div>
            </div>
        @endforeach
    @endforeach
</div>
