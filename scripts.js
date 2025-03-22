// Función para obtener las publicaciones desde el servidor
function obtenerPublicaciones() {
    fetch('publicaciones.php?ajax=1')
        .then(response => response.json())
        .then(data => {
            const contenedor = document.getElementById('publicaciones-container');
            contenedor.innerHTML = ''; // Limpiar el contenedor

            // Renderizar las publicaciones
            data.forEach(publicacion => {
                const post = document.createElement('div');
                post.className = 'post';
                post.innerHTML = `
                    <div class="post-header">
                        <img src="data:image/png;base64,${publicacion.foto_url}" alt="Foto de perfil" class="post-img">
                        <div class="post-usuario">${publicacion.nombre_usuario}</div>
                    </div>
                    <h4>${publicacion.titulo}</h4>
                    <p>${publicacion.contenido}</p>
                    <div class="reacciones">
                        <span>Likes: ${publicacion.likes} | Dislikes: ${publicacion.dislikes}</span>
                        <br>
                        <a href="publicaciones.php?accion=like&id=${publicacion.id}">Like</a> | 
                        <a href="publicaciones.php?accion=dislike&id=${publicacion.id}">Dislike</a>
                    </div>
                `;
                contenedor.appendChild(post);
            });
        })
        .catch(error => console.error('Error al obtener publicaciones:', error));
}

// Función para enviar una nueva publicación
function crearPublicacion(titulo, contenido) {
    const formData = new FormData();
    formData.append('titulo', titulo);
    formData.append('contenido', contenido);

    fetch('publicaciones.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            obtenerPublicaciones(); // Actualizar la lista de publicaciones
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error al crear publicación:', error));
}

// Obtener publicaciones cada 5 segundos (polling)
setInterval(obtenerPublicaciones, 5000);

// Escuchar el envío del formulario
document.getElementById('form-publicacion').addEventListener('submit', function (e) {
    e.preventDefault();
    const titulo = document.querySelector('input[name="titulo"]').value;
    const contenido = document.querySelector('textarea[name="contenido"]').value;
    crearPublicacion(titulo, contenido);
});

// Cargar publicaciones al iniciar la página
document.addEventListener('DOMContentLoaded', obtenerPublicaciones);