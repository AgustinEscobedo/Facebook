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
function usuario_reacciono($usuario_id, $publicacion_id)
{
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
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <style>
        /* styles.css */
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            display: flex;
            width: 100%;
            /* max-width: 1200px; */
            /* margin: 20px; */
            padding: 20px;
            gap: 20px;
        }

        /* Estilos para el formulario de publicaciones */
        #form-publicacion {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 450px;
            height: 500px;
            flex-shrink: 0;
        }

        #form-publicacion h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        #form-publicacion input[type="text"],
        #form-publicacion textarea {
            width: 90%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.875rem;
            color: #4a5568;
            margin-bottom: 1rem;
            transition: border-color 0.2s;
        }

        #form-publicacion input[type="text"]:focus,
        #form-publicacion textarea:focus {
            border-color: #ef4444;
            outline: none;
        }

        #form-publicacion button[type="submit"] {
            background-color: #ef4444;
            color: #ffffff;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }

        #form-publicacion button[type="submit"]:hover {
            background-color: #dc2626;
        }

        /* Estilos para las publicaciones */
        #publicaciones-container {
            flex-grow: 1;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .post {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .post-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .post-usuario {
            font-size: 1rem;
            font-weight: 600;
            color: #2d3748;
        }

        .post h4 {
            font-size: 1.125rem;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .post p {
            font-size: 0.875rem;
            color: #4a5568;
            margin-bottom: 10px;
        }

        .reacciones {
            font-size: 0.875rem;
            color: #4a5568;
        }

        .reacciones a {
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            margin-right: 10px;
        }

        .reacciones a:hover {
            text-decoration: underline;
        }

        .reacciones a[disabled] {
            color: #cbd5e0;
            pointer-events: none;
        }

        .contenedor-foto {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            overflow: hidden;
            /* Recorta la imagen para que no sobresalga del círculo */
            border: 3px solid #ef4444;
            /* Borde opcional */
        }

        .foto-perfil {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Ajusta la imagen al contenedor */
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Formulario para crear publicaciones (izquierda) -->
        <form id="form-publicacion">
            <h3>Crear Publicación</h3>
            <input type="text" name="titulo" placeholder="Título" required><br>
            <textarea name="contenido" placeholder="Contenido de la publicación" required></textarea><br>
            <button type="submit">Crear Publicación</button>
            <h3>Bienvenido, <?php echo $_SESSION['usuario']; ?>!</h3>
            <div class="contenedor-foto">
                <img src="data:image/png;base64,<?php echo $_SESSION['foto']; ?>" alt="Foto de perfil"
                    class="foto-perfil">
            </div>
        </form>

        <!-- Contenedor de publicaciones (derecha) -->
        <div id="publicaciones-container">
            <h3>Publicaciones:</h3>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="post">
                    <div class="post-header">
                        <img src="data:image/png;base64,<?php echo $row['foto_url']; ?>" alt="Foto de perfil" class="post-img">
                        <div class="post-usuario"><?php echo $row['nombre_usuario']; ?></div>
                    </div>
                    <h4><?php echo $row['titulo']; ?></h4>
                    <p><?php echo $row['contenido']; ?></p>
                    <div class="reacciones">
                        <span>Likes: <?php echo $row['likes']; ?> | Dislikes: <?php echo $row['dislikes']; ?></span>
                        <br>
                        <!-- Verificar si el usuario ya reaccionó -->
                        <a href="publicaciones.php?accion=like&id=<?php echo $row['id']; ?>" <?php if (usuario_reacciono($usuario_id, $row['id']))
                               echo 'disabled'; ?>>Like</a> |
                        <a href="publicaciones.php?accion=dislike&id=<?php echo $row['id']; ?>" <?php if (usuario_reacciono($usuario_id, $row['id']))
                               echo 'disabled'; ?>>Dislike</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>