<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:53
 */

namespace Gruter\ResourceViewer;

use Gate;
use Gruter\ResourceViewer\Fields\BelongsTo;
use Gruter\ResourceViewer\Fields\Boolean;
use Gruter\ResourceViewer\Fields\Options;
use Gruter\ResourceViewer\Fields\Text;
use Gruter\ResourceViewer\Operators\SimpleOperator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

abstract class Resource
{

    const MODE_UPDATE = 1;
    const MODE_CREATE = 2;

    public static $title = 'id';

    /**
     * @var String classpath related model of resource
     */
    public static $model;

    /**
     * @var array of relations for eager loading
     */
    protected $with = [];

    /**
     * @var Builder query builder
     */
    private $query;

    /**
     * @var array Field
     */
    private $fields;

    /**
     * @var array Filter
     */
    private $filters;

    /**
     * @var array Action
     */
    private $actions;

    private $rows;

    private $assignModel;

    public $hideActions = false;

    private $pivotTable = null;

    public function __construct()
    {
        $this->rows = config('resource-viewer.index.rows', 25);
    }

    /// setup ///
    public abstract function fields($model);

    public abstract function filters();

    public abstract function actions();

    public function buildIndexQuery($query){

    }


    /// controller ///

    public function index(Request $request){

        return view('ResourceViewer::pages.index', ['resource' => $this]);
    }

    public function create(Request $request){

        return view('ResourceViewer::pages.create', ['resource' => $this]);
    }

    public function show(Request $request, Model $model){

        $fields = $this->getFields('showOnDetail');

        foreach($fields as $field){
            $field->value = $model->{$field->attribute()};
        }

        return view('ResourceViewer::pages.show', ['resource' => $this, 'model' => $model, 'fields' => $fields]);
    }

    public function edit(Request $request, Model $model){


        return view('ResourceViewer::pages.edit', ['resource' => $this, 'model' => $model]);
    }

    public function store(Request $request, Model $model, Validator $validator){

        $validator->validate();

        $model->save();

        return redirect()->to(static::route('index'));
    }

    public function update(Request $request, Model $model, Validator $validator){

        $validator->validate();

        $model->save();

        $returnUrl = Session::get('resource.return_url', null);
        //dd($returnUrl);
        return redirect()->to($returnUrl ?? static::route('show', $model->getKey()));
    }

    /// views ///

    public function renderIndexTable(){
        $query = $this->getQuery();

        $paginator = $query->paginate($this->rows, ['*'], static::name().'_page');
        $paginator->appends(\Illuminate\Support\Facades\Request::all());

        $count = $paginator->total();

        $models = $query->get();

        $advancedSearch = $this->getFields('searchableAdvanced');

        foreach ($advancedSearch as $field) {
            if ($field instanceof Boolean || $field instanceof Options)
                $field->defaultEmpty = true;
        }

        return view('ResourceViewer::partials.indexTable',
            ['count' => $count,
                'models' => $models,
                'fields' => $this->getFields('showOnIndex'),
                'resource' => $this,
                'paginator' => $paginator,
                'filters' => $this->getFilters(),
                'advancedSearch' => $advancedSearch,
                'actions' => $this->getActions('multiSelection'),
                'actionsInRow' => $this->getActions('displayOnRow')]);
    }

    public function renderActions(Model $model){
        $countSelect = 0;

        $actions = collect($this->getActions())->reject(function($action) use ($model, &$countSelect){
            $canRun = $action->authorizedToRun($model);
            $countSelect += $canRun && !$action->displayOnRow ? 1 : 0;
            return !$canRun;
        })->toArray();

        return view('ResourceViewer::partials.actions',
            ['resource' => $this, 'actions' => $actions, 'model' => $model, 'hideSelect' => $countSelect == 0]);
    }

    public function createFormBuilder(){
        $fields = $this->getFields('showOnCreate');
        $form = new FormBuilder($fields, static::route('store'), self::MODE_CREATE);
        return $form;
    }

    public function renderInfo($models){

        $fields = $this->getFields('infoOnUpdate');
        if (empty($fields))
            return '';

        $models = $models->map(function ($item) use ($fields) {
            $fields = collect($fields)->mapWithKeys(function ($it) use ($item){
                return [$it->label() => $it->display($item)];
            });
            return $fields;
        });

        return view('ResourceViewer::partials.info', ['fields' => $fields, 'models' => $models]);
    }

    public function updateFormBuilder(Model $model){
        $fields = $this->getFields('showOnUpdate');
        $form = new FormBuilder($fields, static::route('update', $model->getKey()), self::MODE_UPDATE);
        $form->method = 'PUT';
        $form->model($model);
        return $form;
    }

    /// builder

    public function related(Model $model, $foreignKey = null, $ownerKey = null){
        $ownerKey = $ownerKey ?? $model->getKeyName();

        $foreignKey = $foreignKey ?? Str::snake(class_basename($model)).'_'.$model->getKeyName();

        $this->getQuery()->where($foreignKey, $model->getAttribute($ownerKey));

        return $this;
    }

    public function relatedToPivot(Model $model, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
                                    $fields)
    {
        $this->pivotTable = $table;

        foreach($fields ?? [] as $field)
            $field->table($table);

        $this->fields = $fields;
        $this->fields[] = Text::make(static::$title, static::labelSingular());

        $query = $this->getQuery();

        $query->addSelect($table.'.'.$parentKey);

        $relatedTable = $this->newModel()->getTable();

        $query->join($table, $table.'.'.$relatedPivotKey, '=', $relatedTable.'.'.$relatedKey);
        $query->where($table.'.'.$foreignPivotKey, $model->getAttributes($parentKey));

        return $this;

        // SELECT * FROM brands JOIN brand_customer ON brand_customer.brand_id = brands.id WHERE brand_customers.customer_id = ?
        //                                                            relatedPivotKey                           foreignPivotKey

        // SELECT * FROM products JOIN product_composed ON product_composed.product_id = products.id
    }


    /**
     * @param $rows integer the default amount of rows per page
     * @return $this
     */
    public function rows($rows){
        $this->rows = $rows;
        return $this;
    }

    public function hideActions(){
        $this->hideActions = true;
        return $this;
    }

    public function setActions(array $actions){
        $this->actions  = $actions;
        return $this;
    }

     /// naming ///

    final public static function name(){
        return strtolower(class_basename(get_called_class()));
    }

    public static function label(){
        return Str::title(Str::snake(self::name(), ' '));
    }

    public static function labelSingular(){
        return Str::singular(self::label());
    }

    public static function uri(){
        return str_replace('_', '-', snake_case(class_basename(get_called_class())));
    }

    public static function route($type, $id = 0){
        $data = ['resource' => static::uri()];

        if ($id > 0)
            $data['id'] = $id;

        return route('resource.'.$type, $data);
    }


    /// permissions

    public function canSee(){
        return Gate::allows('index', static::$model);
    }

    public function canCreate(){
        return Gate::allows('create', static::$model);
    }

    public function canView(Model $model){
        return Gate::allows('view', $model);
    }

    public function canEdit(Model $model){
        return Gate::allows('update', $model);
    }


    /// internal

    public function newModel(){
        return new static::$model;
    }

    public function isPivot(){
        return $this->pivotTable != null;
    }

    /**
     * @return array Field
     */
    public function getFields($criteria = null){
        if ($this->fields == null){
            $this->fields = [];
            foreach ($this->fields($this) as $field){
                if ($field->authorizedTosee()){
                    $this->fields[] = $field;
                    $field->setResource($this);
                }
            }
        }

        if ($criteria){
            $fields = collect($this->fields)
                ->where($criteria, true)
                ->toArray();
            return $fields;
        }

        return $this->fields;
    }

    private function getFilters(){
        if ($this->filters == null) {

            $this->filters = [];

            $filters = $this->filters();

            if ($filters != null) {
                foreach ($filters as $filter) {
                    if ($filter->authorizedToSee()) {
                        if ($value = request(static::name() . '_' . $filter->name())) {
                            $filter->value = $value;
                        }
                        $this->filters[] = $filter;
                    }
                }
            }
        }
        return $this->filters;
    }

    public function getAction($name){
        foreach($this->getActions() as $action){
            if ($action->name() == $name)
                return  $action;
        }
        return null;
    }

    private function getActions($criteria = null){
        if ($this->actions == null) {

            $this->actions = [];

            $actions = $this->actions();

            if ($actions != null) {
                foreach ($actions as $action) {
                    if ($action->authorizedToSee()) {
                        $this->actions[] = $action;
                    }
                }
            }
        }
        if ($criteria){
            $fields = collect($this->actions)
                ->where($criteria, true)
                ->toArray();
            return $fields;
        }
        return $this->actions;
    }

    public function newQuery(){
        $request = \Illuminate\Support\Facades\Request::instance();

        $modelInstance = new static::$model;
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
            $query->where(function($query) use ($modelInstance, $request){
                $fields = $this->getFields('searchable');
                $value = $request->get('search_' . static::name());

                foreach($fields as $field){
                    if ($field instanceof BelongsTo){
                        $model = $field->getRelatedModelInstance();

                        $query->leftJoin($model->getTable(), $field->attribute(), '=', $model->getTable().'.'.$model->getKeyName());
                        $query->orWhere($model->getTable().'.'.$field->getForeignTitle(), 'like', '%'.$value.'%');
                    }else {
                        $query->orWhere($modelInstance->getTable().'.'.$field->attribute(), 'like', '%' . $value . '%');
                    }
                }
            });
        }
        foreach($this->getFilters() as $filter){
            if ($filter->value != null)
                $filter->apply($query, $filter->value);
        }

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

                $operator->apply($query, $modelInstance->getTable().'.'.$field->attribute(), $field->advancedSearchValue);
            }
        }
        $this->buildIndexQuery($query);
        return $query;
    }

    public function getQuery(){
        if ($this->query == null)
            $this->query = $this->newQuery();

        return $this->query;
    }
}
