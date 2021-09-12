# JawabApp Community

## Installation

You can install the package via composer:

```bash
composer require jawabapp/community
```

## Usage

###### User.php Model

```php
USE Jawabapp\Community\Contracts\CommunityAccount;
use Jawabapp\Community\Traits\HasCommunityAccount;

class User extends Authenticatable implements CommunityAccount
{
	use HasCommunityAccount;
	//...
}
```

implement those methods from CommunityAccount interface.

```php

use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable implements CommunityAccount
{
    public function getDefaultAccount()
    {
        //...
    }
    public function getAccount($account_id)
    {
        //...
    }
}
```

---

##### Publish Package assets

###### Run follwoing command on Terminal

```php

php artisan vendor:publish --provider=Jawabapp\Community\CommunityServiceProvider

```

###### alter community.php config file adding user class and route prefix

```php
[
    'user_class' => \App\User::class, // user class
    'route' => [
        'prefix' => 'package', // route prefix
        'middleware' => 'web', // route middlware
    ]
,
	//...
]
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email trmdy@hotmail.com instead of using the issue tracker.

## Credits

- [Ibraheem Qanah](https://github.com/Qanah)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
