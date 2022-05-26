<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['actiune'])) {
    header("Location: ../login/index.php");
    die;
}
include("../include/dbconnect.php");

if ($_POST['actiune'] == 'adaugare') {
    $parola = md5($_POST['parola']);
    $query = $db->prepare("insert into utilizatori (nume, email, parola) values (?, ?, ?)");
    if (!$query->bind_param("sss", $_POST['nume'], $_POST['email'], $parola)) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }
    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }


    die('{"success":true, "id":' . mysqli_insert_id($db) . '}');
}

if($_POST['actiune'] == 'login'){
    $parola = md5($_POST['parola']);

    $query = $db->prepare("select id, nume, tip from utilizatori where email=? and parola=?");
    if(!$query->bind_param("ss", $_POST['email'], $parola)) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if(!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    if(!$result = $query->get_result()) {
        die('{"success":false, "error":"mysql result error: ' . mysqli_stmt_error($query) . '"}');
    }

    if(!$row = $result->fetch_assoc()) {
        die('{"success":false, "error":"Username sau password incorect"}');
    }
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['user_name'] = $row['nume'];
    $_SESSION['user_type'] = $row['tip'];
    die('{"success":true}');
}


