<div class="index-table">
    <nav class="navbar navbar-default table-navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">{{$resource->label()}}</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    @if($resource->isPivot() && $resource->authorizedToAssign())
                        <li>
                            <a href="javascript:void(0);" class="assign-button" data-uri="{{$resource->name()}}">
                                <div class="fa fa-exchange"></div>
                                Toewijzen
                            </a>
                        </li>
                    @endif

                    @if($resource->authorizedToCreate())
                        <li>
                            <a href="{{$resource->route('create')}}">
                                <div class="fa fa-plus"></div>
                                Toevoegen
                            </a>
                        </li>
                    @endif
                    @if(count($actions) > 0)
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <div class="fa fa-play"></div>
                                Handelingen <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                @foreach($actions as $action)
                                    <li>
                                        <a href="#" class="resource-action" data-resource="{{$resource->name() }}" data-action="{{$action->index()}}">
                                            <div class="fa {{$action->icon}}"></div> &nbsp;&nbsp; {{$action->label()}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                </ul>
                <form class="navbar-form navbar-right" method="get">
                    @foreach(Request::except(['search_'.$resource->name(), $resource->name().'_page']) as $key => $value)
                        <input type="hidden" name="{{$key}}" value="{{$value}}" />
                    @endforeach
                    <div class="form-group">

                        <input type="text" class="form-control" name="{{'search_'.$resource->name()}}"
                               value="{{request('search_'.$resource->name())}}" placeholder="Search">
                    </div>
                    <button type="submit" class="btn btn-default">Submit</button>
                </form>
                <ul class="nav navbar-nav navbar-right">

                    @if(count($advancedSearch) > 0)
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                               role="button" aria-haspopup="true" aria-expanded="false">
                                <div class="fa fa-search"></div>
                                Geavanceerd zoeken
                                @php
                                    $advancedSearchActive = collect($advancedSearch)
                                                 ->where('advancedSearchValue', '!=', null)->count();
                                @endphp
                                @if($advancedSearchActive > 0)
                                    <span class="badge">{{$advancedSearchActive}}</span>
                                @endif
                                <span class="caret"></span>
                            </a>
                            <div class="advanced-search dropdown-menu">
                                <form method="get" class="form-horizontal">
                                    <div class="advanced-search-fields">
                                        @foreach($advancedSearch as $field)
                                            @php
                                                $operators = $field->advancedSearchOperators();
                                                $ignoreUrlQuery[] = $resource->name().'_s_'.$field->attribute();
                                                $ignoreUrlQuery[] = $resource->name().'_o_'.$field->attribute();
                                            @endphp
                                            <div class="row">
                                                <label class="col-lg-4 text-right control-label">{{$field->label()}}:</label>
                                                <div class="col-lg-2">
                                                    @if($operators != null && count($operators) > 0)
                                                        <select class="form-control" name="{{$resource->name().'_o_'.$field->attribute()}}">
                                                            @foreach($field->advancedSearchOperators() as $key => $operator)
                                                                <option value="{{$key+1}}" {{$key+1 == $field->advancedSearchOperator ? 'selected' : ''}}>
                                                                    {{$operator->value()}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                </div>
                                                <div class="col-lg-6">
                                                    {!! $field->render($resource->name().'_s_'.$field->attribute(), $field->advancedSearchValue) !!}
                                                </div>
                                            </div>
                                        @endforeach
                                        @foreach(Request::except(array_merge([$resource->name().'_page'], $ignoreUrlQuery)) as $key => $value)
                                            <input type="hidden" name="{{$key}}" value="{{$value}}" />
                                        @endforeach
                                    </div>
                                    <button class="btn btn-primary col-lg-12">Filter</button>
                                </form>
                            </div>
                        </li>
                    @endif

                    @if(count($filters) > 0)
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <div class="fa fa-filter"></div>
                                Filter
                                @php
                                    $filterActive = collect($filters)->where('value', '!=', null)->count();
                                @endphp
                                @if($filterActive > 0)
                                    <span class="badge">{{$filterActive}}</span>
                                @endif
                                <span class="caret"></span>
                            </a>
                            <div class="filter {{count($filters) > 4 ? 'filter-columns-2' : ''}} dropdown-menu">
                                <form method="get">
                                    @foreach($filters as $filter)
                                        @php
                                            $ignoreUrlQuery[] = $resource->name().'_'.$filter->name();
                                        @endphp
                                        <div class="panel-default {{count($filters) > 4 ? 'col-lg-6' : ''}}">
                                            <div class="panel-heading">
                                                {{$filter->label()}}:
                                            </div>
                                            <div class="panel-body">
                                                <select name="{{$resource->name().'_'.$filter->name()}}" class="form-control">
                                                    <option></option>
                                                    @foreach($filter->options() as $key => $option)
                                                        <option value="{{$key}}" {{$filter->value == $key ? 'selected' : ''}}>{{$option}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endforeach
                                    @foreach(Request::except(array_merge([$resource->name().'_page'], $ignoreUrlQuery)) as $key => $value)
                                        <input type="hidden" name="{{$key}}" value="{{$value}}" />
                                    @endforeach
                                    <button class="btn btn-primary col-lg-12">Filter</button>
                                </form>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

     <div class="table-responsive">
         <form method="post" action="{{route('resources.action', ['resource' => $resource->uri()]).
               '?'.request()->getQueryString()}}" id="actionForm">
             @csrf

             <input type="hidden" name="__action" value="" class="action-attribute">

            <table class="table table-th-block table-hover">
                <thead>
                <tr>
                    @if(count($actions) > 0)
                        <td class="dropdown">
                            @if($paginator->total() > $models->count())

                                <input type="checkbox" role="button" id="dropdownMenuLink"
                                       class="resource-model select-all-dummy" data-toggle="dropdown">

                                <div  class="caret"></div>

                                <div class="dropdown-menu with-triangle select-all-menu">
                                    <form class="container" style="width:300px;">
                                        <div class="form-check p-2">
                                            <input type="checkbox" class="resource-model" id="selectAll">
                                            <label for="selectAll">Select all</label>
                                        </div>
                                        <div class="form-check p-2">
                                            <input type="checkbox" name="__allMatching" class="form-check-input action-attribute" id="selectAllMatching"
                                            {{old('__allMatching') != null ? 'checked' : ''}}>
                                            <label for="selectAllMatching" class="form-check-label">
                                                Select all matching ({{$paginator->total()}})
                                            </label>
                                        </div>
                                    </form>
                                </div>

                            @else
                                <input type="checkbox" id="selectAll">
                            @endif

                        </td>
                    @endif
                    @foreach($fields as $field)
                        <th>
                            @if($field->sortable)
                                <a href="{{$field->sortUrl()}}">
                                    {{$field->label()}}
                                    <div class="fa fa-sort"></div>
                                </a>
                            @else
                                {{$field->label()}}
                            @endif

                        </th>
                    @endforeach
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($models as $model)
                    <tr class="resource-row" data-id="{{$model->getKey()}}" data-name="{{$model->{$resource::$title} }}">
                        @if(count($actions) > 0)
                            <td>
                                <input type="checkbox"
                                       name="__ids[]"
                                       class="resource-model action-attribute"
                                       value="{{$model->getKey()}}"
                                       {{in_array($model->getKey(), old('__ids', [])) ? 'checked' : ''}}>
                            </td>
                        @endif
                        @foreach($fields as $field)
                                <td>{!! $field->display($model)  !!}</td>
                        @endforeach
                        <td class="actions text-right">
                            @foreach($actionsInRow as $action)
                                @if($action->authorizedToRun($model))
                                    <a href="#" class="resource-action single"
                                       data-model="{{$model->getKey()}}"
                                       data-action="{{$action->index()}}"
                                       data-resource="{{$resource->name() }}">
                                            <div class="fa {{$action->icon}}"></div>&nbsp;
                                            @if($action->displayOnRowWithLabel)
                                                {{$action->label()}}
                                            @endif
                                    </a>
                                @endif
                            @endforeach

                            @if($resource->authorizedToEdit($model))
                                <a href="{{$resource->route('edit', $model->getKey()) }}"><div class="fa fa-edit"></div></a>
                            @endif
                            @if($resource->authorizedToView($model))
                                <a href="{{$resource->route('show', $model->getKey()) }}"><div class="fa fa-eye"></div></a>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
         </form>
    </div>

    <div class="index-bottom">
        <div class="left">
            {{$paginator->total()}} rows
        </div>
        <div class="right">
            {{ $paginator }}
        </div>
    </div>
</div>

@if($resource->isPivot() && $resource->authorizedToAssign())
    <div class="assign-form-dialog" title="Assign {{$resource->labelSingular()}}"
         data-uri="{{$resource->name()}}">
        {!! $resource->makeAssignForm()->render() !!}
    </div>
@endif

@include('ResourceViewer::partials.actionForms', ['actions' => $resource->getActions()])