# simple-multitenancy
 Support Multitenancy for laravel application
    

**Installation**
```
composer require larawire-garage/simple-multitenancy
```


**publish configurations**
```
php artisan vendor:publish --tag=simple-mutitenancy-configs
```

## Usage

call `userBy()` at the end of the Schema create callback function in the migration file
```
Schema::create('users', function (Blueprint $table) {
    // other columns
    
    $table->userBy();
});
```

The `userBy(bool $hasSoftDeletes = true)` function will add following columns to the table
- created_by
- updated_by
- deleted_by [only $hasSoftDeletes == true]

**Inside the Model**
<br>use `HasUserBy` trait in the model which needed multitenancy
```
// app/Models/User.php
class User extends Authenticatable {
    use HasUserBy;
}

// app/Models/Post.php
class Post extends Model {
    use HasUserBy;
}
```

Retrieve User
```
$post->createdBy(); // return App\Models\User|null
$post->updatedBy(); // return App\Models\User|null
$post->deletedBy(); // return App\Models\User|null
```

!!! :tada::tada::tada: Enjoy :tada::tada::tada: !!!
