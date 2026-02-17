<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase {

    private $pdo;

    protected function setUp(): void {
        // Conectar directamente igual que en index.php
        $host = '127.0.0.1';
        $port = 3306;
        $db   = 'minecraft_forum';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            $this->fail("No se pudo conectar a la base de datos: " . $e->getMessage());
        }
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

        $this->assertEquals('usuario_incorrecto', $result['username']);
    }

    protected function tearDown(): void {
        $this->pdo->exec("DELETE FROM users WHERE username LIKE 'test_user_%'");
    }
}
