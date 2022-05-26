<?php

session_start();
$db = mysqli_connect("localhost", "root", "","acab");

const TIP_UTILIZATOR_ANGAJAT = 0;
const TIP_UTILIZATOR_ADMIN = 1;

$pagini = [
    '/taskuri/adaugare.php',
    '/taskuri/editare.php',
    '/taskuri/index.php',
    '/taskuri/proprii.php',
    '/taskuri/vizualizare.php',

    '/utilizatori/editare.php',
    '/utilizatori/vizualizare.php',
];

foreach ($pagini as $pagina) {
    //$db->query("insert into permisiuni (tip_utilizator, pagina) values (0, '".$pagina."')");
}
