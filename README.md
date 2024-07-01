# Laravel Usage Limiter

[![Tests](https://github.com/NabilHassen/laravel-usage-limiter/actions/workflows/tests.yml/badge.svg)](https://github.com/NabilHassen/laravel-usage-limiter/actions/workflows/tests.yml)

## Introduction

A Laravel package to track, limit, & restrict usages of users, accounts, or any other model.

## Features

- Define usage limits you need for your app per plan
- Set reset frequency for each of your limits
- Attach usage limits to models (e.g. User model)
- Consume and unconsume usage limits whenever a resource is created or deleted
- Get usage report of a specific model (e.g. User model)
- Check and determine if a model can consume more resources
- Manually reset consumed usage limits of a model
- Automatically reset consumed usage limits by setting reset frequencies such as every second, every minute, every hour, every day, every month, etc and scheduling a built-in artisan command

## Use cases

Basically with this package you can track your users' or any other models' usages and restrict them when they hit their maximum limits.

#### Example use-cases:

- API usages per second, minute, month, etc
- Resource creation. E.g: projects, teams, users, products, etc
- Resource usages. E.g: storage, etc

## Versions

Compatible for **_Laravel versions >= 8.0_**.

## Installation

Install Laravel Usage Limiter using the Composer package manager:

```bash
composer require nabilhassen/laravel-usage-limiter
```

Next, you should publish the Laravel Usage Limiter configuration and migration files using the vendor:publish Artisan command:

```bash
php artisan vendor:publish --provider="NabilHassen\LaravelUsageLimiter\ServiceProvider"
```

Finally, you should run the migrate command in order to create the tables needed to store Laravel Usage Limiter's data:

```bash
php artisan migrate
```

## Basic Usage

First, you need to use the **HasLimits** trait on your model.

```php
use NabilHassen\LaravelUsageLimiter\Traits\HasLimits;

class User extends Authenticatable
{
    use HasLimits;
}
```

#### Create your Limits

```php
# On standard plan 5 projects are allowed per month
$projectsStandardLimit = Limit::create([
    'name' => 'projects',
    'allowed_amount' => 5,
    'plan' => 'standard', // optional
    'reset_frequency' => 'every month' // optional
]);

# On pro plan 10 projects are allowed per month
$projectsProLimit = Limit::create([
    'name' => 'projects',
    'allowed_amount' => 10,
    'plan' => 'pro', // optional
    'reset_frequency' => 'every month' // optional
]);

# Increment projects limit on standard plan from 5 to 15 per month
$projectsStandardLimit->incrementBy(10);

# Decrement projects limit on pro plan from 10 to 7 per month
$projectsProLimit->decrementBy(3);
```

###### Possible values for "reset_frequency" column came from src/Enum/FrequencyEnum.php

- null
- EVERY_SECOND = 'every second' // works in laravel >= 10.x
- EVERY_MINUTE = 'every minute'
- EVERY_HOUR  = 'every hour'
- EVERY_DAY  = 'every day'
- EVERY_WEEK  = 'every week'
- EVERY_TWO_WEEKS  = 'every two weeks'
- EVERY_MONTH  = 'every month'
- EVERY_QUARTER  = 'every quarter'
- EVERY_SIX_MONTHS  = 'every six months'
- EVERY_YEAR  = 'every year'

#### Set Limits on models

```php
$user->setLimit('projects', 'standard'); OR
$user->setLimit($projectsStandardLimit);
```

#### Set Limits on models with beginning used amounts

If a user has already consumed limits then:

```php
$user->setLimit('projects', 'standard', 2); OR
$user->setLimit($projectsStandardLimit, usedAmount: 2);
```

#### Unset Limits from models

```php
$user->unsetLimit('projects', 'standard'); OR
$user->unsetLimit($projectsStandardLimit);
```

#### Consume/Unconsume Limits

```php
# When a user creates a project
$user->useLimit('projects', 'standard'); OR
$user->useLimit($projectsStandardLimit);

# When a user creates multiple projects
$user->useLimit('projects', 'standard', 3); OR
$user->useLimit($projectsStandardLimit, amount: 3);

# When a user deletes a project
$user->unuseLimit('projects', 'standard'); OR
$user->unuseLimit($projectsStandardLimit);

# When a user deletes multiple projects
$user->unuseLimit('projects', 'standard', 3); OR
$user->unuseLimit($projectsStandardLimit, amount: 3);
```

> ###### _Both useLimit and unuseLimit methods throws an exception if a user exceeded limits or tried to unuse limits below 0_.

#### Reset Limits for models

```php
$user->resetLimit('projects', 'standard'); OR
$user->resetLimit($projectsStandardLimit);
```

#### All available methods

| Method           | Return Type | Parameters                                                                         |
| ---------------- | ----------- | ---------------------------------------------------------------------------------- |
| setLimit         | true\|throw | string\|Limit $limit, <br> ?string $plan = null, <br> float\|int $usedAmount = 0.0 |
| unsetLimit       | bool        | string\|Limit $limit, <br> ?string $plan = null                                    |
| isLimitSet       | bool        | string\|Limit $limit, <br> ?string $plan = null                                    |
| useLimit         | true\|throw | string\|Limit $limit, <br> ?string $plan = null, <br> float\|int $amount = 1.0     |
| unuseLimit       | true\|throw | string\|Limit $limit, <br> ?string $plan = null, <br> float\|int $amount = 1.0     |
| resetLimit       | bool        | string\|Limit $limit, <br> ?string $plan = null                                    |
| hasEnoughLimit   | bool        | string\|Limit $limit, <br> ?string $plan = null                                    |
| usedLimit        | float       | string\|Limit $limit, <br> ?string $plan = null                                    |
| remainingLimit   | float       | string\|Limit $limit, <br> ?string $plan = null                                    |
| limitUsageReport | array       | string\|Limit\|null $limit = null, <br> ?string $plan = null                       |

#### All available commands

| Command           | Arguments                                                        | Example                                                                     |
| ----------------- | ---------------------------------------------------------------- | --------------------------------------------------------------------------- |
| limit:create      | name: required <br> allowed_amount: required <br> plan: optional | php artisan limit:create --name products --allowed_amount 20 --plan premium |
| limit:delete      | name: required <br> plan: optional                               | php artisan limit:delete --name products --plan premium                     |
| limit:list        | None                                                             | php artisan limit:list                                                      |
| limit:reset       | None                                                             | php artisan limit:reset # reset limit usages to 0                           |
| limit:cache-reset | None                                                             | php artisan limit:cache-reset # flushes limits cache                        |

#### Blade

```blade
# Using limit instance
@limit($user, $projectsStandardLimit)
    // user has enough limits left
@else
    // user has NO enough limits left
@endlimit

# Using limit name and plan
@limit($user, 'projects', 'standard')
    // user has enough limits left
@else
    // user has NO enough limits left
@endlimit
```

## Schedule Limit Usage Resetting

The `limit:reset` command will reset your model's (e.g. user) limit usages based on the Limit's `reset_frequency`.

Add `limit:reset` command to the console kernel.

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    ...
    // Laravel < 10
    $schedule->command('limit:reset')->everyMinute();

    // Laravel >= 10
    $schedule->command('limit:reset')->everySecond();
    ...
}
```

## Advanced Usage

### Extending

- If you would like to write your own model, make sure that your new Limit model extends `NabilHassen\LaravelUsageLimiter\Models\Limit::class` and change the model in the `limit.php` config file to your new model.
- If you already have used the `limits` method or property where the `HasLimits` trait is used, you can change it to any string value (e.g. 'restricts') by changing the `relationship` key in the `limit.php` config file and you're done.
- If there are any conficts in the database table names, you will just need to change the tables names in the `limit.php` config file and you're good to go.

**_Clear your config cache if you have made any changes in the `limit.php` config file._**

### Caching

By default, Laravel Usage Limiter uses the default cache you chose for your app. If you would like to use any other cache store you will need to change the store key in the `limit.php` config file to your preferred cache store.

- All of your Limits will be cached for 24 hours.
- On create, update, or deleting of a limit, the Limits cache will be refreshed.
- Model-specific limits are cached in-memory (i.e. during the request).

## Manual Cache Reset

In your code

```php
app()->make(\NabilHassen\LaravelUsageLimiter\LimitManager::class)->flushCache();
```

Via command line

```bash
php artisan limit:cache-reset
```

## Testing

```bash
composer test
```

## Security

If you have found any security issues, please send an email to the author at nabil.hassen08@gmail.com.

## Contributing

You are welcome to contribute to the package and you will be credited. Just make sure your PR does one thing and add tests.

## License

The Laravel Usage Limiter is open-sourced software licensed under the MIT license [MIT license](https://github.com/NabilHassen/laravel-usage-limiter/blob/main/LICENSE).
