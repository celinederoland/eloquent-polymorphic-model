<?php

namespace EloquentPolymorphism\Model\BasicExample;

use EloquentPolymorphism\PolymorphicChild;
use Illuminate\Database\Eloquent\Builder;

/**
 */
class Man extends Person {

    use PolymorphicChild;

    public function setChildDefaultAttributes() {

        $this->gender = 'm';
    }

    /**
     * This scope will be added to all requests to unsure not retrieving other child.
     *
     * @param Builder $query
     */
    protected function polymorphismScope(Builder $query) {

        $query->where('gender', 'm');
    }
}