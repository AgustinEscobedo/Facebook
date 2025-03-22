<?php
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
    $foto = $_POST['foto'];

    // Verificar si el usuario ya existe
    $sql = "SELECT * FROM usuario WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "El usuario ya existe.";
    } else {
        // Insertar el nuevo usuario
        $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuario (usuario, pwd, foto_url) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $usuario, $hashed_pwd, $foto);

        if ($stmt->execute()) {
            echo "Registro exitoso. <a href='login.html'>Iniciar sesión</a>";
        } else {
            echo "Error en el registro: " . $stmt->error;
        }
    }
    $conn->close();
}
?>
