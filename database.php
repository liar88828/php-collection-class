<?php

class Database {
    private PDO $connection;
    private array $arrayQuery = [];
    private array $arraySelect = [];
    private array $updateValues = [];

    public function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $this->connection = new PDO($dsn, $user, $pass, $options);
    }

    public function select(string $table, array $columns = []) {
        $cols = empty($columns) ? '*' : implode(', ', $columns);
        $this->arrayQuery[] = "SELECT $cols FROM $table";
        return $this;
    }

    public function where(string $key, string|int|bool $value) {
        $val = is_string($value) ? "'$value'" : $value;
        $this->arrayQuery[] = "WHERE $key = $val";
        return $this;
    }

    public function update(string $table) {
        $this->arrayQuery[] = "UPDATE $table";
        return $this;
    }

    public function set(string $key, string|int|bool $value) {
        $this->updateValues[] = ['key' => $key, 'value' => $value];
        return $this;
    }

    public function delete(string $table) {
        $this->arrayQuery[] = "DELETE FROM $table";
        return $this;
    }

    public function insert(string $table) {
        $this->arrayQuery[] = "INSERT INTO $table";
        return $this;
    }

    public function column(string $key, string|int|bool $value) {
        $this->arraySelect[] = ['key' => $key, 'value' => $value];
        return $this;
    }

    public function exec() {
        $finalQuery = '';

        if ($this->startsWith($this->arrayQuery[0] ?? '', 'SELECT')) {
            $finalQuery = implode(' ', $this->arrayQuery);

        } elseif ($this->startsWith($this->arrayQuery[0] ?? '', 'UPDATE')) {
            $updateStmt = $this->arrayQuery[0];
            $setClause = implode(', ', array_map(function ($item) {
                $value = is_string($item['value']) ? "'{$item['value']}'" : $item['value'];
                return "{$item['key']} = $value";
            }, $this->updateValues));
            $whereClause = $this->findWhereClause();
            $finalQuery = "$updateStmt SET $setClause $whereClause";

        } elseif ($this->startsWith($this->arrayQuery[0] ?? '', 'DELETE')) {
            $finalQuery = implode(' ', $this->arrayQuery);

        } elseif ($this->startsWith($this->arrayQuery[0] ?? '', 'INSERT')) {
            $insertStmt = $this->arrayQuery[0];
            $keys = implode(', ', array_column($this->arraySelect, 'key'));
            $values = implode(', ', array_map(function ($item) {
                return is_string($item['value']) ? "'{$item['value']}'" : $item['value'];
            }, $this->arraySelect));
            $finalQuery = "$insertStmt ($keys) VALUES ($values)";

        } else {
            throw new Exception('Unknown query type.');
        }

        echo "Executing: $finalQuery\n";
        $stmt = $this->connection->prepare($finalQuery);
        $stmt->execute();

        $this->reset();

        return $stmt->fetchAll();
    }

    private function reset() {
        $this->arrayQuery = [];
        $this->arraySelect = [];
        $this->updateValues = [];
    }

    private function findWhereClause(): string {
        foreach ($this->arrayQuery as $q) {
            if (str_starts_with($q, 'WHERE')) return $q;
        }
        return '';
    }

    private function startsWith(string $haystack, string $needle): bool {
        return str_starts_with($haystack, $needle);
    }
}
