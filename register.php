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

    // Procesar la subida de la imagen
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto_tmp_path = $_FILES['foto']['tmp_name'];
        $foto_type = $_FILES['foto']['type'];

        // Convertir la imagen a base64
        $foto_data = file_get_contents($foto_tmp_path);
        $foto_base64 = base64_encode($foto_data);

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
            $stmt->bind_param("sss", $usuario, $hashed_pwd, $foto_base64);

            if ($stmt->execute()) {
                header("Location: login.html");
                exit(); // Asegúrate de terminar la ejecución del script después de la redirección
            } else {
                echo "Error en el registro: " . $stmt->error;
            }
        }
    } else {
        echo "Error al subir la imagen.";
    }
    $conn->close();
}
?>