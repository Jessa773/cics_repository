<?php
class UserController {
    // Controller logic for user management
    public function login($username, $password) {
        // Logic for user login
        $user = User::findByUsername($username);
        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            header('Location: /public/index.php');
        } else {
            echo "Invalid credentials!";
        }
    }

    public function register($username, $password, $email) {
        // Logic for user registration
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $user = new User();
        $user->username = $username;
        $user->password = $hashed_password;
        $user->email = $email;
        $user->save();
        header('Location: /public/login.php');
    }

    public function logout() {
        session_destroy();
        header('Location: /public/login.php');
    }
}
?>
