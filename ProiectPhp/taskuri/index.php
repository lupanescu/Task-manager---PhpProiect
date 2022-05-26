<?php
include("../include/dbconnect.php");

$search = '';
if(isset($_POST['search']))
    $search = $_POST['search'];


function myTruncate($string, $limit, $break=".", $pad="...")
{
    // return with no change if string is shorter than $limit
    if(strlen($string) <= $limit) return $string;

    // is $break present between $limit and the end of the string?
    if(false !== ($breakpoint = strpos($string, $break, $limit))) {
        if($breakpoint < strlen($string) - 1) {
            $string = substr($string, 0, $breakpoint) . $pad;
        }
    }

    return $string;
}
?>
<!doctype html>
<html lang="en">
<?php include("../include/html_head.php"); ?>
    <body>
<?php  include("../include/header.php");  ?>

<div class="container-xxl mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="font-size-18">Task-uri neatribuite</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">App</a></li>
                        <li class="breadcrumb-item active">Taskuri</li>
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
                                   class="btn btn-primary"><span>Task Nou</span></a>
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

                    <div class="row my-4">
                        <?php


                        $limit = 5;
                        $page = 1;
                        if(isset($_GET['page']))
                            $page = $_GET['page'];
                        if($page < 1) $page = 1;
                        $offset = ($page - 1) * $limit;

                        $id_utilizator=$_SESSION['user_id'];
                        $where = "where t.utilizator_asignat IS NULL";

                        if($search) {
                            $search = '%'.mysqli_real_escape_string($db, $search).'%';
                            $where .= " AND t.titlu like '".$search."' or t.descriere like '".$search."'";
                        }

                        $sql = "select t.*, u.nume as nume_utilizator_asignat from taskuri t left join utilizatori u on u.id = t.utilizator_asignat ".$where." limit ".$limit." offset ".$offset;
                        $result = mysqli_query($db, $sql);

                        if(!$result)
                            die("Eroare mysql: ".mysqli_error($db));

                        $intrari_afisate = mysqli_num_rows($result);


                        ?>

                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <?php
                                        $days = intval($row['timp_estimativ']/(24*60*60));
                                        $hours = intval(($row['timp_estimativ']%(24*60*60))/(60*60));
                                        $minutes = intval(($row['timp_estimativ']%(60*60))/60);
                                    ?>
                                <div class="col-12 col-md-4">
                                    <div class="card">
                                        <div class="card-body pb-5" style="position: relative">
                                            <h4 class="card-title mt-3"><a href="vizualizare.php?id=<?= $row['id'] ?>"><?= $row['titlu'] ?></a></h4>
                                            <p class="card-text"><?= myTruncate($row['descriere'], 200, ' ') ?></p>
                                            <span style="position: absolute; top: 5px; right: 5px" class="badge rounded-pill bg-secondary" data-bs-toggle="tooltip" data-bs-placement= "top" title="1 - foarte usor /  5 - foarte greu">Dificultate: <?= $row['dificultate'] ?></span>
                                            <div class="text-muted" style="position: absolute; bottom: 5px; right: 5px">
                                                <small>
                                                    <?= $row['nume_utilizator_asignat'] ? '<p class="m-0">'.$row['nume_utilizator_asignat'].'</p>' : '' ?>
                                                    <p class="m-0"><?= $days.'d '.$hours.'h '.$minutes.'m' ?></p>
                                                </small>
                                            </div>

                                            <div style="position: absolute; left: 16px; bottom: 5px">
                                                <a href="editare.php?id=<?= $row['id'] ?>">Editare</a>
                                                <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN): ?>
                                                <a onclick="stergere(<?= $row['id'] ?>)" href="#">Stergere</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php endwhile; ?>

                    </div>

                    <?php
                    $sql = "select t.id from taskuri t left join utilizatori u on u.id = t.utilizator_asignat ".$where;
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

<?php  include("../include/footer.php"); ?>


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
