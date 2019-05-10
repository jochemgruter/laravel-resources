
<div class="form-inline lookup-field" data-resource="{{$resourceUri}}">
    <div class="input-group full-width">
        <div class="input-group-addon lookup-button"><div class="fa fa-search"></div></div>
        <input type="text" class="form-control relation-field" name="{{$attribute}}" value="{{$value}}">
        <div class="input-group-addon">

            <img src="/vendor/resource-viewer/loading.gif" class="loading-img"/>

            <span class="related-value">
                {{$field->getRelatedValue($value)}}
            </span>
        </div>
    </div>
</div>
