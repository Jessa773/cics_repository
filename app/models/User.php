<?php
class User {
    public $id;
    public $username;
    public $password;
    public $email;

    public static function findByUsername($username) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchObject('User');
    }

    public function save() {
        $db = Database::getConnection();
        if ($this->id) {
            $stmt = $db->prepare("UPDATE users SET username = ?, password = ?, email = ? WHERE id = ?");
            $stmt->execute([$this->username, $this->password, $this->email, $this->id]);
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$this->username, $this->password, $this->email]);
            $this->id = $db->lastInsertId();
        }
    }
}
?>
