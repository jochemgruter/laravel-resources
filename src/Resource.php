<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:53
 */

namespace Gruter\ResourceViewer;

use Gate;
use Gruter\ResourceViewer\Actions\Action;
use Gruter\ResourceViewer\Fields\BelongsTo;
use Gruter\ResourceViewer\Fields\Boolean;
use Gruter\ResourceViewer\Fields\Field;
use Gruter\ResourceViewer\Fields\Options;
use Gruter\ResourceViewer\Fields\Text;
use Gruter\ResourceViewer\Filters\Filter;
use Gruter\ResourceViewer\Operators\SimpleOperator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use InvalidArgumentException;
use Mockery\Matcher\Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Resource extends Element
{

    const MODE_UPDATE = 1;
    const MODE_CREATE = 2;

    /**
     * Attribute database name of the title field
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Classpath of the Model related to the Resource
     *
     * @var String
     */
    public static $model;

    /**
     * All available routes of the Resource
     *
     * @var string[]
     */
    protected static $routes = ['index', 'create', 'show', 'edit', 'update', 'store', 'lookup'];

    /**
     * Routes that should not be registered for the Resource
     *
     * @var string[]
     */
    protected static $disabledRoutes = [];

    /**
     * Eager loading relationships for the index query
     *
     * @var string[]
     */
    protected $with = [];

    /**
     * The index query builder
     *
     * @var Builder
     */
    private $query;

    /**
     * Fields of the Resource
     *
     * @var Collection
     */
    private $fields;

    /**
     * Filters of the Resource
     *
     * @var Collection
     */
    private $filters;

    /**
     * Actions of the Resource
     *
     * @var Collection
     */
    private $actions;

    /**
     * Amount of rows for the index table
     *
     * @var int
     */
    private $rows = 25;

    /**
     * Permission for the index page
     *
     * @var Closure|boolean|null
     */
    protected $canSee = null;

    /**
     * Permission for creation and store
     *
     * @var Closure|boolean|null
     */
    protected $canCreate = null;

    /**
     * Permission for the view page
     *
     * @var Closure|boolean|null
     */
    protected $canView = null;

    /**
     * Permission for edit and update
     *
     * @var Closure|boolean|null
     */
    protected $canEdit = null;

    /**
     * The Model fields of the Resource
     *
     * @return Field[]
     */
    abstract protected function fields();

    /**
     * The filters of the Resource.
     *
     * @return Filter[]
     */
    abstract protected function filters();

    /**
     * The actions of the Resource.
     *
     * @return Action[]
     */
    abstract protected function actions();

    /**
     * Function to override to modify the index query
     *
     * @param $query
     */
    protected function buildIndexQuery($query)
    {
        //
    }


    /// controller ///

    /**
     * Index controller.
     *
     * @param  Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('ResourceViewer::pages.index', ['resource' => $this]);
    }

    /**
     * Create controller.
     *
     * @param  Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('ResourceViewer::pages.create', ['resource' => $this]);
    }

    /**
     * Show controller (detail page).
     *
     * @param  Request $request
     * @param  Model $model
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, Model $model)
    {
        return view('ResourceViewer::pages.show', ['resource' => $this, 'model' => $model]);
    }

    /**
     * Edit controller.
     *
     * @param Request $request
     * @param Model $model
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, Model $model)
    {
        return view('ResourceViewer::pages.edit', ['resource' => $this, 'model' => $model]);
    }

    /**
     * Store controller. Validates and save created model.
     *
     * @param  Request  $request
     * @param  Model  $model
     * @param  Validator  $validator
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Model $model, Validator $validator)
    {
        $validator->validate();

        $model->save();

        return redirect()->to(static::route('index'));
    }

    /**
     * Update controller. Validates and save the edited model.
     *
     * @param  Request  $request
     * @param  Model  $model
     * @param  Validator  $validator
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Model $model, Validator $validator)
    {
        $validator->validate();

        $model->save();

        $returnUrl = Session::get('resource.return_url', null);

        return redirect()->to($returnUrl ?? static::route('show', $model->getKey()));
    }

    /// views ///

    /**
     * Render a View with the index table of the Resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function renderIndexTable()
    {
        $query = $this->getQuery();

        //TODO paginate cause two times eager loading
        $paginator = $query->paginate($this->rows, ['*'], static::name().'_page');
        $paginator->appends(request()->all());

        $models = $this->getQueryResult($query);

        $advancedSearch = $this->getFields('searchableAdvanced')->each(function(Field $field){
            if ($field instanceof Boolean || $field instanceof Options)
                $field->defaultEmpty = true;
        });

        return view('ResourceViewer::partials.indexTable', [
                'models' => $models,
                'fields' => $this->getFields('showOnIndex'),
                'resource' => $this,
                'paginator' => $paginator,
                'filters' => $this->getFilters(),
                'advancedSearch' => $advancedSearch,
                'actions' => $this->getActions('multiSelection'),
                'actionsInRow' => $this->getActions('displayOnRow')
        ]);
    }

    /**
     * Render a View of the actions of a particular Model.
     *
     * @param  Model  $model
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function renderActions(Model $model)
    {
        $countSelect = 0;

        $actions = collect($this->getActions())->filter(function($action) use ($model, &$countSelect){
            $canRun = $action->canRun($model);
            $countSelect += $canRun && !$action->displayOnRow ? 1 : 0;
            return $canRun;
        })->toArray();

        return view('ResourceViewer::partials.actions', [
            'resource' => $this,
            'actions' => $actions,
            'model' => $model,
            'hideSelect' => $countSelect == 0
        ]);
    }


    /**
     * Render a View of the details of a Model
     *
     * @param  Model  $model
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function renderDetails(Model $model)
    {
        $fields = $this->getFields('showOnDetail', $model)->each(function(Field $field) use ($model){
            $field->value = $model->{$field->attribute()};
        });

        return view('ResourceViewer::partials.details', ['fields' => $fields, 'model' => $model]);
    }

    /**
     * Initiates a FormBuilder instance with the creation fields
     *
     * @return FormBuilder
     */
    public function makeCreationForm()
    {
        $fields = $this->getFields('showOnCreate');
        $form = new FormBuilder($fields, $this->route('store'));
        $form->actionButton = 'Create';
        return $form;
    }

    /**
     * Initiates a FormBuilder instance with the updates fields
     * contained with values of a particular Model.
     *
     * @param  Model  $model
     * @return FormBuilder
     */
    public function makeUpdateForm(Model $model)
    {
        $fields = $this->getFields('showOnUpdate');
        $form = new FormBuilder($fields, $this->route('update', $model->getKey()));
        $form->method = 'PUT';
        $form->actionButton = 'Update';
        $form->model($model);
        return $form;
    }

    /// builder

    /**
     * Modify the index query so it filters the results that match a particular model
     *
     * @param  Model  $model
     * @param  string|null  $foreignKey
     * @param  string|null  $ownerKey
     * @return $this
     */
    public function related(Model $model, $foreignKey = null, $ownerKey = null)
    {
        $ownerKey = $ownerKey ?? $model->getKeyName();

        $foreignKey = $foreignKey ?? Str::snake(class_basename($model)).'_'.$model->getKeyName();

        $this->getQuery()->where($foreignKey, $model->getAttribute($ownerKey));

        return $this;
    }

    /**
     * Remove a particular field from the resource fields array
     *
     * @param $attribute
     * @return $this
     */
    public function removeField($attribute)
    {
        $this->fields = $this->getFields()->reject(function($field) use ($attribute){
                return $field->attribute == $attribute;
            });
        return $this;
    }

    /**
     * Amount of rows per page in the index table
     *
     * @param $rows integer the default amount of rows per page
     * @return $this
     */
    public function rows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * Hide all actions in the Resource
     *
     * @return $this
     */
    public function hideActions()
    {
        $this->actions = collect();
        $this->canEdit = false;
        $this->canView = false;
        $this->canCreate = false;
        return $this;
    }

    /**
     * Set the permission to create and store a new Model
     *
     * @param  Closure|bool  $callback
     * @return $this
     */
    final public function canCreate($callback = true)
    {
        $this->canCreate = $callback;
        return $this;
    }

    /**
     * Set the permission the view a Model
     *
     * @param  Closure|bool  $callback
     * @return $this
     */
    final public function canView($callback = true)
    {
        $this->canView = $callback;
        return $this;
    }

    /**
     * Set the permission to edit and update a Model
     *
     * @param  bool  $callback
     * @return $this
     */
    final public function canEdit($callback = true)
    {
        $this->canEdit = $callback;
        return $this;
    }

     /// naming ///

    /**
     * The label of the Resource (plural)
     *
     * @return string
     */
    public function label()
    {
        return Str::title(Str::snake(self::name(), ' '));
    }

    /**
     * The label of the Resource (singular)
     *
     * @return string
     */
    public function labelSingular()
    {
        return Str::singular($this->label());
    }

    /**
     * The route uri of the Resource
     *
     * @return mixed
     */
    public static function uri()
    {
        return str_replace('_', '-', snake_case(class_basename(get_called_class())));
    }

    /**
     * Array of all routes to register for this Resource
     *
     * @return array
     */
    public static function routes()
    {
        return array_diff(static::$routes, static::$disabledRoutes);
    }

    /**
     * Get the route url of a particular route type
     *
     * @param  string $type (e.g. index, create, edit, store, update)
     * @param  int  $id
     * @return string
     */
    public function route($type, $id = 0){
        $data = ['resource' => static::uri()];

        if ($id > 0)
            $data['id'] = $id;

        return route('resource.'.$type, $data);
    }


    /// permissions

    /**
     * Authorization for create and store.
     *
     * @return bool
     */
    final public function authorizedToCreate()
    {
        if (is_bool($this->canCreate))
            return $this->canCreate;

        if (is_callable($this->canCreate))
            return call_user_func($this->canCreate);

        return Gate::allows('create', static::$model);
    }

    /**
     * Authorization to view a particular Model
     *
     * @param  Model  $model
     * @return bool
     */
    final public function authorizedToView(Model $model)
    {
        if (is_bool($this->canView))
            return $this->canView;

        if (is_callable($this->canView))
            return call_user_func($this->canView, $model);

        return Gate::allows('view', $model);
    }

    /**
     * Authorization to edit and update a particular Model
     *
     * @param  Model  $model
     * @return bool|mixed|null
     */
    final public function authorizedToEdit(Model $model)
    {
        if (is_bool($this->canEdit))
            return $this->canEdit;

        if (is_callable($this->canEdit))
            return call_user_func($this->canEdit, $model);

        return Gate::allows('edit', $model);
    }


    /// internal

    /**
     * Initiates a new model
     *
     * @return Model
     */
    public function newModel()
    {
        return new static::$model;
    }

    /**
     * Returns if the resource is an PivotResource
     *
     * @return bool
     */
    public function isPivot()
    {
        return $this instanceof PivotResource;
    }

    /**
     * Get a particular field from the Resource
     *
     * @param  string $attribute  name of the database attribute or classpath of the resource
     * @return Field
     */
    public function getField($attribute)
    {
        return $this->getFields()->firstWhere('attribute', $attribute);
    }

    /**
     * Get the fields array of the Resource
     *
     * @param  string|null $criteria
     * @param  string|null $model
     * @return Collection
     */
    public function getFields($criteria = null, $model = null){
        if ($this->fields == null){
            $this->fields = collect($this->fields() ?? [])->filter(function(Field $field){
                $field->setResource($this);
                return $field->authorizedToSee();
            });
        }

        if ($criteria){
            return $this->fields->where($criteria, true)
                ->filter(function($field) use ($criteria, $model){
                    return $field->showUsingCallback($criteria, $model);
                });
        }

        return $this->fields;
    }

    /**
     * Get the filter array of the Resource
     *
     * @return Collection
     */
    public function getFilters()
    {
        if ($this->filters == null) {

            $this->filters = collect($this->filters() ?? [])->filter(function(Filter $filter){
                if ($value = request(static::name() . '_' . $filter->name())) {
                    $filter->value = $value;
                }
                return $filter->authorizedToSee();
            });
        }
        return $this->filters;
    }

    /**
     * Get a particular Action
     *
     * @param  $namee
     * @return Action action
     */
    public function getAction($name){
        foreach($this->getActions() as $action){
            if ($action->name() == $name) {
                $action->setResource($this);
                return $action;
            }
        }
        return null;
    }

    /**
     * Get the actions array of the Resource
     *
     * @param  String $criteria
     * @return Collection actions
     */
    public function getActions($criteria = null){
        if ($this->actions == null) {

            $this->actions = collect($this->actions() ?? [])->filter(function(Action $action){
                $action->setResource($this);
                return $action->authorizedToSee();
            });
        }

        if ($criteria){
            return $this->actions->where($criteria, true);
        }
        return $this->actions;
    }

    /**
     * Find the model of a given id
     *
     * @param $id
     * @return Model
     */
    public function findModel($id){
        return (static::$model)::find($id);
    }

    /**
     * Find the model of a given id. Returns a 404 response if no Model found.
     *
     * @param $id
     * @return Model
     *
     * @throws NotFoundHttpException
     */
    public function findOrFailModel($id)
    {
        if ($model = $this->findModel($id))
            return $model;

        throw new NotFoundHttpException();
    }

    /**
     * Initiates a new index query
     *
     * @return \Illuminate\Database\Eloquent\Builder query
     */
    public function newQuery(){
        $request = request();

        $modelInstance = $this->newModel();
        $query = (static::$model)::query();
        $query->with($this->with);

        foreach($this->getFields('showOnIndex') as $field){
            $table = $field->table ?? $modelInstance->getTable();
            $query->addSelect($table.'.'.$field->attribute());
        }

        if ($request->has('sort_'.static::name())){
            $direction = $request->get('sort_'.static::name().'_direction', 'asc');
            $query->orderBy($request->get('sort_'.static::name()), $direction);
        }

        if ($request->has('search_'.static::name())){
            $query->where(function($q) use ($modelInstance, $request, $query){
                $fields = $this->getFields('searchable');
                $value = $request->get('search_' . static::name());

                foreach($fields as $field){
                    if ($field instanceof BelongsTo){
                        $model = $field->getRelatedModelInstance();

                        $query->leftJoin($model->getTable(), $field->attribute(), '=', $model->getTable().'.'.$model->getKeyName());
                        $q->orWhere($model->getTable().'.'.$field->getForeignTitle(), 'like', '%'.$value.'%');
                    }else {
                        $q->orWhere($modelInstance->getTable().'.'.$field->attribute(), 'like', '%' . $value . '%');
                    }
                }
            });
        }

        $this->getFilters()->where('value', '!=', null)->each(function(Filter $filter) use ($query){
            $filter->apply($query, $filter->value);
        });

        $this->applyAdvancedSearchInQuery($query);
        $this->buildIndexQuery($query);

        return $query;
    }

    /**
     * Apply the advanced searchable parameters to the query
     *
     * @param  $query
     * @return void
     */
    private function applyAdvancedSearchInQuery($query){
        $request = request();

        foreach($this->getFields('searchableAdvanced') as $field){
            $field->advancedSearchValue = $request->get(static::name().'_s_'.$field->attribute());
            $field->advancedSearchOperator = $request->get(static::name().'_o_'.$field->attribute());

            if ($field->advancedSearchValue != null){

                if ($field instanceof Boolean || $field instanceof Options)
                    if ($field->advancedSearchValue == -1){
                        $field->advancedSearchValue = null;
                        continue;
                    }

                if ($field->advancedSearchOperator)
                    $operator = $field->advancedSearchOperators()[$field->advancedSearchOperator - 1];
                else
                    $operator = new SimpleOperator('=');

                $operator->apply($query, $this->newModel()->getTable().'.'.$field->attribute(), $field->advancedSearchValue);
            }
        }
    }

    /**
     * Get the initiated index Query.
     *
     * @return \Illuminate\Database\Eloquent\Builder|Builder
     */
    public function getQuery(){
        if ($this->query == null)
            $this->query = $this->newQuery();

        return $this->query;
    }

    /**
     * Get the result of a query
     *
     * @param $query
     * @return \Illuminate\Support\Collection
     */
    public function getQueryResult($query){
        return $query->get();
    }
}