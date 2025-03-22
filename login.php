<?php
session_start();

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'facebook2';
$username = 'root';  // Usa el nombre de usuario de tu base de datos
$password = '';      // Usa la contraseña de tu base de datos

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $pwd = $_POST['pwd'];

    // Verificar las credenciales
    $sql = "SELECT * FROM usuario WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pwd, $row['pwd'])) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['foto'] = $row['foto_url'];
            // Redirigir a la página de publicaciones
            header("Location: publicaciones.php");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
    $conn->close();
}
?>