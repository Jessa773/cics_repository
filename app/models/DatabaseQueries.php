<?php
require_once '../config/db.php';

class DatabaseQueries {
    public static function select($table, $conditions = [], $fields = '*') {
        $sql = "SELECT $fields FROM $table";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_map(fn($field) => "$field = ?", array_keys($conditions)));
        }
        return Database::executeQuery($sql, array_values($conditions));
    }

    public static function insert($table, $data) {
        $fields = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($fields) VALUES ($values)";
        return Database::executeQuery($sql, array_values($data));
    }

    public static function update($table, $data, $conditions) {
        $sql = "UPDATE $table SET " . implode(', ', array_map(fn($field) => "$field = ?", array_keys($data)));
        $sql .= " WHERE " . implode(' AND ', array_map(fn($field) => "$field = ?", array_keys($conditions)));
        return Database::executeQuery($sql, [...array_values($data), ...array_values($conditions)]);
    }

    public static function delete($table, $conditions) {
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', array_map(fn($field) => "$field = ?", array_keys($conditions)));
        return Database::executeQuery($sql, array_values($conditions));
    }
}
?>
