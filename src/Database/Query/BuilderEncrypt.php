<?php

namespace DevMaster10\GrammarEncrypt\Database\Query;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Builder as QueryBuilderCustom;
use DevMaster10\GrammarEncrypt\Database\Query\Grammars\GrammarEncrypt;

class BuilderEncrypt extends QueryBuilderCustom
{
    public $fillableColumns = [];

    /**
     * Set encrypted columns.
     *
     * @param  string  $grammar
     * @return void
     */
    public function setFillableColumns($fillableColumns)
    {
        $this->fillableColumns = $fillableColumns;
    }

    /**
     * Create a new query builder instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \GrammarEncrypt\Database\Query\Grammars\GrammarEncrypt  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor  $processor
     * @return void
     */
    public function __construct(ConnectionInterface $connection, GrammarEncrypt $grammar = null, Processor $processor = null, $fillableColumns = null)
    {
        $this->fillableColumns = $fillableColumns;
        parent::__construct($connection, $grammar, $processor);
    }


    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        if($columns == ['*'] &&  $this->fillableColumns)
            $columns =  $this->fillableColumns;

        return parent::get($columns);
    }
    
}
                                                                                                                                                                                                                       