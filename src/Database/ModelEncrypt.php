<?php

namespace DevMaster10\GrammarEncrypt\Database;

use Auth;

use Illuminate\Database\Eloquent\Model;
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
            $grammarEncrypt->setFillableEncrypt($this->fillableCrypt);
            $connection->setQueryGrammar($grammarEncrypt);
        }

        return new QueryBuilderEncrypt(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor(), $this->fillable
        );
    }

}