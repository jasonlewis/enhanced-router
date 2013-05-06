# Enhanced Router

Enhanced Router is an extension to the Laravel 4 router and provides some enhanced functionality.

## Installation

Add `jasonlewis/enhanced-router` to `composer.json`.

    "jasonlewis/enhanced-router": "1.0.*"
    
Run `composer update` to pull down the latest version of Enhanced Router. Now open up `app/config/app.php` and add the service provider to your `providers` array.

    'providers' => array(
        'JasonLewis\EnhancedRouter\EnhancedRouterServiceProvider'
    )

That's it. You now have some enhanced functionality available to your routes.

## Features

- Set `where` requirements on route group prefixes and domains.
- Use the `before` and `after` methods to apply filters to an entire route group.
- Set filters to run on an HTTP verb or an array of HTTP verbs.

## Using Enhanced Router

Once you have the package installed and the service provider in your `providers` key you can begin using the features right away.

### Route Group Prefixes

Using the Laravel 4 router there is no way to set a requirement on a prefix. What this means is that prefixes themselves are hard-coded. There are a number of real-world scenarios where being able to use variable prefixes is very useful.

Let's say you're building an application that has localization support and you're currently prefixing all your routes with the locale.

```php
Route::get('{locale}/about', function($locale)
{

})->where('locale', '(en|fr)');

Route::get('{locale}', function($locale)
{
    return 'Homepage';
})->where('locale', '(en|fr)');
```

For a small application this might suffice. But once your application gets quite large it can become a bit of a smell. And when it comes time to add another language you'll need to go through all the routes and add the language.

Using Enhanced Router you can set the requirement itself on the group. This means you only need to define the requirement once, and adding languages in the future isn't so painful.

```php
Route::group(array('prefix' => '{locale}'), function()
{
    Route::get('about', function($locale)
    {
    
    });
    
    Route::get('/', function($locale)
    {
        return 'Homepage';
    });
})->where('locale', '(en|fr)');
```

#### Parameters

It's important to note that the locale is actually given to each route as a **parameter**. The parameter is also given to every method of every controller that is within the group. When your route requires a parameter of its own it will be given after the prefix parameter.

### Subdomain Routing

Using route groups in Laravel 4 you can specify the domain the group responds to. This is especially helpful when you want to route to a subdomain. Currently you can only route to a single subdomain or every subdomain.

```php
Route::group(array('domain' => 'example.laravel.dev'), function()
{

});

Route::group(array('domain' => '{user}.laravel.dev'), function()
{

});
```
    
The first group will match `example.laravel.dev` and the second will match any subdomain. Using the exact same syntax as prefixes you can also set the requirement on the subdomain.

```php
Route::group(array('domain' => '{user}.laravel.dev'), function()
{

})->where('user', '(jason|shawn)');
```
    
Now the group will only match the subdomains `jason.laravel.dev` and `shawn.laravel.dev`.

### Filters

#### Route Groups

Filters can now be applied to a group using the fluent syntax you might be familiar with from routes. The only thing to be aware of here is that you still need to provide an array as the first parameter to the group.

```php
Route::group(array(), function()
{
    
})->before('auth');
```
    
All filters in the group will now have the `auth` filter applied to them. When you have nested groups with filters applied to them the outermost filters are applied first since they are actually defined first.

```php
Route::group(array(), function()
{
    Route::group(array(), function()
    {
    
    })->before('csrf');
})->before('auth');
```
    
The above example would trigger the `auth` filter first and then move on to the `csrf` filter if the matched route was within that group.

Because of type hinting in the Laravel 4 router it's difficult to remove the empty array from the first parameter. If you aren't using a prefix or subdomain routing then you can use the new `bunch` method.

```php
Route::bunch(function()
{

})->before('auth');
```
    
This method is the same as `group` except you don't have to pass in an array as the first parameter.

#### HTTP Verbs

Enhanced Router allows you to apply filters to all routes for specific HTTP verbs. Consider an application where all `POST` requests require the `csrf` filter.

```php
Route::on('post', 'csrf');
```

Or you can use an array of verbs.

```php
Route::on(['post', 'put'], 'csrf');
```

You can also use an array of filters to apply.

```php
Route::on(['post', 'put'], ['csrf', 'auth']);
```

### More Examples

This example shows how you can nest groups and use filters, domains, and prefixes all at once.

```php
Route::group(array('prefix' => '{locale}'), function()
{
    Route::controller('auth', 'AuthController');
    
    Route::group(array(), function()
    {
        Route::get('/', 'UserController@profile');
        
        Route::group(array('domain' => 'admin.laravel.dev'), function()
        {
            Route::controller('/', 'AdminController');
            
            Route::resource('posts', 'AdminPostsController');
        });
    })->before('auth');
})->where('locale', '(en|fr)');
```

## Changes

#### v1.0.1
- Allow an array to be given as the expression and have it converted to a proper regular expression.
- Added `on` method to apply filters on a given HTTP verb.

#### v1.0.0

- Initial release.

## License

Released under the 2-clause BSD. Copyright 2013 Jason Lewis.