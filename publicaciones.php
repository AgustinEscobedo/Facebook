<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'facebook2';
$username = 'root';  // Usa el nombre de usuario de tu base de datos
$password = '';      // Usa la contraseña de tu base de datos

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el usuario logueado
$usuario = $_SESSION['usuario'];
$usuario_id = (int) $_SESSION['usuario_id'];  // Asegúrate de que el usuario_id esté en la sesión

// Crear publicación (solo para solicitudes POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['contenido'])) {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $foto_url = $_SESSION['foto'];  // Foto del usuario
    $nombre_usuario = $_SESSION['usuario'];

    // Insertar la publicación
    $sql = "INSERT INTO publicaciones (usuario_id, titulo, contenido, foto_url, nombre_usuario) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $usuario_id, $titulo, $contenido, $foto_url, $nombre_usuario);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Publicación creada exitosamente!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al crear publicación: " . $stmt->error]);
    }
    exit();
}

// Obtener las publicaciones (solo para solicitudes GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {
    $sql = "SELECT p.*, u.foto_url FROM publicaciones p JOIN usuario u ON p.usuario_id = u.id ORDER BY p.fecha DESC";
    $result = $conn->query($sql);
    $publicaciones = [];

    while ($row = $result->fetch_assoc()) {
        $publicaciones[] = $row;
    }

    echo json_encode($publicaciones);
    exit();
}

// Función para verificar si el usuario ya reaccionó a una publicación
function usuario_reacciono($usuario_id, $publicacion_id) {
    global $conn;
    $sql = "SELECT * FROM reacciones WHERE usuario_id = ? AND publicacion_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $publicacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;  // Si el usuario ya reaccionó, devuelve true
}

// Función para manejar el like y dislike
if (isset($_GET['accion'], $_GET['id'])) {
    $accion = $_GET['accion'];
    $publicacion_id = (int) $_GET['id'];

    // Verificar si el usuario ya reaccionó a esta publicación
    if (usuario_reacciono($usuario_id, $publicacion_id)) {
        echo "Ya has reaccionado a esta publicación.";
    } else {
        // Registrar la reacción (like o dislike)
        $sql = "INSERT INTO reacciones (usuario_id, publicacion_id, reaccion) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $usuario_id, $publicacion_id, $accion);
        $stmt->execute();

        // Actualizar los contadores de likes o dislikes en la publicación
        if ($accion == 'like') {
            $sql = "UPDATE publicaciones SET likes = likes + 1 WHERE id = ?";
        } elseif ($accion == 'dislike') {
            $sql = "UPDATE publicaciones SET dislikes = dislikes + 1 WHERE id = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
    }

    // Redirigir de nuevo a la página de publicaciones
    header("Location: publicaciones.php");
    exit();
}

// Obtener las publicaciones para renderizar en HTML
$sql = "SELECT p.*, u.foto_url FROM publicaciones p JOIN usuario u ON p.usuario_id = u.id ORDER BY p.fecha DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicaciones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?>!</h2>

        <form id="form-publicacion">
            <h3>Crear Publicación</h3>
            <input type="text" name="titulo" placeholder="Título" required><br>
            <textarea name="contenido" placeholder="Contenido de la publicación" required></textarea><br>
            <button type="submit">Crear Publicación</button>
        </form>

        <h3>Publicaciones:</h3>
        <div id="publicaciones-container">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="post">
                    <div class="post-header">
                        <img src="<?php echo $row['foto_url']; ?>" alt="Foto de perfil" class="post-img">
                        <div class="post-usuario"><?php echo $row['nombre_usuario']; ?></div>
                    </div>
                    <h4><?php echo $row['titulo']; ?></h4>
                    <p><?php echo $row['contenido']; ?></p>
                    <div class="reacciones">
                        <span>Likes: <?php echo $row['likes']; ?> | Dislikes: <?php echo $row['dislikes']; ?></span>
                        <br>
                        <!-- Verificar si el usuario ya reaccionó -->
                        <a href="publicaciones.php?accion=like&id=<?php echo $row['id']; ?>" <?php if (usuario_reacciono($usuario_id, $row['id'])) echo 'disabled'; ?>>Like</a> | 
                        <a href="publicaciones.php?accion=dislike&id=<?php echo $row['id']; ?>" <?php if (usuario_reacciono($usuario_id, $row['id'])) echo 'disabled'; ?>>Dislike</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>