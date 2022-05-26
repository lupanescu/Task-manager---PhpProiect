<?php
include("../include/dbconnect.php");

$search = '';
if(isset($_POST['search']))
    $search = $_POST['search'];
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
<div class="container-xxl mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="font-size-18">Utilizatori</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">App</a></li>
                        <li class="breadcrumb-item active">Utilizatori</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                        <div class="overflow-auto">
                            <div class="float-start">
                                <div class="btn-group">
                                    <a href="adaugare.php"
                                       class="btn btn-primary"><span>Utilizator Nou</span></a>
                                </div>
                            </div>
                            <div class="float-end">
                                <div class="text-end">
                                    <form id="formularCautare" action="?page=1" method="post">
                                        <label class="text-left font-weight-normal mb-2">Cautare:</label>
                                        <input type="text" name="search" value="<?= $search ?>" class="form-control form-control-sm d-inline-block w-auto ml-2">
                                    </form>

                                </div>
                            </div>
                        </div>

                    <div class="row">
                        <?php


                        $limit = 5;
                        $page = 1;
                        if(isset($_GET['page']))
                            $page = $_GET['page'];
                        if($page < 1) $page = 1;
                        $offset = ($page - 1) * $limit;

                        $where = "";

                        if($search) {
                            $search = '%'.mysqli_real_escape_string($db, $search).'%';
                            $where = "where u.nume like '".$search."' or u.email like '".$search."'";
                        }


                        $result = mysqli_query($db, "select u.id, u.nume, u.email, u.tip from utilizatori u ".$where." limit ".$limit." offset ".$offset);

                        if(!$result)
                            die("Eroare mysql: ".mysqli_error($db));

                        $intrari_afisate = mysqli_num_rows($result);

                        $tipuri_utilizator = [TIP_UTILIZATOR_ANGAJAT => 'Angajat', TIP_UTILIZATOR_ADMIN => 'Admin'];

                        ?>
                        <div class="col-sm-12 table table-responsive">
                            <table class="table table-striped cakeTable cakeTr-inline my-3">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nume</th>
                                    <th>Email</th>
                                    <th>Tip</th>
                                    <th>Actiuni</th>
                                </tr>
                                </thead>


                                <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= $row['nume'] ?></td>
                                        <td><?= $row['email'] ?></td>
                                        <td><?= $tipuri_utilizator[$row['tip']] ?></td>
                                        <td class="actions">
                                            <a href="vizualizare.php?id=<?= $row['id'] ?>">Vizualizare</a>
                                            <a href="editare.php?id=<?= $row['id'] ?>">Editare</a>
                                            <a onclick="stergere(<?= $row['id'] ?>)" href="#">Stergere</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php
                    $sql = "select u.id from utilizatori u ".$where;
                    $result = mysqli_query($db, $sql);
                    if(!$result)
                        die("Eroare mysql: ".mysqli_error($db));
                    $total_intrari = mysqli_num_rows($result);
                    $total_pagini = ceil($total_intrari/$limit);


                    ?>

                    <div class="">
                        <div class="float-start">
                            Pagina <?= $page ?> din <?= $total_pagini ?>, afisare <?= $intrari_afisate ?> intrari din totalul de <?= $total_intrari ?>
                        </div>
                        <div class="float-end">
                            <ul class="pagination pagination-rounded text-end">

                                <li class="paginate_button page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a onclick="schimbarePagina(<?= ($page-1) ?>)" href="#" class="page-link"><i class="mdi mdi-chevron-left"></i></a></li>

                                <?php for($i=1; $i <= $total_pagini; $i++): ?>
                                    <li class="paginate_button page-item <?= $page == $i ? 'active' : '' ?>"><a onclick="schimbarePagina(<?= $i ?>)" href="#" class="page-link"><?= $i ?></a></li>
                                <?php endfor; ?>

                                <li class="paginate_button page-item <?= $page >= $total_pagini ? 'disabled' : '' ?>"><a onclick="schimbarePagina(<?= ($page+1) ?>)" href="#" class="page-link"><i class="mdi mdi-chevron-right"></i></a></li>
                            </ul>
                        </div>

                    </div>


                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->

</div>
<?php
include("../include/footer.php");
?>

<script>
    function schimbarePagina(numar) {
        $("#formularCautare").prop('action', '?page='+numar);
        $("#formularCautare").submit();
    }

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
                location.reload();
            }
        })
    }
</script>

</body>
</html>