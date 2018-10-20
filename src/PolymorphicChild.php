<?php

namespace EloquentPolymorphism;

use Illuminate\Database\Eloquent\Builder;

trait PolymorphicChild {

    public function __construct($attributes = []) {

        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct($attributes);
        $this->setChildDefaultAttributes();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery() {

        /** @noinspection PhpUndefinedClassInspection */
        $builder = parent::newQuery();
        $builder->withGlobalScope(
            $this->polymorphismScopeIdentifier(), function (Builder $query) {

            $this->polymorphismScope($query);
        }
        );

        return $builder;
    }

    protected function setChildDefaultAttributes() { }

    /**
     * This scope will be added to all requests to unsure not retrieving other child.
     *
     * @param Builder $query
     */
    protected abstract function polymorphismScope(Builder $query);

    /**
     * An identifier that will be used to register the underlying eloquent scope
     *
     * @return string
     */
    protected abstract function polymorphismScopeIdentifier();
}