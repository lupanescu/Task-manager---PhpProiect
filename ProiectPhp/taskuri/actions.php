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



function adaugare_istoric($task_id, $mesaj)
{
    global $db;
    $query = $db->prepare("insert into task_logs (task_id, creat_la, id_utilizator, mesaj) values (?, NOW(), ?, ?)");
    if (!$query->bind_param("iis", $task_id, $_SESSION['user_id'], $mesaj )) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }
    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

}

function task_id_din_comentariu($id_comentariu)
{
    global $db;
    $query = $db->prepare("select task_id from comentarii where id=?");
    if(!$query->bind_param("i", $id_comentariu)) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if(!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    if(!$result = $query->get_result()) {
        die('{"success":false, "error":"mysql result error: ' . mysqli_stmt_error($query) . '"}');
    }

    if(!$row = $result->fetch_assoc()) {
        die('{"success":false, "error":"obiectul nu exista task id din comentariu"}');
    }

    return $row['task_id'];

}

function task_id_din_timp($id_timp)
{
    global $db;
    $query = $db->prepare("select task_id from timp_inregistrat where id=?");
    if(!$query->bind_param("i", $id_timp)) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if(!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    if(!$result = $query->get_result()) {
        die('{"success":false, "error":"mysql result error: ' . mysqli_stmt_error($query) . '"}');
    }

    if(!$row = $result->fetch_assoc()) {
        die('{"success":false, "error":"obiectul nu exista task id din timp"}');
    }
    return $row['task_id'];

}

function autoParagraph($text)
{
    if(!$text) $text = '';
    if (trim($text) !== '') {
        $text = preg_replace('|<br[^>]*>\s*<br[^>]*>|i', "\n\n", $text . "\n");
        $text = preg_replace("/\n\n+/", "\n\n", str_replace(["\r\n", "\r"], "\n", $text));
        $texts = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $text = '';
        foreach ($texts as $txt) {
            $text .= '<p>' . nl2br(trim($txt, "\n")) . "</p>\n";
        }
        $text = preg_replace('|<p>\s*</p>|', '', $text);
    }

    return $text;
}

if ($_POST['actiune'] == 'adaugare') {

    $_POST['timp_estimativ'] = (int)($_POST['timp_estimativ']/1000);
    if(!empty($_POST['utilizator_asignat'])) {
        $query = $db->prepare("insert into taskuri (titlu, descriere, dificultate, timp_estimativ, creat_de, creat_la, utilizator_asignat) values (?, ?, ?, ?, ?, NOW(), ?)");
        if (!$query->bind_param("ssiiii", $_POST['titlu'], $_POST['descriere'], $_POST['dificultate'], $_POST['timp_estimativ'], $_SESSION['user_id'], $_POST['utilizator_asignat'])) {
            die('{"success":false, "error":"Could not bind the parameters"}');
        }
    }
    else {
        $query = $db->prepare("insert into taskuri (titlu, descriere, dificultate, timp_estimativ, creat_de, creat_la) values (?, ?, ?, ?, ?, NOW())");
        if (!$query->bind_param("ssiii", $_POST['titlu'], $_POST['descriere'], $_POST['dificultate'], $_POST['timp_estimativ'], $_SESSION['user_id'])) {
            die('{"success":false, "error":"Could not bind the parameters"}');
        }
    }

    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    $task_id = mysqli_insert_id($db);
    adaugare_istoric($task_id, "Task adaugat");
    die('{"success":true, "id":' . $task_id . '}');
}

if ($_POST['actiune'] == 'editare') {
    $_POST['timp_estimativ'] = (int)($_POST['timp_estimativ']/1000);
    if(!empty($_POST['utilizator_asignat'])) {
        $query = $db->prepare("update taskuri set titlu=?, descriere=?, dificultate=?, timp_estimativ=?, utilizator_asignat=? where id=?");
        if (!$query->bind_param("ssiiii", $_POST['titlu'], $_POST['descriere'], $_POST['dificultate'], $_POST['timp_estimativ'], $_POST['utilizator_asignat'], $_POST['id'])) {
            die('{"success":false, "error":"Could not bind the parameters"}');
        }
    } else {
        $query = $db->prepare("update taskuri set titlu=?, descriere=?, dificultate=?, timp_estimativ=?, utilizator_asignat=NULL where id=?");
        if (!$query->bind_param("ssiii", $_POST['titlu'], $_POST['descriere'], $_POST['dificultate'], $_POST['timp_estimativ'], $_POST['id'])) {
            die('{"success":false, "error":"Could not bind the parameters"}');
        }
    }

    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    adaugare_istoric($_POST['id'], "Task editat");

    die('{"success":true}');
}

if ($_POST['actiune'] == 'stergere') {
    if($_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN)
        die('{"success":false, "error":"Nu ai acces la aceasta actiune"}');
    $query = $db->prepare("delete from taskuri where id=?");
    if (!$query->bind_param("i", $_POST['id'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }
    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    //adaugare_istoric($_POST['id'], "Task sters");

    die('{"success":true}');
}

if ($_POST['actiune'] == 'adaugare_comentariu') {
    $query = $db->prepare("insert into comentarii (task_id, id_utilizator, creat_la, continut) values (?, ?, NOW(), ?)");
    if (!$query->bind_param("iis", $_POST['task_id'], $_SESSION['user_id'], $_POST['continut'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    adaugare_istoric($_POST['task_id'], "Comentariu adaugat");
    die('{"success":true, "id":' . mysqli_insert_id($db) . '}');
}

if ($_POST['actiune'] == 'editare_comentariu') {
    if($_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN)
        die('{"success":false, "error":"Nu ai acces la aceasta actiune"}');
    $query = $db->prepare("update comentarii set continut=? where id=?");
    if (!$query->bind_param("si", $_POST['continut'], $_POST['id'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }



    adaugare_istoric(task_id_din_comentariu($_POST['id']), "Comentariu editat");

    die('{"success":true}');
}

if ($_POST['actiune'] == 'vizualizare_comentariu') {


    $query = $db->prepare("select continut from comentarii where id=?");
    if(!$query->bind_param("i", $_POST['id'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if(!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    if(!$result = $query->get_result()) {
        die('{"success":false, "error":"mysql result error: ' . mysqli_stmt_error($query) . '"}');
    }

    if(!$row = $result->fetch_assoc()) {
        die('{"success":false, "error":"obiectul nu exista vizualizare comentariu"}');
    }
    $r = ['success' => true, 'continut' => $row['continut']];
    if(!empty($_POST['autoparagraph']))
        $r['continut'] = autoParagraph($r['continut']);
    die(json_encode($r));

}

if ($_POST['actiune'] == 'stergere_comentariu') {
    if($_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN)
        die('{"success":false, "error":"Nu ai acces la aceasta actiune"}');
    $task_id = task_id_din_comentariu($_POST['id']);
    $query = $db->prepare("delete from comentarii where id=?");
    if (!$query->bind_param("i", $_POST['id'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }
    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    adaugare_istoric($task_id, "Comentariu sters");
    die('{"success":true}');
}

if ($_POST['actiune'] == 'adaugare_timp') {

    $_POST['timp'] = (int)($_POST['timp']/1000);
    $query = $db->prepare("insert into timp_inregistrat (task_id, id_utilizator, timp, inceput, observatii) values (?, ?, ?, ?, ?)");
    if (!$query->bind_param("iiiss", $_POST['task_id'], $_SESSION['user_id'], $_POST['timp'], $_POST['inceput'], $_POST['observatii'] )) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    adaugare_istoric($_POST['task_id'], "Timp adaugat");
    die('{"success":true, "id":' . mysqli_insert_id($db) . '}');
}

if ($_POST['actiune'] == 'editare_timp') {
    if($_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN)
        die('{"success":false, "error":"Nu ai acces la aceasta actiune"}');
    $query = $db->prepare("update timp_inregistrat set timp=?, inceput=?, observatii=? where id=?");
    $_POST['timp'] = (int)($_POST['timp']/1000);
    if (!$query->bind_param("issi", $_POST['timp'], $_POST['inceput'], $_POST['observatii'], $_POST['id_timp'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }
    adaugare_istoric(task_id_din_timp($_POST['id_timp']), "Timp editat");
    die('{"success":true}');
}

if ($_POST['actiune'] == 'vizualizare_timp') {


    $query = $db->prepare("select timp, inceput, observatii from timp_inregistrat where id=?");
    if(!$query->bind_param("i", $_POST['id'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }

    if(!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    if(!$result = $query->get_result()) {
        die('{"success":false, "error":"mysql result error: ' . mysqli_stmt_error($query) . '"}');
    }

    if(!$row = $result->fetch_assoc()) {
        die('{"success":false, "error":"obiectul nu exista vizualizare timp"}');
    }

    $r = ['success' => true, 'timp' => $row['timp']*1000, 'inceput' => str_replace(' ', 'T', $row['inceput']), 'observatii'=>$row['observatii']];
    if(!empty($_POST['autoparagraph']))
        $r['observatii'] = autoParagraph($r['observatii']);
    die(json_encode($r));

}

if ($_POST['actiune'] == 'stergere_timp') {

    $task_id = task_id_din_timp($_POST['id']);
    $query = $db->prepare("delete from timp_inregistrat where id=?");
    if (!$query->bind_param("i", $_POST['id'])) {
        die('{"success":false, "error":"Could not bind the parameters"}');
    }
    if (!$query->execute()) {
        die('{"success":false, "error":"mysql execute error: ' . mysqli_error($db) . '"}');
    }

    adaugare_istoric($task_id, "Timp sters");
    die('{"success":true}');
}