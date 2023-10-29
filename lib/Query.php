<?php
/**
 * @author Drajat Hasan
 * @email <drajathasan20@gmail.com>
 * @create date 2023-10-30 04:41:33
 * @modify date 2023-10-30 06:17:20
 * @license GPLv3
 * @desc : 
 * this is part of SLiMS\DB::class, 
 * but you can use it without SLiMS\DB::class. : )
 * 
 * How to : ?
 * - with DB::class :
 * foreach(DB::query('select * from setting') as $data) {
 *   // do something here
 * }
 * 
 * - if you want setup connection 
 * $query = DB::query();
 * $query->setConnection('<your-connection-name>');
 * 
 * foreach($query->prepare('select * from setting') as $data) {
 *   // do something here
 * }
 * - if you want setup default output
 * $query = DB::query();
 * $query->setDefaultOutput(PDO::FETCH_OBJ);
 * 
 * foreach($query->prepare('select * from setting') as $data) {
 *   // do something here
 * }
 */
namespace SLiMS;

use PDO;
use PDOStatement;
use ArrayIterator;
use Countable;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use JsonException;
use SLiMS\DB;

final class Query implements IteratorAggregate,Countable
{
    /**
     * @var PDOStatement|null
     */
    private ?PDOStatement $statement = null;

    /**
     * @var string
     */
    private string $connection = ''; // default SLiMS;

    /**
     * Raw SQL
     * 
     * @var string
     */
    private string $sql = '';

    /**
     * Query parameter to execute
     * 
     * @var array
     */
    private array $params = [];

    /**
     * PDO Statement options
     * 
     * @var array
     */
    private array $options = [];

    /**
     * Output options
     * 
     * @var int
     */
    private int $defaultOutput = PDO::FETCH_ASSOC;

    /**
     * @param string $sql
     * @param array $params
     * @param array $options
     */
    public function __construct(string $sql = '', array $params = [], array $options = [])
    {
        $this->sql = $sql;
        $this->params = $params;
        $this->options = $options;
    }

    /**
     * Same as construct if you want to setup other
     * property g.g connection before generating data
     *
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return Query
     */
    public function prepare(string $sql = '', array $params = [], array $options = []): Query
    {
        $this->sql = $sql;
        $this->params = $params;
        $this->options = $options;
        return $this;
    }

    /**
     * Get connection based on connection property
     *
     * @return PDO
     */
    private function getCon(): PDO
    {
        return empty($this->connection) ? DB::getInstance() : DB::connection($this->connection);
    }

    /**
     * Switching connection
     *
     * @param string $connectionName
     * @return Query
     */
    public function setConnection(string $connectionName): Query
    {
        $this->connection = $connectionName;
        return $this;
    }

    /**
     * Output
     *
     * @param integer $output
     * @return void
     */
    public function setDefaultOutput(int $output)
    {
        $this->defaultOutput = $output;
        return $this;
    }

    /**
     * Generate data
     *
     * @return Generator
     */
    private function execute(): Generator
    {
        $this->statement = $this->getCon()->prepare($this->sql, $this->options);
        $this->statement->execute($this->params);

        while ($result = $this->statement->fetch($this->defaultOutput)) {
            yield $result;
        }
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * @return integer
     */
    public function count(): int
    {
        return count($this->toArray());
    }

    /**
     * If Object inside json_encode
     *
     * @return void
     */
    public function jsonSerialize(): Array
    {
        return $this->toArray();
    }

    /**
     * Export data to Array
     *
     * @return Array
     */
    public function toArray(): Array
    {
        return iterator_to_array($this->execute());
    }

    /**
     * Extract object to string as json format
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Review all data before generate by this library
     *
     * @return void
     */
    public function dump()
    {
        dump([
            '⚙️ Connection : ' => $this->connection, 
            '📎 SQL' => $this->sql, 
            '↪️ Params' => $this->params, 
            '📌 Options' => $this->options
        ]);
    }
}