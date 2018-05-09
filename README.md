* [Installation](#installation)
* [Updating your Eloquent Models](#updating-your-eloquent-models)
* [Creating tables to support encrypt columns](#creating-tables-to-support-encrypt-columns)
* [Set encryption key in .env file](#set-encryption-key-in-env-file)


# grammar-encrypt
Encryption AES on mysql side
What versions of Laravel are supported It have been tested only with Laravel 5.4.
But you can try it with Laravel 5.x too.

## Installation

How to install Add package to composer.json

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


## Creating tables to support encrypt columns
It adds new features to Schema which you can use in your migrations:

```php
    Schema::create('tb_persons', function (Blueprint $table) {
        // here you do all columns supported by the schema builder
        $table->increments('id')->unsigned;
        $table->string('description', 250);
        $table ->unsignedInteger('created_by')->nullable();
        $table ->unsignedInteger('updated_by')->nullable();
    });
    
    // once the table is created use a raw query to ALTER it and add the BLOB, MEDIUMBLOB or LONGBLOB
    DB::statement("ALTER TABLE tb_persons ADD name MEDIUMBLOB after id");  
```


});

## Set encryption key in .env file

```php
APP_GRAMMARENCRYPT_KEY=yourencryptedkey
```
