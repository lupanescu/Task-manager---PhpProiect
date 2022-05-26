<?php


$allowed_pages = ['/login/index.php', '/login/signup.php'];

if (!in_array($_SERVER['SCRIPT_NAME'], $allowed_pages) && (!isset($_SESSION['user_id']) || !$_SESSION['user_id'])) {
    header("Location: /login");
    die;
}


if(!in_array($_SERVER['SCRIPT_NAME'], $allowed_pages))  {
    $result = mysqli_query($db, "select id from permisiuni where tip_utilizator='".$_SESSION['user_type']."' and pagina='".$_SERVER['SCRIPT_NAME']."'");
    if(!$result) {
        die("Eroare Mysql: ".mysqli_error($db));
    }
    if (!$row=$result->fetch_assoc()) {
        $_SESSION['flash_error'] = 'Nu ai acces la aceasta pagina!';
        if($_SERVER['HTTP_REFERER']) {
            header("Location: ".$_SERVER['HTTP_REFERER']);
        }
        else {
            header("Location: /");
        }
        die();
    }
}
?>


<nav class="navbar navbar-expand-lg  navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/taskuri">ACaB</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">


            <ul class="navbar-nav me-auto mb-2 mb-lg-0">


                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php
                    $sql = "select n1.id, n1.titlu, n1.adresa, count(n2.id) as numar_submeniu from navigare n1 LEFT JOIN navigare n2 ON n2.parinte = n1.id where n1.tip_utilizator=" . $_SESSION['user_type'] . " AND n1.parinte IS NULL GROUP BY n1.id";
                    //die($sql);
                    $result = mysqli_query($db, $sql);
                    if (!$result) {
                        die("Eroare Mysql: " . mysqli_error($db));
                    }
                    ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $adresa = $row['adresa'];
                        //die($adresa);
                        $adresa = str_replace('[user_name]', $_SESSION['user_name'], $adresa);
                        $adresa = str_replace('[user_id]', $_SESSION['user_id'], $adresa);
                        //die($adresa);
                        ?>

                        <?php if ($row['numar_submeniu']): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= $row['titlu'] ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <?php
                                    $sql = "select n1.titlu, n1.adresa  from navigare n1 where n1.tip_utilizator=" . $_SESSION['user_type'] . " AND n1.parinte=" . $row['id'];
                                    //die($sql);
                                    $result2 = mysqli_query($db, $sql);
                                    if (!$result2) {
                                        die("Eroare Mysql: " . mysqli_error($db));
                                    }
                                    while ($row2 = $result2->fetch_assoc()) {
                                        $adresa2 = $row2['adresa'];
                                        $adresa2 = str_replace('[user_name]', $_SESSION['user_name'], $adresa2);
                                        $adresa2 = str_replace('[user_id]', $_SESSION['user_id'], $adresa2);
                                        echo '<a class="dropdown-item" href="' . $adresa2 . '">' . $row2['titlu'] . '</a>';
                                    }
                                    ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $adresa ?>"><?= $row['titlu'] ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endwhile; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/login/signup.php">Inregistrare</a>
                    </li>
                <?php endif; ?>
            </ul>

        </div>
</nav>

<?php
if(isset($_SESSION['flash_error'])) {
    echo '<div class="container-xxl mt-4"><div class="alert alert-danger" role="alert">'.$_SESSION['flash_error'].'</div></div>';
    unset($_SESSION['flash_error']);
}
?>