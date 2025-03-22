<?php
session_start();
session_unset();
session_destroy();
echo "Has cerrado sesión. <a href='login.html'>Iniciar sesión</a>";
?>
