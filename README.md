

# Night Framework

A simple and lightweight framework that gives you more speed . 

<br>

## Install 
```
composer create-project fphpr/fastphp blog
```

<br>
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
For example we enter the `http://yoursite.com/account/login` address in the browser The output will be as follows
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

<br>

# Database

## Get Start

sample for normal use query
```php

$users=DB::select('select * from users where active=?',[true]);

DB::insert('insert into users (name,age,username,password) values (?,?,?,?)',
[
'ben',
25,
'ben25',
Hash::create('123456')
]);

DB::update('...');
DB::delete('...');


```
<br>

### Using Multiple Database Connections
When using multiple connections, you may access each connection via the `in` method  on the DB facade. 
```
//DB::in(database_config_name)

$pages  = DB::in('blog')->select('select * from pages');
$users = DB::in('site')->select('select * from users');

//or in query builder
$count=DB::in('site')->table('users')->count();
```


## Query Builder
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

### union
The query builder also provides a quick way to "union" two queries together. For example, you may create an initial query and use the union method to union it with a second query:
```php
$first = DB::table('users')
            ->whereNull('first_name');

$users = DB::table('users')
            ->whereNull('last_name')
            ->union($first)
            ->get();
```
### count
```php
// ⇒ get count users list
$count_users = DB::table('users')->count(); // return int
```

### sum
```php
// ⇒ get count users list
$score = DB::table('users')->sum(`score`); // return int
```

### join / leftJoin / rightJoin
```php
// => join
$users = DB::table('users')
            ->join('car', 'users.id=car.user_id')
            ->get();

// => left Join
$users = DB::table('users')
            ->leftJoin('posts', 'users.id=posts.user_id')
            ->get();

// right join
$users = DB::table('users')
            ->rightJoin('posts', 'users.id=posts.user_id')
            ->get();
       
  // full Join
$users = DB::table('users')
            ->fullJoin('posts', 'users.id=posts.user_id')
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
#### groupBy / having / duplicate
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

To find fields that contain duplicate information .
The following code will find users whose phone numbers are duplicates
```php
$users = DB::table('users')
                ->duplicate('phone', 2)
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
#### Insert
The query builder also provides an `insert` method for inserting records into the database table. The `insert` method accepts an array of column names and values:
```php
DB::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

 ### Updates

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

### Delete

The query builder may also be used to delete records from the table via the  `delete`method. You may constrain  `delete`  statements by adding  `where`  clauses before calling the  `delete`  method:

```php
DB::table('users')->delete();

DB::table('users')->where('votes', '>', 100)->delete();
```

If you wish to truncate the entire table, which will remove all rows and reset the auto-incrementing ID to zero, you may use the  `truncate`  method:

```php
DB::table('users')->truncate();
```

<br>

## File

### upload

```php
<?php
namespace Controllers;

use App\Web\File;

class fileController{

  // yoursite.com/file/upload
  function uploadAction(){
   $upload=File::upload('myfile');
  }
}
```

#### path / toStorage
save path file location use the `path` method
```php
$upload->path(public_path('imge'));
```
or path to storage
```php
$upload->toStorage('image/products');
```
<br>

#### maxSize / limit_ext / limit_type

##### maxSize(int amount)
The `maxSize ` method is used to limit the file size

```php
$upload->maxSize(250); // limit max size 250 kb 

$upload->maxSize(3 * 1023); // limit max size 3 mg
```

##### limit_ext(array of extension)
By the `limit_ext` method we limit the file to its extension
```php
$upload->limit_ext(['jpg','png','gif']);
```
##### limit_type(array of extension)
By the `limit_type` method we limit the file to its mime type
```php
$upload->limit_type(['image/jpage']);
```

#### rename / randomName

#### rename(string name_file)
The `rename` method is used to save the file with the custom name
```php
$uplaod->rename('my_file_name');

// or change custom file extension
$uplaod->rename('my_file_name','pngo');//my_file_name.pngo
```
#### randomName()
The `randomName` method creates a unique name for the file. The name contains  10 random  number and the [time](https://www.php.net/manual/en/function.time.php) in seconds
sample name: f_25813_38893_158593089589.png
```php
$upload->randomName();
```

### save
The `save` method is used to save the file to the server

```php
$upload->save();
```
#### code sample
```php
<?php
namespace Controllers;

use App\Web\File;

class fileController{

  // yoursite.com/file/upload
  function uploadAction(){
  
   $upload=File::upload('myfile')
   ->limit_ext(['png','jpg'])
   ->maxSize(1024)      //1 mg
   ->toStorage('image') //Storage/image directory
   ->save();            // save file
	
	if($upload->status()==true){
		// Upload Is Done :)
		return['upload'=>true,'name'=>$upload->getFileName()];
	}
	else{
		// Upload Is Failed :(
		$errors=$upload->getErrors();
		return['upload'=>false,'errors'=>$errors];
	}
  }
}
```

#### status 
The `status` method is used to check the file upload status
 if the upload is done correctly Return `true` Otherwise return ` false`
 ```php
 if($upload->status()==true){
	 //Code ...
 }
```

#### getErrors
Get the array of errors
```php
$upload->getErrors();
```
