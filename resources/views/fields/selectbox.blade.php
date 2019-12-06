
<select name="{{$attribute}}" class="form-control">

    @foreach($options as $v => $label)
        <option value="{{$v}}" {{$value == $v ? 'selected' : ''}}>{{$label}}</option>
    @endforeach

</select>