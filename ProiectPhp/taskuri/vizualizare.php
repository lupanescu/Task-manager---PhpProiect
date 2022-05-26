<?php
include("../include/dbconnect.php");


function autoParagraph($text)
{
    if (!$text) $text = '';
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

function myTruncate($string, $limit, $break = ".", $pad = "...")
{
    // return with no change if string is shorter than $limit
    if (strlen($string) <= $limit) return $string;

    // is $break present between $limit and the end of the string?
    if (false !== ($breakpoint = strpos($string, $break, $limit))) {
        if ($breakpoint < strlen($string) - 1) {
            $string = substr($string, 0, $breakpoint) . $pad;
        }
    }

    return $string;
}

?>
<!doctype html>
<html lang="en">
<?php
include("../include/html_head.php");
?>
<body>

<style>
    .bdp-input {
        border-radius: 2px;
        padding: 0 3px;
        border: 1px solid rgba(34, 36, 38, .15);
        cursor: pointer;
    }

    .bdp-input.disabled {
        color: #AAA;
        cursor: default;
    }

    .bdp-popover {
        min-width: 110px;
    }

    .bdp-popover input {
        display: inline;
        margin-bottom: 3px;
        width: 60px;
    }

    .bdp-block {
        display: inline-block;
        line-height: 1;
        text-align: center;
        padding: 5px 3px;
    }

    .bdp-label {
        font-size: 70%;
    }

    .container {
        padding-top: 80px;
    }
</style>

<?php
include("../include/header.php");
?>


<?php

if (!isset($_GET['id'])) {
    die('Campul ID este necesar');
}

$query = $db->prepare("select t.*, cd.nume as creat_de_nume, cd.id as creat_de_id, ua.nume as utilizator_asignat_nume, ua.id as utilizator_asignat_id from taskuri t left join utilizatori ua on ua.id=t.utilizator_asignat left join utilizatori cd on cd.id = t.creat_de where t.id=?");
if (!$query->bind_param("i", $_GET['id'])) {
    die('Could not bind the parameters');
}

if (!$query->execute()) {
    die('mysql execute error: ' . mysqli_stmt_error($query));
}

if (!$result = $query->get_result()) {
    die('mysql result error: ' . mysqli_stmt_error($query));
}

if (!$row = $result->fetch_assoc()) {
    die("Obiectul nu exista");
}

$task_id = $row['id'];


?>

<div class="modal" id="modalAdaugareComentariu" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adaugare Comentariu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formularAdaugareComentariu">
                    <fieldset>
                        <input type="hidden" name="actiune" value="adaugare_comentariu">
                        <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                        <div class="form-group">
                            <label for="continut">Continut</label>
                            <textarea rows="10" required minlength="20" maxlength="5000" class="form-control"
                                      name="continut" id="continut"></textarea>
                        </div>
                    </fieldset>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#formularAdaugareComentariu').submit();">
                    Salvare
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="modalAdaugareTimp" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adaugare timp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formularAdaugareTimp">
                    <input type="hidden" name="actiune" value="adaugare_timp">
                    <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                    <div class="form-group">
                        <label for="timp_adaugat">Timp</label>
                        <input type="text" class="form-control" name="timp" id="timp_adaugat">
                    </div>
                    <div class="form-group">
                        <label for="timp_inceput">Inceput la:</label>
                        <input type="datetime-local" required class="form-control" name="inceput" id="timp_inceput">
                    </div>
                    <div class="form-group">
                        <label for="observatii">Observatii</label>
                        <textarea rows="3" class="form-control" name="observatii" id="observatii"></textarea>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#formularAdaugareTimp').submit();">
                    Salvare
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="modalEditareComentariu" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editare Comentariu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formularEditareComentariu">
                    <input type="hidden" name="actiune" value="editare_comentariu">
                    <input type="hidden" name="id" id="editareComentariuId">
                    <div class="form-group">
                        <label for="editareComentariuContinut">Continut</label>
                        <textarea rows="10" required minlength="20" maxlength="5000" class="form-control"
                                  name="continut"
                                  id="editareComentariuContinut"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#formularEditareComentariu').submit();">
                    Salvare
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modalVizualizareComentariu" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vizualizare Comentariu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="vizualizareComentariuContinut">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modalEditareTimp" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editare timp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formularEditareTimp">
                    <input type="hidden" name="actiune" value="editare_timp">
                    <input type="hidden" name="id_timp" id="editareTimpId">
                    <div class="form-group">
                        <label for="editareTimpCamp">Timp</label>
                        <input type="text" class="form-control" name="timp" id="editareTimpCamp">
                    </div>
                    <div class="form-group">
                        <label for="editareTimpInceput">Inceput la:</label>
                        <input type="datetime-local" required class="form-control" name="inceput"
                               id="editareTimpInceput">
                    </div>
                    <div class="form-group">
                        <label for="editareTimpObservatii">Observatii</label>
                        <textarea rows="3" class="form-control" name="observatii" id="editareTimpObservatii"></textarea>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#formularEditareTimp').submit();">
                    Salvare
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container-xxl mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class=" font-size-18">Vizualizare Task
                    <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN): ?>
                    <span class="ms-3">
                        <a href="editare.php?id=<?= $row['id'] ?>"><i class="bx bx-edit"></i> </a>
                        <a onclick="stergere(<?= $row['id'] ?>)" href="#"><i class="bx bx-trash"></i> </a>
                    </span>
                    <?php endif; ?>
                </h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">App</a></li>
                        <li class="breadcrumb-item active"><a href="/taskuri">Taskuri</a></li>
                        <li class="breadcrumb-item active">Vizualizare</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <?php
    $days = intval($row['timp_estimativ'] / (24 * 60 * 60));
    $hours = intval(($row['timp_estimativ'] % (24 * 60 * 60)) / (60 * 60));
    $minutes = intval(($row['timp_estimativ'] % (60 * 60)) / 60);
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pb-5">
                    <h4 class="card-title">Detalii</h4>
                    <table class="table-responsive py-1 mb-4">
                        <tr>
                            <th style="min-width: 150px !important;">ID</th>
                            <td><?= $row['id'] ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Titlu</th>
                            <td><?= $row['titlu'] ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Dificultate</th>
                            <td><?= $row['dificultate'] ?></td>
                        </tr>

                        <tr>
                            <th style="min-width: 120px !important;">Timp Estimativ</th>
                            <td><?= $days . 'd ' . $hours . 'h ' . $minutes . 'm' ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Utilizator Asignat</th>
                            <td><?= $row['utilizator_asignat_id'] ? '<a href="/utilizatori/vizualizare.php?id=' . $row['utilizator_asignat_id'] . '">' . $row['utilizator_asignat_nume'] . '</a>' : '' ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Creat de</th>
                            <td><?= $row['creat_de_id'] ? '<a href="/utilizatori/vizualizare.php?id=' . $row['creat_de_id'] . '">' . $row['creat_de_nume'] . '</a>' : '' ?></td>
                        </tr>
                        <tr>
                            <th style="min-width: 120px !important;">Creat la</th>
                            <td><?= $row['creat_la'] ?></td>
                        </tr>
                    </table>

                    <h6>Descriere</h6>
                    <blockquote><?= autoParagraph($row['descriere']) ?></blockquote>

                </div>
            </div>
        </div>


        <div class="col-12">
            <div class="card">
                <div class="card-body pb-5">

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab_comentarii" data-bs-toggle="tab"
                                    data-bs-target="#comentarii" type="button" role="tab" aria-controls="comentarii"
                                    aria-selected="true">Comentarii
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_timp_inregistrat" data-bs-toggle="tab"
                                    data-bs-target="#timp_inregistrat" type="button" role="tab"
                                    aria-controls="timp_inregistrat" aria-selected="false">Timp Inregistrat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_istoric" data-bs-toggle="tab" data-bs-target="#istoric"
                                    type="button" role="tab" aria-controls="istoric" aria-selected="false">Istoric
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="comentarii" role="tabpanel"
                             aria-labelledby="tab_comentarii">
                            <div class="row">
                                <?php


                                $result = mysqli_query($db, "select c.id, c.creat_la, c.continut, u.nume as nume_utilizator, u.id as id_utilizator from comentarii c left join utilizatori u on u.id = c.id_utilizator where c.task_id=" . $row['id'] . " order by c.creat_la DESC");

                                if (!$result)
                                    die("Eroare mysql: " . mysqli_error($db));


                                ?>
                                <div class="float-start mt-4">
                                    <div class="btn-group">
                                        <a href="javascript:void(0)" data-bs-toggle="modal"
                                           data-bs-target="#modalAdaugareComentariu"
                                           class="btn btn-secondary"><span>Comentariu Nou</span></a>
                                    </div>
                                </div>

                                <div class="col-sm-12 table table-responsive">
                                    <table class="table table-striped cakeTable cakeTr-inline my-3">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Utilizator</th>
                                            <th>Creat La</th>
                                            <th>Continut</th>
                                            <th>Actiuni</th>
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['id_utilizator'] ? '<a href="/utilizatori/vizualizare.php?id=' . $row['id_utilizator'] . '">' . $row['nume_utilizator'] . '</a>' : '' ?></td>
                                                <td><?= $row['creat_la'] ?></td>
                                                <td><?= myTruncate($row['continut'], 70, ' ') ?></td>
                                                <td class="actions">
                                                    <a href="javascript:vizualizareComentariu(<?= $row['id'] ?>)">Vizualizare</a>
                                                    <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN): ?>
                                                    <a href="javascript:editareComentariu(<?= $row['id'] ?>)">Editare</a>
                                                    <a onclick="stergereComentariu(<?= $row['id'] ?>)" href="#">Stergere</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="timp_inregistrat" role="tabpanel"
                             aria-labelledby="tab_timp_inregistrat">
                            <div class="row">
                                <?php


                                $result = mysqli_query($db, "select ti.id, ti.timp, ti.inceput, ti.observatii, u.nume as nume_utilizator, u.id as id_utilizator from timp_inregistrat ti left join utilizatori u on u.id = ti.id_utilizator where ti.task_id=" . $task_id . " order by ti.id DESC");

                                if (!$result)
                                    die("Eroare mysql: " . mysqli_error($db));


                                ?>
                                <div class="float-start mt-4">
                                    <div class="btn-group">
                                        <a href="javascript:void(0)" data-bs-toggle="modal"
                                           data-bs-target="#modalAdaugareTimp"
                                           class="btn btn-secondary"><span>Adaugare timp</span></a>
                                    </div>
                                </div>
                                <div class="col-sm-12 table table-responsive">
                                    <table class="table table-striped cakeTable cakeTr-inline my-3">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Utilizator</th>
                                            <th>Timp</th>
                                            <th>Inceput</th>
                                            <th>Observatii</th>
                                            <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN): ?>
                                            <th>Actiuni</th>
                                            <?php endif; ?>
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <?php
                                            $days = intval($row['timp'] / (24 * 60 * 60));
                                            $hours = intval(($row['timp'] % (24 * 60 * 60)) / (60 * 60));
                                            $minutes = intval(($row['timp'] % (60 * 60)) / 60);
                                            ?>
                                            <tr>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['id_utilizator'] ? '<a href="/utilizatori/vizualizare.php?id=' . $row['id_utilizator'] . '">' . $row['nume_utilizator'] . '</a>' : '' ?></td>
                                                <td><?= $days . 'd ' . $hours . 'h ' . $minutes . 'm' ?></td>
                                                <td><?= $row['inceput'] ?> </td>
                                                <td><?= myTruncate($row['observatii'], 70, ' ') ?></td>
                                                <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN): ?>
                                                <td class="actions">
                                                    <a href="javascript:editareTimp(<?= $row['id'] ?>)">Editare</a>
                                                    <a onclick="stergereTimp(<?= $row['id'] ?>)"
                                                       href="#">Stergere</a>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endwhile; ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="istoric" role="tabpanel" aria-labelledby="tab_istoric">
                            <div class="row">
                                <?php


                                $result = mysqli_query($db, "select tl.id, tl.creat_la, tl.mesaj, u.nume as nume_utilizator, u.id as id_utilizator from task_logs tl left join utilizatori u on u.id = tl.id_utilizator where tl.task_id=" . $task_id . " order by tl.creat_la DESC");

                                if (!$result)
                                    die("Eroare mysql: " . mysqli_error($db));


                                ?>
                                <div class="col-sm-12 table table-responsive">
                                    <table class="table table-striped cakeTable cakeTr-inline my-3">
                                        <thead>
                                        <tr>

                                            <th>Data</th>
                                            <th>Utilizator</th>
                                            <th>Mesaj</th>
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?= $row['creat_la'] ?></td>
                                                <td><?= $row['id_utilizator'] ? '<a href="/utilizatori/vizualizare.php?id=' . $row['id_utilizator'] . '">' . $row['nume_utilizator'] . '</a>' : '' ?></td>
                                                <td><?= myTruncate($row['mesaj'], 70, ' ') ?></td>
                                            </tr>
                                        <?php endwhile; ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

    </div>

</div>
<?php
include("../include/footer.php");
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script>

    var editorTimp;
    function stergere(id) {
        $.post("actions.php", {actiune: 'stergere', id: id}, function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    type: 'error',
                    text: result.error,
                })
            } else {
                location.href = "index.php";
            }
        })
    }

    function stergereComentariu(id) {
        $.post("actions.php", {actiune: 'stergere_comentariu', id: id}, function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    type: 'error',
                    text: result.error,
                })
            } else {
                location.reload();
            }
        })
    }


    $("#formularAdaugareComentariu").submit(function (event) {
        //if (!$("#formularAdaugareComentariu").validate())
            //return;
        event.preventDefault();

        $.post("actions.php", $("#formularAdaugareComentariu").serialize(), function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            } else {
                location.reload();
            }
        })
    });

    function editareComentariu(id) {
        $.post("actions.php", {'actiune': 'vizualizare_comentariu', 'id': id}, function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            } else {
                $('#editareComentariuContinut').val(result.continut);
                $('#editareComentariuId').val(id);
                $('#modalEditareComentariu').modal('show');
            }
        })
    }

    function vizualizareComentariu(id) {
        $.post("actions.php", {'actiune': 'vizualizare_comentariu', 'id': id, 'autoparagraph': 1}, function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            } else {

                $("#vizualizareComentariuContinut").html(result.continut);
                $("#modalVizualizareComentariu").modal('show');
            }
        })
    }

    $("#formularEditareComentariu").submit(function (event) {
        event.preventDefault();

        $.post("actions.php", $("#formularEditareComentariu").serialize(), function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            } else {
                location.reload();
            }
        })
    });


    $("#formularAdaugareTimp").submit(function (event) {
        event.preventDefault();

        $.post("actions.php", $("#formularAdaugareTimp").serialize(), function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            } else {
                location.reload();
            }
        })
    });

    function editareTimp(id) {
        $.post("actions.php", {'actiune': 'vizualizare_timp', 'id': id}, function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            } else {
                $('#editareTimpCamp').val(result.timp);
                $('#editareTimpInceput').val(result.inceput);
                $('#editareTimpObservatii').val(result.observatii);
                $('#editareTimpId').val(id);
                $('#modalEditareTimp').modal('show');
            }
        })
    }

    $("#formularEditareTimp").submit(function (event) {
        event.preventDefault();

        $.post("actions.php", $("#formularEditareTimp").serialize(), function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            } else {
                location.reload();
            }
        })
    });

    function stergereTimp(id) {
        $.post("actions.php", {actiune: 'stergere_timp', id: id}, function (response) {
            var result = JSON.parse(response);
            if (!result.success) {
                Swal.fire({
                    type: 'error',
                    text: result.error,
                })
            } else {
                location.reload();
            }
        })
    }
</script>


<script>
    (function ($) {

        var langs = {
            en: {
                day: 'day',
                hour: 'hour',
                minute: 'minute',
                second: 'second',
                days: 'days',
                hours: 'hours',
                minutes: 'minutes',
                seconds: 'seconds'
            },
            es: {
                day: 'd&iacute;a',
                hour: 'hora',
                minute: 'minuto',
                second: 'segundo',
                days: 'd&iacute;s',
                hours: 'horas',
                minutes: 'minutos',
                seconds: 'segundos'
            }
        };

        $.fn.durationPicker = function (options) {
                // Store an instance of moment duration
                var totalDuration = 0;

                var defaults = {
                    lang: 'en',
                    max: 59,
                    checkRanges: false,
                    totalMax: 31556952000, // 1 year
                    totalMin: 60000, // 1 minute
                    showSeconds: false,
                    showDays: true
                };

                var settings = $.extend({}, defaults, options);

                this.each(function (i, mainInput) {

                    var $mainInput = $(mainInput);

                    if ($mainInput.data('bdp') === '1') {
                        return;
                    }

                    function buildDisplayBlock(id, hidden) {
                        return '<div class="bdp-block ' + (hidden ? 'hidden' : '') + '">' +
                            '<span id="bdp-' + id + '"></span><br>' +
                            '<span class="bdp-label" id="' + id + '_label"></span>' +
                            '</div>';
                    }

                    var $mainInputReplacer = $('<div class="bdp-input"></div>');
                    $mainInputReplacer.append(buildDisplayBlock('days', !settings.showDays));
                    $mainInputReplacer.append(buildDisplayBlock('hours'));
                    $mainInputReplacer.append(buildDisplayBlock('minutes'));
                    $mainInputReplacer.append(buildDisplayBlock('seconds', !settings.showSeconds));

                    $mainInput.after($mainInputReplacer).hide().data('bdp', '1');

                    var inputs = [];

                    var disabled = false;
                    if ($mainInput.hasClass('disabled') || $mainInput.attr('disabled') == 'disabled') {
                        disabled = true;
                        $mainInputReplacer.addClass('disabled');
                    }

                    function updateMainInput() {
                        $mainInput.val(totalDuration.asMilliseconds());
                        $mainInput.change();
                    }

                    function updateMainInputReplacer() {
                        $mainInputReplacer.find('#bdp-days').text(totalDuration.days());
                        $mainInputReplacer.find('#bdp-hours').text(totalDuration.hours());
                        $mainInputReplacer.find('#bdp-minutes').text(totalDuration.minutes());
                        $mainInputReplacer.find('#bdp-seconds').text(totalDuration.seconds());

                        $mainInputReplacer.find('#days_label').text(langs[settings.lang][totalDuration.days() == 1 ? 'day' : 'days']);
                        $mainInputReplacer.find('#hours_label').text(langs[settings.lang][totalDuration.hours() == 1 ? 'hour' : 'hours']);
                        $mainInputReplacer.find('#minutes_label').text(langs[settings.lang][totalDuration.minutes() == 1 ? 'minute' : 'minutes']);
                        $mainInputReplacer.find('#seconds_label').text(langs[settings.lang][totalDuration.seconds() == 1 ? 'second' : 'seconds']);
                    }

                    function updatePicker() {
                        if (disabled) {
                            return;
                        }
                        // Array of jQuery object inputs
                        inputs.days.val(totalDuration.days());
                        inputs.hours.val(totalDuration.hours());
                        inputs.minutes.val(totalDuration.minutes());
                        inputs.seconds.val(totalDuration.seconds());
                    }

                    function init() {
                        if (!$mainInput.val()) {
                            $mainInput.val(0);
                        }

                        // Initialize moment with locale
                        moment.locale(settings.lang);

                        totalDuration = moment.duration(parseInt($mainInput.val(), 10));
                        checkRanges();
                        updatePicker();
                    }

                    function picker_changed() {
                        totalDuration = moment.duration({
                            seconds: parseInt(inputs.seconds.val()),
                            minutes: parseInt(inputs.minutes.val()),
                            hours: parseInt(inputs.hours.val()),
                            days: parseInt(inputs.days.val())
                        });
                        checkRanges();
                        updateMainInput();
                    }

                    function buildNumericInput(label, hidden, max) {
                        var $input = $('<input class="form-control input-sm" type="number" min="0" value="0">')
                            .change(picker_changed);
                        if (max) {
                            $input.attr('max', max);
                        }
                        inputs[label] = $input;
                        var $ctrl = $('<div> ' + langs[settings.lang][label] + '</div>');
                        if (hidden) {
                            $ctrl.addClass('hidden');
                        }
                        return $ctrl.prepend($input);
                    }

                    function checkRanges() {
                        if (settings.checkRanges) {
                            // Assign max value if out of range
                            totalDuration = (totalDuration.asMilliseconds() > settings.totalMax) ? moment.duration(settings.totalMax) : totalDuration;
                            // Assign minimum value if out of range
                            totalDuration = (totalDuration.asMilliseconds() < settings.totalMin) ? moment.duration(settings.totalMin) : totalDuration;
                        }
                        // Always update input replacer
                        updateMainInputReplacer();
                    }

                    if (!disabled) {
                        var $picker = $('<div class="bdp-popover"></div>');
                        buildNumericInput('days', !settings.showDays).appendTo($picker);
                        buildNumericInput('hours', false, 23).appendTo($picker);
                        buildNumericInput('minutes', false, 59).appendTo($picker);
                        buildNumericInput('seconds', !settings.showSeconds, 59).appendTo($picker);

                        $mainInputReplacer.popover({
                            placement: 'bottom',
                            trigger: 'click',
                            html: true,
                            content: $picker
                        });
                    }
                    init();
                    $mainInput.change(init);
                });
        };
    }(jQuery));

    $(function () {
        $('#timp_adaugat').durationPicker();
        $('#editareTimpCamp').durationPicker();

    });
</script>
</body>
</html>