<?php
use PHPUnit\Framework\TestCase;
use App\Database;

class UsuarioTest extends TestCase {

    private $pdo;

    protected function setUp(): void {
        $this->pdo = Database::connect();
    }

    public function testInsertarUsuarioEnBaseDeDatos() {

        // Datos de prueba
        $username = "test_user_" . rand(1000, 9999);
        $passwd = "testpass";

        // Insertar usuario (como en index.php)
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, password) VALUES (?, ?)"
        );
        $stmt->execute([$username, $passwd]);

        // Comprobar que existe
        $check = $this->pdo->prepare(
            "SELECT username FROM users WHERE username = ?"
        );
        $check->execute([$username]);
        $result = $check->fetch();

        // Verificaciones
        $this->assertNotFalse($result);
        $this->assertEquals($username, $result['username']);
    }
}