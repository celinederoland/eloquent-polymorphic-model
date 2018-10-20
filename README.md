# Eloquent extension for polymorphic models
Extend eloquent facilities to map one class Hierarchy branch to one table in database.

## Purpose 
If you have one table on database (let say a `Person` table with fields `person_id`, `name` and `gender`) and you want to map it with your class hierarchy (let say a parent class `Person` and 2 childs `Man extends Person` and `Woman extends Person`), then you can use this package to make the mapping automatically.

## Configure class mapping 

### Retrieving instances from the parent class

In parent class, define the Model as usual :
```php
class Person extends Model {

    protected $table      = 'Person';
    protected $primaryKey = 'person_id';
    
}
```

Define empty children classes :

```
class Man extends Person {}

class Woman extends Person {}
```

In parent class use the `EloquentPolymorphism\PolymorphicParent` helper
You must define how database results will be bound with your class hierarchy (in my example it depends on the `gender` field value)
```php
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
```

With that you can retrieve a collection of Men and Women :
```php
$persons = Person::all(); //an eloquent collection, containing instances of `Man` and instances of `Woman`
```

### Retrieving instances from children classes

Now we must define constraints in child classes (otherwise `Man::all()` would also retrieve a collection of men and women).
For that in children classes you have to use the trait `EloquentPolymorphism\PolymorphicChild` and define the `polymorphismScope` constraint ;

```php
class Man extends Person {

    * This scope will be added to all requests to make sure not retrieving other child.
     *
     * @param Builder $query
     */
    protected function polymorphismScope(Builder $query) {
    
        $query->where('gender', 'm');
    }
}
``` 

Now if you write `Man::all()` or any more complex query on `Man` model it will result on a collection of `Man` instances, corresponding to the table entries which represent men.

Optionally, you can overwrite the name of the scope you just defined in `Man` class adding this code either in parent or child class :

```php
protected function polymorphismScopeIdentifier() {

    return 'polymorphism_scope_identifier';
}
```


## Updating/Creating model :

### default attributes

It is strongly recommended to define default attributes values in children classes. 

In our example it would be comfortable to write : 
```php
$woman = new Woman(['name' => 'Sandra']); 
$woman->save();
``` 
without having to set her gender. 

For this purpose, you must overwrite method `setChildDefaultAttributes` in children classes

```php
public function setChildDefaultAttributes() {
      
     $this->gender = 'f';
}
```

### verifications on save method call

The trait `PolymorphicParent` prevents unnatural update/create on children like as example :

```php
$man = new Man();
$man->gender = 'f';
$man->save(); //returns false, entry is not saved
```

This is done by checking that the conditions defined in `instanceFactory` method would effectively retrieve an instance of `Man`.
You can overwrite this behaviour by implementing the method `checkHierarchyConstraintsBeforeSaving` 

```php
class Man extends Person {
  
  /**
   * @return bool
   */
  protected function checkHierarchyConstraintsBeforeSaving() {
  
      //Your logic : return true if it's correct to consider this instance as beeing a man, false otherwise
  }
}
```

## Complex queries, relations, etc.

You can use all other functionality of Eloquent models like usual. In particular, you can define relations and complex queries as needed.

# Contribute

Fork the project in your github account

Composer install
````bash
sh composer.sh install
````

Test
```bash
docker-compose up testunit
```

