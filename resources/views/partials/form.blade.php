<form method="POST" action="{{$form->action}}" >
    <input type="hidden" name="_method" value="{{$form->method}}" />
    <input type="hidden" name="_token" value="{{csrf_token()}}" />

    @include('ResourceViewer::partials.formFields')

    @if($form->actionButton != null)
        <div class="form-group">
            <div class="col-lg-{{$form->columnsLabel}}"></div>

            <div class="col-lg-{{12 - $form->columnsLabel}}">
                <input type="submit" value="{{$form->actionButton}}" class="btn btn-primary">
            </div>
        </div>
    @endif

</form>