<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase {

    private $pdo;

    protected function setUp(): void {
        // Conectar directamente igual que en index.php
        $host = 'localhost';
        $db   = 'minecraft_forum';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function testInsertarUsuarioEnBaseDeDatos() {

        $username = "test_user_" . rand(1000, 9999);
        $passwd = "testpass";

        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, password) VALUES (?, ?)"
        );
        $stmt->execute([$username, $passwd]);

        $check = $this->pdo->prepare(
            "SELECT username FROM users WHERE username = ?"
        );
        $check->execute([$username]);
        $result = $check->fetch();

        $this->assertNotFalse($result);
        $this->assertEquals($username, $result['username']);
    }

    protected function tearDown(): void {
        $this->pdo->exec("DELETE FROM users WHERE username LIKE 'test_user_%'");
    }
}
