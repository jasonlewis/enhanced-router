# Enhanced Router

Enhanced Router is an extension to the Laravel 4 router and provides some enhanced functionality.

[![Build Status](https://travis-ci.org/jasonlewis/enhanced-router.png?branch=master)](https://travis-ci.org/jasonlewis/enhanced-router)

## Installation

Add `jasonlewis/enhanced-router` to `composer.json`.

    "jasonlewis/enhanced-router": "1.0.*"
    
Run `composer update` to pull down the latest version of Enhanced Router. Now open up `app/config/app.php` and add the service provider to your `providers` array.

    'providers' => array(
        'JasonLewis\EnhancedRouter\EnhancedRouterServiceProvider'
    )

That's it. You now have some enhanced functionality available to your routes.

## Usage

### Route Groups

Enhanced Router attempts to make route groups more consistent with that of an actual route. The following functionality is available to your route groups.

- Set `where` requirements on prefixes and domains.
- Use the `before` and `after` methods to apply filters to an entire group.

#### Route Group Prefixes

Let's say you're building an application that has localization support and you're currently prefixing all your routes with something like this.

```php
Route::get('{locale}/about', function($locale)
{

})->where('locale', '(en|fr)');

Route::get('{locale}', function($locale)
{
    return 'Homepage';
})->where('locale', '(en|fr)');
```
    
What happens when you add in localization support for Germany? Two routes wouldn't be so bad but imagine going through dozens of routes and adding "de" to the locale requirement. What a pain.

With Enhanced Router you can apply the prefix as a requirement. Now you have something like this.

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
    
Now the requirement is being set on the group, and all routes within that group will have the prefix applied to them.

#### Subdomain Routing

Laravel 4 makes subdomain routing as easy as pie with route groups. The only problem is you either target a single domain or all subdomains.

```php
Route::group(array('domain' => 'example.laravel.dev'), function()
{

});

Route::group(array('domain' => '{user}.laravel.dev'), function()
{

});
```
    
The first group will match `example.laravel.dev` and the second will match anything. This isn't always ideal. Enhanced Router also allows requirements to be set on the domain exactly like it does with prefixes.

```php
Route::group(array('domain' => '{user}.laravel.dev'), function()
{

})->where('user', '(jason|shawn)');
```
    
Now the group will only match the subdomains `jason.laravel.dev` and `shawn.laravel.dev`. These are regular expressions remember, so you have a lot of power at your fingertips.

#### Filters

Filters can now be applied to a group using the fluent syntax you might be familiar with from routes. The only downside here is that you still need to provide an array as the first parameter to the group.

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

Because of type hinting in the Laravel 4 router it was difficult to remove the blank array from the first parameter. If you aren't using a prefix or subdomain routing then you can use the new `bunch` method.

```php
Route::bunch(function()
{

})->before('auth');
```
    
This method is the same as `group` except you don't have to pass in an array of attributes.

#### Parameter Order

When you're nesting groups within groups that contain parameters it's important to keep in mind the order these parameters are given to nested methods or closures. Consider the following example.

```php
Route::group(array('prefix' => '{locale}'), function()
{
    Route::group(array('domain' => '{user}.laravel.dev'), function()
    {
        Route::get('/', function($locale, $user)
        {
        
        });
    });
});
```
    
Parameters are passed from the outermost group first. So the `$locale` parameter is given first followed by the `$user` parameter. It's always important to remember that as all controller methods will behave in a similar way.

#### Examples

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

## License

Released under the 2-clause BSD. Copyright 2013 Jason Lewis.
