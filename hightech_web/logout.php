<?php 
    session_start();
    session_destroy();
    header("location: /hightech/signin");
?>