<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries\base;

use yii\db\ActiveQueryInterface;
use yii\db\BatchQueryResult;
use yii\db\Connection;
use yii\db\ExpressionInterface;

interface Oauth2BaseActiveQueryInterface extends ActiveQueryInterface
{
    /**
     * Wrapper for ActiveQuery::$via
     *
     * @return array|object
     * @since 1.0.0
     */
    public function getVia();

    # region \yii\db\ActiveQuery methods.
    /**
     * Joins with the specified relations.
     *
     * This method allows you to reuse existing relation definitions to perform JOIN queries.
     * Based on the definition of the specified relation(s), the method will append one or multiple
     * JOIN statements to the current query.
     *
     * If the `$eagerLoading` parameter is true, the method will also perform eager loading for the specified relations,
     * which is equivalent to calling [[with()]] using the specified relations.
     *
     * Note that because a JOIN query will be performed, you are responsible to disambiguate column names.
     *
     * This method differs from [[with()]] in that it will build up and execute a JOIN SQL statement
     * for the primary table. And when `$eagerLoading` is true, it will call [[with()]] in addition with the
     * specified relations.
     *
     * @param string|array $with the relations to be joined. This can either be a string, representing a relation name
     * or an array with the following semantics:
     *
     * - Each array element represents a single relation.
     * - You may specify the relation name as the array key and provide an anonymous functions that
     *   can be used to modify the relation queries on-the-fly as the array value.
     * - If a relation query does not need modification, you may use the relation name as the array value.
     *
     * The relation name may optionally contain an alias for the relation table (e.g. `books b`).
     *
     * Sub-relations can also be specified, see [[with()]] for the syntax.
     *
     * In the following you find some examples:
     *
     * ```php
     * // find all orders that contain books, and eager loading "books"
     * Order::find()->joinWith('books', true, 'INNER JOIN')->all();
     * // find all orders, eager loading "books", and sort the orders and books by the book names.
     * Order::find()->joinWith([
     *     'books' => function (\yii\db\ActiveQuery $query) {
     *         $query->orderBy('item.name');
     *     }
     * ])->all();
     * // find all orders that contain books of the category 'Science fiction', using the alias "b" for the books table
     * Order::find()->joinWith(['books b'], true, 'INNER JOIN')->where(['b.category' => 'Science fiction'])->all();
     * ```
     *
     * The alias syntax is available since version 2.0.7.
     *
     * @param bool|array $eagerLoading whether to eager load the relations
     * specified in `$with`.  When this is a boolean, it applies to all
     * relations specified in `$with`. Use an array to explicitly list which
     * relations in `$with` need to be eagerly loaded.  Note, that this does
     * not mean, that the relations are populated from the query result. An
     * extra query will still be performed to bring in the related data.
     * Defaults to `true`.
     * @param string|array $joinType the join type of the relations specified in `$with`.
     * When this is a string, it applies to all relations specified in `$with`. Use an array
     * in the format of `relationName => joinType` to specify different join types for different relations.
     * @return $this the query object itself
     * @since 1.0.0
     */
    public function joinWith($with, $eagerLoading = true, $joinType = 'LEFT JOIN');

    /**
     * Starts a batch query and retrieves data row by row.
     *
     * This method is similar to [[batch()]] except that in each iteration of the result,
     * only one row of data is returned. For example,
     *
     * ```php
     * $query = (new Query)->from('user');
     * foreach ($query->each() as $row) {
     * }
     * ```
     *
     * @param int $batchSize the number of records to be fetched in each batch.
     * @param Connection $db the database connection. If not set, the "db" application component will be used.
     * @return BatchQueryResult the batch query result. It implements the [[\Iterator]] interface
     * and can be traversed to retrieve the data in batches.
     * @since 1.0.0
     */
    public function each($batchSize = 100, $db = null);

    /**
     * Define an alias for the table defined in [[modelClass]].
     *
     * This method will adjust [[from]] so that an already defined alias will be overwritten.
     * If none was defined, [[from]] will be populated with the given alias.
     *
     * @param string $alias the table alias.
     * @return $this the query object itself
     * @since 1.0.0
     */
    public function alias($alias);

    /**
     * Inner joins with the specified relations.
     * This is a shortcut method to [[joinWith()]] with the join type set as "INNER JOIN".
     * Please refer to [[joinWith()]] for detailed usage of this method.
     * @param string|array $with the relations to be joined with.
     * @param bool|array $eagerLoading whether to eager load the relations.
     * Note, that this does not mean, that the relations are populated from the
     * query result. An extra query will still be performed to bring in the
     * related data.
     * @return $this the query object itself
     * @see joinWith()
     * @since 1.0.0
     */
    public function innerJoinWith($with, $eagerLoading = true);


    /**
     * Sets the SELECT part of the query.
     * @param string|array|ExpressionInterface $columns the columns to be selected.
     * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. ['id', 'name']).
     * Columns can be prefixed with table names (e.g. "user.id") and/or contain column aliases
     * (e.g. "user.id AS user_id").
     * The method will automatically quote the column names unless a column contains some parenthesis
     * (which means the column contains a DB expression). A DB expression may also be passed in form of
     * an [[ExpressionInterface]] object.
     *
     * Note that if you are selecting an expression like `CONCAT(first_name, ' ', last_name)`, you should
     * use an array to specify the columns. Otherwise, the expression may be incorrectly split into several parts.
     *
     * When the columns are specified as an array, you may also use array keys as the column aliases (if a column
     * does not need alias, do not use a string key).
     *
     * Starting from version 2.0.1, you may also select sub-queries as columns by specifying each such column
     * as a `Query` instance representing the sub-query.
     *
     * @param string|null $option additional option that should be appended to the 'SELECT' keyword. For example,
     * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
     * @return $this the query object itself
     */
    public function select($columns, $option = null);

    /**
     * Executes the query and returns the first column of the result.
     * @param Connection|null $db the database connection used to generate the SQL statement.
     * If this parameter is not given, the `db` application component will be used.
     * @return array the first column of the query result. An empty array is returned if the query results in nothing.
     */
    public function column($db = null);
    # endregion \yii\db\ActiveQuery methods.
}
