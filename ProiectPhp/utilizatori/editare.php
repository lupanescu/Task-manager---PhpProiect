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

//include("../includes/dbconnect.php");

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

?>

<div class="container-xxl mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="font-size-18">Editare Utilizator</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">App</a></li>
                        <li class="breadcrumb-item active"><a href="/utilizatori">Utilizatori</a></li>
                        <li class="breadcrumb-item active">Editare</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-body">
                    <form id="formularEditareUtilizator">
                        <input type="hidden" name="actiune" value="editare">
                        <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                        <fieldset>
                            <div class="form-group">
                                <label for="name">Nume</label>
                                <input id="name" value="<?= $row['nume'] ?>" maxlength="100" class="form-control" <?= $_SESSION['user_type'] != TIP_UTILIZATOR_ADMIN ? 'readonly' : '' ?> type="text" name="nume">
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" value="<?= $row['email'] ?>" maxlength="50" class="form-control" type="text" name="email">
                            </div>
                            <div class="form-group">
                                <label for="parola">Parola</label>
                                <input autocomplete="new-password" id="parola" minlength="5" maxlength="50" class="form-control" type="password" name="parola">
                            </div>

                            <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN): ?>
                            <div class="form-group">
                                <label for="tip">Tip Utilizator</label>
                                <select id="tip" name="tip" class="form-control">
                                    <option value="<?= TIP_UTILIZATOR_ANGAJAT ?>" <?= $row['tip'] == TIP_UTILIZATOR_ANGAJAT ? 'selected' : '' ?> >Angajat</option>
                                    <option value="<?= TIP_UTILIZATOR_ADMIN ?>" <?= $row['tip'] == TIP_UTILIZATOR_ADMIN ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </fieldset>
                        <div class="text-end mt-2">
                            <button type="submit" class="btn btn-primary">Salvare</button>
                        </div>

                    </form>


                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>

</div>
<?php
include("../include/footer.php");
?>

<script>
    $("#formularEditareUtilizator").submit(function(event) {
        event.preventDefault();

        $.post("actions.php", $("#formularEditareUtilizator").serialize(), function(response) {
            var result = JSON.parse(response);
            if(!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            }
            else {
                location.href = "vizualizare.php?id=<?= $_GET['id'] ?>";
            }
        })
    });
</script>

</body>
</html>