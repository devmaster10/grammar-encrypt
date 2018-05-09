<?php

namespace DevMaster10\GrammarEncrypt\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Builder;

/**
 * Extended version of MySqlGrammar with
 * support of 'set' data type
 */
class GrammarEncrypt extends MySqlGrammar {

    public $fillableEncrypt = [];


    protected $GRAMMAR_KEY;

    public function __construct()
    {
        $this->GRAMMAR_KEY = env('APP_GRAMMARENCRYPT_KEY');
    }

    /**
     * Set encrypted columns.
     *
     * @param  string  $grammar
     * @return void
     */
    public function setFillableEncrypt($fillableEncrypt)
    {
        $this->fillableEncrypt = $fillableEncrypt;
    }

    // /**
    //  * Compile the "select *" portion of the query.
    //  *
    //  * @param  \Illuminate\Database\Query\Builder  $query
    //  * @param  array  $columns
    //  * @return string|null
    //  */
    public function compileColumns(Builder $query, $columns)
    {
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax that is best handled by that function to keep things neat.
        if (! is_null($query->aggregate)) {
            return;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select.$this->columnizeSelect($columns);
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param  array   $columns
     * @return string
     */
    public function columnizeSelect(array $columns)
    {
        return implode(', ', array_map([$this, 'wrapSelect'], $columns));
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $value
     * @param  bool    $prefixAlias
     * @return string
     */
    public function wrapSelect($value, $prefixAlias = false)
    {
        $colString = $this->wrap($value, $prefixAlias, true, true);
        //  if (strpos(strtolower($value), ' as ') !== false) {

        return $colString;
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $value
     * @param  bool    $prefixAlias
     * @return string
     */
    public function wrapWhere($value, $prefixAlias = false)
    {
        $colString = $this->wrap($value, $prefixAlias, true, false);
        //  if (strpos(strtolower($value), ' as ') !== false) {

        return $colString;
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $value
     * @param  bool    $prefixAlias
     * @return string
     */
    public function wrap($value, $prefixAlias = false,  $encrypt = false, $forceAlias = false)
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        // If the value being wrapped has a column alias we will need to separate out
        // the pieces so we can wrap each of the segments of the expression on it
        // own, and then joins them both back together with the "as" connector.
        if (strpos(strtolower($value), ' as ') !== false) {
            return $this->wrapAliasedValue($value, $prefixAlias, $encrypt);
        }

        return $this->wrapSegments(explode('.', $value), $encrypt, $forceAlias);
    }

    /**
     * Wrap a value that has an alias.
     *
     * @param  string  $value
     * @param  bool  $prefixAlias
     * @return string
     */
    protected function wrapAliasedValue($value, $prefixAlias = false, $encrypt = false)
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        // If we are wrapping a table we need to prefix the alias with the table prefix
        // as well in order to generate proper syntax. If this is a column of course
        // no prefix is necessary. The condition will be true when from wrapTable.
        if ($prefixAlias) {
            $segments[1] = $this->tablePrefix.$segments[1];
        }

        return $this->wrap($segments[0], false, $encrypt).' as '.$this->wrapValue($segments[1]);
    }

    /**
     * Wrap the given value segments.
     *
     * @param  array  $segments
     * @return string
     */
    public function wrapSegments($segments, $encrypt = false, $forceAlias = false)
    {
        return collect($segments)->map(function ($segment, $key) use ($segments, $encrypt, $forceAlias) {

            if($key == 0 && count($segments) > 1) {
                return $this->wrapTable($segment);
            } else if($encrypt && in_array($segment, $this->fillableEncrypt)) {
                $columnAlias = $this->wrapValue($segment);
                return "AES_DECRYPT(" . $this->wrapValue($segment) . ", '" . $this->GRAMMAR_KEY . "')" . ($forceAlias ? " as " . $columnAlias : "");
            } else {
                return $this->wrapValue($segment);
            }
        })->implode('.');
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        // If the given value is a JSON selector we will wrap it differently than a
        // traditional value. We will need to split this path and wrap each part
        // wrapped, etc. Otherwise, we will simply wrap the value as a string.
        if ($this->isJsonSelector($value)) {
            return $this->wrapJsonSelector($value);
        }
        
        return '`'.str_replace('`', '``', $value).'`';
    }

        /**
     * Create query parameter place-holders for an array.
     *
     * @param  array   $values
     * @return string
     */
    public function parameterize(array $values)
    {
        return implode(', ',  array_map([$this, 'parameterKey'], array_keys($values), $values));
    }
    
    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param  mixed   $value
     * @return string
     */
    public function parameterKey($key, $value = null)
    {
        if($this->isExpression($value))
            return $this->getValue($value);
        else if($key && in_array($key, $this->fillableEncrypt))
            return  "AES_ENCRYPT(?, '" . $this->GRAMMAR_KEY . "')";
        else
            return '?';
    }


    /**
     * Compile all of the columns for an update statement.
     *
     * @param  array  $values
     * @return string
     */
    protected function compileUpdateColumns($values)
    {
        return collect($values)->map(function ($value, $key) {
            if ($this->isJsonSelector($key)) {
                return $this->compileJsonUpdateColumn($key, new JsonExpression($value));
            } else {
                return $this->wrap($key).' = '.$this->parameterKey($key, $value);
          
            }
        })->implode(', ');
    }


     /**
     * Compile a basic where clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    public function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrapWhere($where['column']).' '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "where in" clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    public function whereIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrapWhere($where['column']).' in ('.$this->parameterize($where['values']).')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where not in" clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNotIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrapWhere($where['column']).' not in ('.$this->parameterize($where['values']).')';
        }

        return '1 = 1';
    }

    /**
     * Compile a where in sub-select clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereInSub(Builder $query, $where)
    {
        return $this->wrapWhere($where['column']).' in ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a where not in sub-select clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNotInSub(Builder $query, $where)
    {
        return $this->wrapWhere($where['column']).' not in ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a "where null" clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNull(Builder $query, $where)
    {
        return $this->wrapWhere($where['column']).' is null';
    }

    /**
     * Compile a "where not null" clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNotNull(Builder $query, $where)
    {
        return $this->wrapWhere($where['column']).' is not null';
    }

    /**
     * Compile a "between" where clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    public function whereBetween(Builder $query, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        return $this->wrapWhere($where['column']).' '.$between.' ? and ?';
    }

    /**
     * Compile a date based where clause.
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $type.'('.$this->wrapWhere($where['column']).') '.$where['operator'].' '.$value;
    }

    /**
     * Compile a where clause comparing two columns..
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    public function whereColumn(Builder $query, $where)
    {
        return $this->wrapWhere($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
    }

      /**
     * Compile a where condition with a sub-select.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @param  array   $where
     * @return string
     */
    protected function whereSub(Builder $query, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrapWhere($where['column']).' '.$where['operator']." ($select)";
    }

    /**
     * Compile a basic having clause.
     *
     * @param  array   $having
     * @return string
     */
    protected function compileBasicHaving($having)
    {
        $column = $this->wrapWhere($having['column']);

        $parameter = $this->parameter($having['value']);

        return $having['boolean'].' '.$column.' '.$having['operator'].' '.$parameter;
    }

    /**
     * Compile the query orders to an array.
     *
     * @param  \Illuminate\Database\Query\Builder
     * @param  array  $orders
     * @return array
     */
    protected function compileOrdersToArray(Builder $query, $orders)
    {
        return array_map(function ($order) {
            return ! isset($order['sql'])
                        ? $this->wrapWhere($order['column']).' '.$order['direction']
                        : $order['sql'];
        }, $orders);
    }

    /**
     * Compile an exists statement into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileExists(Builder $query)
    {
        $select = $this->compileSelect($query);

        return "select exists({$select}) as {$this->wrapWhere('exists')}";
    }

}