<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['actiune'])) {
    header("Location: index.php");
    die;
}
include("../include/dbconnect.php");


if(empty($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Nu ai acces la aceasta pagina!';
    if($_SERVER['HTTP_REFERER']) {
        header("Location: ".$_SERVER['HTTP_REFERER']);
    }
    else {
        header("Location: /");
    }
    die();
}


if ($_POST['actiune'] == 'adaugare') {
    if($_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN)
        die('{"success":false, "error":"Nu ai acces la aceasta actiune"}');
    $parola = md5($_POST['parola']);
    $query = $db->prepare("insert into utilizatori (nume, email, tip, parola) values (?, ?, ?, ?)");
    if (!$query->bind_param("ssis", $_POST['nume'], $_POST['email'], $_POST['tip'], $parola)) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }
    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }


    die('{"success":true, "id":' . mysqli_insert_id($db) . '}');
}

if ($_POST['actiune'] == 'editare') {
    if($_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN && $_SESSION['user_id'] != $_POST['id'])
        die('{"success":false, "error":"Nu ai acces la aceasta actiune"}');
    if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN) {
        if (empty($_POST['parola'])) {
            $query = $db->prepare("update utilizatori set nume=?, email=?, tip=? where id=?");
            if (!$query->bind_param("ssii", $_POST['nume'], $_POST['email'], $_POST['tip'], $_POST['id'])) {
                die('{"success":false, "error":"Could not bind the parameters"}');
            }
        } else {
            $parola = md5($_POST['parola']);
            $query = $db->prepare("update utilizatori set nume=?, email=?, tip=?, parola=? where id=?");
            if (!$query->bind_param("ssisi", $_POST['nume'], $_POST['email'], $_POST['tip'], $parola, $_POST['id'])) {
                die('{"success":false, "error":"Could not bind the parameters"}');
            }
        }
    }
    else {
        if (empty($_POST['parola'])) {
            $query = $db->prepare("update utilizatori set email=?, where id=?");
            if (!$query->bind_param("si", $_POST['email'],  $_POST['id'])) {
                die('{"success":false, "error":"Could not bind the parameters"}');
            }
        } else {
            $parola = md5($_POST['parola']);
            $query = $db->prepare("update utilizatori set email=?, parola=? where id=?");
            if (!$query->bind_param("ssi", $_POST['email'],  $parola, $_POST['id'])) {
                die('{"success":false, "error":"Could not bind the parameters"}');
            }
        }
    }


    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    die('{"success":true}');
}

if ($_POST['actiune'] == 'stergere') {
    if($_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN)
        die('{"success":false, "error":"Nu ai acces la aceasta actiune"}');
    $query = $db->prepare("delete from utilizatori where id=?");
    if (!$query->bind_param("i", $_POST['id'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }
    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    die('{"success":true}');
}
