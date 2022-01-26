# Laravel Singleton Routes



This is a package that adds support for Rails/Phoenix's singleton routes in Laravel. Singleton routes, or singular resources are routes that do not need to have an identifier as a context.

Unlike standard resources, singleton routes do not have an index method.

An example for this would be updating the current user's details.

```
show      GET         /my/profile
create    GET         /my/profile/create
store     POST        /my/profile
edit      GET         /my/profile/edit
update    PUT|PATCH   /my/profile
destroy   DELETE      /my/profile
```



## Installation and Usage

Install the package with composer

```
composer require humans/laravel-singleton-routes
```



Register a singleton route.

```php
Route::singleton('profile', PasswordController::class);
```



Register a nested singleton route.

```php
Route::singleton('accounts.suspension', AccountSuspensionController::class)
  ->only('show', 'store', 'destroy');
```



Like other Laravel resources, you will have access to resource methods such as `middleware`, `only`, `except`.  As of the moment, it's not possible to chain it after calling a Route method. `Route::prefix()->singleton()` ***WILL NOT*** work. 



Wrapping the registration in a group will make it work.

```php
Route::prefix('admin')->as('admin.')->group(function () {
  Route::singleton('theme', ThemeController::class)->only('update');
  // url  =>   PUT|PATCH  /admin/theme
  // name =>   admin.theme.update
});
```



