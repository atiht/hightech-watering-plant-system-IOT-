<?php 
    session_start();
    if(!isset($_SESSION['UserLogin'])) {
        header("location: /hightech/signin");
        exit;
    }
?>