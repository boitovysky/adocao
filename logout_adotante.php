<?php
session_start();
unset($_SESSION['adotante_id']);
unset($_SESSION['adotante_nome']);
header('Location: index.php');
exit;
?>