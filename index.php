<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>PMI BO Tool</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Informace o PMI zásilkách" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script
        src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc="
        crossorigin="anonymous">
    </script>
    </head>

<body>
    <header>
    <h1>PMI BO Tool</h1>
    <?php require 'navigation.php'; ?>
    <img src="images/logo.jpg" class="responsive"/>
    </header> 
<?php 
session_start();
if (isset($_SESSION)) {session_destroy();}
?>
</body>