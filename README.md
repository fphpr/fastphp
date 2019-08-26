
# Fast PHP Framework

A simple and lightweight framework that gives you more speed . 

<br>

## Install 
```
composer create-project fphpr/fastphp blog
```

<br>


# Fast PHP Framework

A simple and lightweight framework that gives you more speed . 

<br>

## Install 
```
composer create-project fphpr/fastphp blog
```

<br>

## The Basics

### Input Value
```php
// get Requests
$name = get('name','Default value');

// post Requests
$name = post('name');
$age = post('age',18);
```
<br>

### Path Helper
```php
root_path();   // root project directory path
public_path(); // public directory path
app_path();    // app directory path
storage_path(); // storage directory path
views_path();   // views directory path
```
fast php framework sample usage in path helper
```php
$app_path=app_path('Storage/text.txt');

echo $app_path; 
//output sample : var/www/site/html/app/Storage/text.txt
```
<br>

### Url Helper
#### url
To get and use the url address
```php
url();// yoursite.com
url('account/login'); // yoursite.com/account/login
```
<br>

#### route_url / params_url
Get route address
For example we enter the 'http://yoursite.com/account/login' address in the browser The output will be as follows
```php
$route=route_url();
//output: account/login

$route=params_url();
// output array:['account','login']
```
<br>

### method / isGet / isPost

```php
//Returns the REQUEST METHOD type
$request = method();  // POST or GET

//If the REQUEST METHOD of the get type returns true
if(isGet()){
  // code
}

//If the REQUEST METHOD of the post type returns true
if(isPost()){
  // post
}

```

## Basic Controllers
pageController.php
```php
<?php
namespace Controllers;

class pageController{

  // yoursite.com/page
  function Action(){
    return view('html_name');
  }

  // yoursite.com/page/id
  function idAction(){
   $id=get('id',0);
   return $id;
  }
}
```

## Query Builer
Fast Php database query builder provides a convenient, fluent interface to creating and running database queries.

```php
<?php
namespace Controllers;
use App\Web\DB;

class pageController{

  // yoursite.com/page/get-list
  function get_listAction(){
  
   $pages=DB::table('page')->get();
   return $pages;
   
  }
}
```

### select
```php
$user = DB::table('users')->where('name', 'John')->first();
echo $user->name;

// ⇒ find user by id field
$user = DB::table('users')->find(20);

//support other functions:
// where / orWhere
// whereBetween / orWhereBetween
// whereNotBetween / orWhereNotBetween
// whereIn / whereNotIn / orWhereIn / orWhereNotIn
// whereNull / whereNotNull / orWhereNull / orWhereNotNull
// whereDate / whereMonth / whereDay / whereYear / whereTime
// whereColumn / orWhereColumn
```

### count
```php
// ⇒ get count users list
$count_users = DB::table('users')->count(); // return int
```

### join / leftJoin / rightJoin
```php
// => join
$users = DB::table('users')
            ->join('car', 'users.id=car.user_id')
            ->get();

// => leftJoin
$users = DB::table('users')
            ->leftJoin('posts', 'users.id=posts.user_id')
            ->get();

$users = DB::table('users')
            ->rightJoin('posts', 'users.id=posts.user_id')
            ->get();
```

### orderBy
The `orderBy` method allows you to sort the result of the query by a given column.
```php
$users = DB::table('users')
                ->orderBy('name', 'desc')
                ->get();
```
#### latest / oldest
The `latest` and `oldest` methods allow you to easily order results by date. By default, result will be ordered by the `created_at` column. Or, you may pass the column name that you wish to sort by:
```php
$user = DB::table('users')
                ->latest()
                ->first();
```

#### inRandomOrder
The  `inRandomOrder`  method may be used to sort the query results randomly. For example, you may use this method to fetch a random user:
```php
$randomUser = DB::table('users')
                ->inRandomOrder()
                ->first();
```
#### groupBy / having
The  `groupBy`  and  `having`  methods may be used to group the query results. The  `having`method's signature is similar to that of the  `where`  method:

```php
$users = DB::table('users')
                ->groupBy('account_id')
                ->having('account_id', '>', 100)
                ->get();
```

You may pass multiple arguments to the  `groupBy`  method to group by multiple columns:

```php
$users = DB::table('users')
                ->groupBy('first_name', 'status')
                ->having('account_id', '>', 100)
                ->get();
```
#### skip / take

To limit the number of results returned from the query, or to skip a given number of results in the query, you may use the  `skip`  and  `take`  methods:

```php
$users = DB::table('users')->skip(10)->take(5)->get();
```

Alternatively, you may use the  `limit`  and  `offset`  methods:

```php
$users = DB::table('users')
                ->offset(10)
                ->limit(5)
                ->get();
```
### Insert
The query builder also provides an `insert` method for inserting records into the database table. The `insert` method accepts an array of column names and values:
```php
DB::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

 ## Updates

In addition to inserting records into the database, the query builder can also update existing records using the  `update`  method. The  `update`  method, like the  `insert`  method, accepts an array of column and value pairs containing the columns to be updated. You may constrain the  `update`  query using  `where`  clauses:

```php
DB::table('users')
            ->where('id', 1)
            ->update(['votes' => 1]);
```



The query builder also provides convenient methods for incrementing or decrementing the value of a given column. This is a shortcut, providing a more expressive and terse interface compared to manually writing the  `update`  statement.

Both of these methods accept at least one argument: the column to modify. A second argument may optionally be passed to control the amount by which the column should be incremented or decremented:

```php
DB::table('users')->increment('votes');

DB::table('users')->increment('votes', 5);

DB::table('users')->decrement('votes');

DB::table('users')->decrement('votes', 5);
```

## Delete

The query builder may also be used to delete records from the table via the  `delete`method. You may constrain  `delete`  statements by adding  `where`  clauses before calling the  `delete`  method:

```php
DB::table('users')->delete();

DB::table('users')->where('votes', '>', 100)->delete();
```

If you wish to truncate the entire table, which will remove all rows and reset the auto-incrementing ID to zero, you may use the  `truncate`  method:

```php
DB::table('users')->truncate();
```
