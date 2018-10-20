<?php

namespace EloquentPolymorphism\Model\BasicExample;

use EloquentPolymorphism\PolymorphicParent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Person
 *
 * @package EloquentPolymorphism\Model
 *
 * @property integer person_id
 * @property string  name
 * @property string  gender
 * @method static Person find(int $int)
 * @method static Builder where($column_or_columns_or_closure, $operator_or_value = null, $value = null, $boolean = 'and')
 */
class Person extends Model {

    use PolymorphicParent;

    const TYPE_WOMAN = 'f';
    const TYPE_MAN   = 'm';
    public    $timestamps = false;
    protected $table      = 'Person';
    protected $primaryKey = 'person_id';
    protected $casts      = [
        'person_id' => 'integer',
        'name'      => 'string',
        'gender'    => 'string',
    ];

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected function instanceFactory($attributes) {

        if (!array_key_exists('gender', $attributes)) {
            return static::class;
        }

        switch ($attributes['gender']) {
            case self::TYPE_WOMAN:
                return Woman::class;
            case self::TYPE_MAN:
                return Man::class;
        }
        return static::class;
    }

}