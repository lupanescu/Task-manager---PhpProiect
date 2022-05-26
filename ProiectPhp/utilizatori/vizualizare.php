<?php
include("../include/dbconnect.php");

?>
<!doctype html>
<html lang="en">
<?php
include("../include/html_head.php");
?>
<body>
<?php
include("../include/header.php");
?>


<?php

if(!isset($_GET['id'])) {
    die('Campul ID este necesar');
}

$query = $db->prepare("select u.id, u.nume, u.email, u.tip from utilizatori u where u.id=?");
if(!$query->bind_param("i", $_GET['id'])) {
    die('Could not bind the parameters');
}

if(!$query->execute()) {
    die('mysql execute error: '.mysqli_stmt_error($query));
}

if(!$result = $query->get_result()) {
    die('mysql result error: '.mysqli_stmt_error($query));
}

if(!$row = $result->fetch_assoc()) {
    die("Obiectul nu exista");
}

$tipuri_utilizator = [TIP_UTILIZATOR_ANGAJAT => 'Angajat', TIP_UTILIZATOR_ADMIN => 'Admin'];

?>

<div class="container-xxl mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class=" font-size-18">Vizualizare Utilizator
                    <span class="ms-3">
                        <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN || $_SESSION['user_id'] == $row['id']): ?>
                                        <a href="editare.php?id=<?= $row['id'] ?>"><i class="bx bx-edit"></i> </a>
                        <?php endif; ?>
                        <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN): ?>
                                        <a onclick="stergere(<?= $row['id'] ?>)" href="#"><i class="bx bx-trash"></i> </a>
                        <?php endif; ?>
                                </span>
                </h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">App</a></li>
                        <li class="breadcrumb-item active"><a href="/utilizatori">Utilizatori</a></li>
                        <li class="breadcrumb-item active">Vizualizare</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pb-5">
                    <h4 class="card-title">Detalii</h4>
                    <table class="table-responsive py-1 mb-4">
                        <tr>
                            <th style="min-width: 120px !important;">ID</th>
                            <td><?= $row['id'] ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Nume</th>
                            <td><?= $row['nume'] ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Email</th>
                            <td><?= $row['email'] ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Tip</th>
                            <td><?= $tipuri_utilizator[$row['tip']] ?></td>
                        </tr>



                    </table>

                </div>
            </div>
        </div>





    </div>

</div>
<?php
include("../include/footer.php");
?>

<script>
    function stergere(id) {
        $.post("actions.php", {actiune:'stergere', id:id}, function(response) {
            var result = JSON.parse(response);
            if(!result.success) {
                Swal.fire({
                    type: 'error',
                    text: result.error,
                })
            }
            else {
                location.href = "index.php";
            }
        })
    }
</script>

</body>
</html>