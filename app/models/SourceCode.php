<?php
require_once dirname(__FILE__, 2) . '/config/database.php';

class SourceCode {
    public $id;
    public $title;
    public $description;
    public $content;
    public $user_id;

    public static function find($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM source_codes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchObject('SourceCode');
    }

    public static function searchByTitle($title) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM source_codes WHERE title LIKE ?");
        $stmt->execute(['%' . $title . '%']);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'SourceCode');
    }

    public function save() {
        $db = Database::getInstance()->getConnection();
        if ($this->id) {
            $stmt = $db->prepare("UPDATE source_codes SET title = ?, description = ?, content = ? WHERE id = ?");
            $stmt->execute([$this->title, $this->description, $this->content, $this->id]);
        } else {
            $stmt = $db->prepare("INSERT INTO source_codes (title, description, content, user_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$this->title, $this->description, $this->content, $this->user_id]);
            $this->id = $db->lastInsertId();
        }
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM source_codes WHERE id = ?");
        $stmt->execute([$id]);
    }
}
?>
