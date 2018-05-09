<?php

namespace DevMaster10\GrammarEncrypt\Database;

use Auth;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Query\Builder as QueryBuilder;
use DevMaster10\GrammarEncrypt\Database\Query\BuilderEncrypt as  QueryBuilderEncrypt;
use DevMaster10\GrammarEncrypt\Database\Query\Grammars\GrammarEncrypt;


// Util
use App\Util\CommonUtil;

class ModelEncrypt extends Model
{

    protected $fillableCrypt = [];

    /**
     * Get a new query builderencrypt instance for the connection.
     *
     * @return \GrammarEncrypt\Database\Query\BuilderEncrypt
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();
        
         // Only for MySqlGrammar
        if (get_class($connection) === 'Illuminate\Database\MySqlConnection') {
            $grammarEncrypt = $connection->withTablePrefix(new GrammarEncrypt);
        //     // $connection->setSchemaGrammar($grammarEncrypt);
            $grammarEncrypt->setFillableEncrypt($this->fillableCrypt);
        //     // $connection->setSchemaGrammar($MySqlGrammar);
            // $grammarEncrypt = new GrammarEncrypt;
            $connection->setQueryGrammar($grammarEncrypt);
        }

        return new QueryBuilderEncrypt(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor(), $this->fillable
        );
    }

    //     /**
    //  * Create a new Eloquent query builder for the model.
    //  *
    //  * @param  \Illuminate\Database\Query\Builder  $query
    //  * @return \DevMaster10\GrammarEncrypt\Database\Query\BuilderEncrypt|static
    //  */
    // public function newEloquentBuilder($query)
    // {
    //     return new QueryBuilderEncrypt($query);
    // }

    // /**
    //  * Get a new query builder instance for the connection.
    //  *
    //  * @return \Illuminate\Database\Query\Builder
    //  */
    // protected function newBaseQueryBuilder()
    // {
    //     $connection = $this->getConnection();

    //     return new QueryBuilderEncrypt(
    //         $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
    //     );
    // }

    // /**
    //  * Get a new query builder instance for the connection.
    //  *
    //  * @return \Illuminate\Database\Query\Builder
    //  */
    // protected function newBaseQueryBuilder()
    // {
    //     $connection = $this->getConnection();
    //     // $grammar =  $connection->getQueryGrammar();
    //     // $postProcessor = $connection->getPostProcessor();

    //     // return new BuilderCustom($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());

    //     $builderCustom = new BuilderCustom(null);

    //     return $builderCustom;
    // }

    // /**
    //  * Get a new query builder that doesn't have any global scopes.
    //  *
    //  * @return \Illuminate\Database\Eloquent\Builder|static
    //  */
    // public function newQueryWithoutScopes()
    // {
    //     $builder = $this->newEloquentBuilder($this->newBaseQueryBuilder());

    //     // Once we have the query builders, we will set the model instances so the
    //     // builder can easily access any information it may need from the model
    //     // while it is constructing and executing various queries against it.
    //     return $builder->setModel($this)
    //                 ->with($this->with)
    //                 ->withCount($this->withCount);
    // }

    // public static function boot()
    // {
    //    parent::boot();
    //    static::creating(function($model)
    //    {
    //        $user = Auth::user();
    //        if($user) {
    //            $model->created_by = $user->id;
    //            $model->updated_by = $user->id;
    //        }

    //    });
    //    static::updating(function($model)
    //    {
    //        $user = Auth::user();
    //        if($user)
    //         $model->updated_by = $user->id;
    //    });
    //    static::saving(function($model)
    //    {
    //        $user = Auth::user();
    //        if($user)
    //         $model->updated_by = $user->id;
    //     });

        // if($this->softDeleting) {
        //     static::deleting(function($model)
        //     {
        //         $user = Auth::user();
        //         if($user)
        //         $model->deleted_by = $user->id;
        //     });
        // }

    // }

}