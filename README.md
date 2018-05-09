* [Installation](#installation)
* [Updating your Eloquent Models](#updating-your-eloquent-models)

# grammar-encrypt
Encryption AES on mysql side
What versions of Laravel are supported It have been tested only with Laravel 5.0.
But you can try it with Laravel 5.4 too. How to install Add package to composer.json

## Installation

```php
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/devmaster10/grammar-encrypt.git"
    }
],
"require": {
    "laravel/framework": "5.0.*",
    "devmaster10/grammar-encrypt": "dev-master"
},
```

## Updating your Eloquent Models

Your models that have encrypted columns, should extend from ModelEncrypt:

```php
namespace App\Models;

use DevMaster10\GrammarEncrypt\Database\ModelEncrypt;

class LSAttribute extends ModelEncrypt
{    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tb_persons';

    /**
     * The attributes that are encrypted.
     *
     * @var array
     */
    protected $fillableEncrypt = [
        'name'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
                'name',
                'description',
                ];
}
```
