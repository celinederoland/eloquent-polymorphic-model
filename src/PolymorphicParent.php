<?php

namespace EloquentPolymorphism;

use Illuminate\Database\Eloquent\Model;

trait PolymorphicParent {

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected abstract function instanceFactory($attributes);
    
    /**
     * @return bool
     */
    protected function checkHierarchyConstraintsBeforeSaving() {

        $guessFinalClass = $this->instanceFactory($this->getAttributes());
        return ($guessFinalClass === static::class || is_subclass_of($guessFinalClass, static::class));
    }
    
    /**
     * Give a name to the scope used to retrieve children instances from database
     */
    protected function polymorphismScopeIdentifier() {

        return 'polymorphism_scope_identifier';
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array       $attributes
     * @param  string|null $connection
     *
     * @return Model
     */
    public function newFromBuilder($attributes = [], $connection = null) {

        $class_name    = $this->instanceFactory((array) $attributes);
        /** @var Model $model */
        $model         = new $class_name();
        $model->exists = true;

        /** @noinspection PhpUndefinedMethodInspection */
        $model->setRawAttributes((array) $attributes, true);

        /** @noinspection PhpUndefinedClassInspection */
        $model->setConnection($connection ?: $this->getConnectionName());

        return $model;
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array $attributes
     * @param  bool  $exists
     *
     * @return Model
     */
    public function newInstance($attributes = [], $exists = false) {

        $class_name    = $this->instanceFactory((array) $attributes);
        $model         = new $class_name();
        $model->exists = $exists;
        return $model;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function save($options = []) {
        
        if($this->checkHierarchyConstraintsBeforeSaving()) {
            /** @noinspection PhpUndefinedClassInspection */
            return parent::save($options);
        }
        return false;
    }

}