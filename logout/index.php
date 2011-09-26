<?php
session_start();
session_unset();
session_destroy();
setcookie("simplemappr", "", time() - 3600, "/");
header('Location: http://' . $_SERVER['SERVER_NAME'] . '');
?>