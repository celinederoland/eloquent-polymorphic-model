<?php

namespace EloquentPolymorphism\Model\BasicExample;

use EloquentPolymorphism\PolymorphicChild;
use Illuminate\Database\Eloquent\Builder;

class Woman extends Person {

    use PolymorphicChild;

    public function setChildDefaultAttributes() {

        $this->gender = 'f';
    }

    /**
     * This scope will be added to all requests to unsure not retrieving other child.
     *
     * @param Builder $query
     */
    protected function polymorphismScope(Builder $query) {

        $query->where('gender', 'f');
    }
}