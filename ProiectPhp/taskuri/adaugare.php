<?php
include("../include/dbconnect.php");

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
<div class="container-xxl mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="font-size-18">Adaugare Task</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">App</a></li>
                        <li class="breadcrumb-item active"><a href="/taskuri">Taskuri</a></li>
                        <li class="breadcrumb-item active">Adaugare</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-body">
                    <form id="formularAdaugareTask">
                        <input type="hidden" name="actiune" value="adaugare">
                        <fieldset>
                            <div class="form-group">
                                <label for="titlu">Titlu</label>
                                <input id="titlu" required minlength="3" maxlength="100" class="form-control" type="text" name="titlu">
                            </div>

                            <div class="form-group">
                                <label for="descriere">Descriere</label>
                                <textarea rows="10" class="form-control" required minlength="20" maxlength="5000" name="descriere" id="descriere"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="dificultate">Dificultate</label>
                                <select name="dificultate" required id="dificultate" class="form-control">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="timp_estimativ">Timp Estimativ</label>
                                <input type="text" required class="form-control" name="timp_estimativ" id="timp_estimativ">

                            </div>

                            <div class="form-group">
                                <?php

                                //include("../includes/dbconnect.php");

                                $result = mysqli_query($db, "select id, nume from utilizatori");

                                if(!$result)
                                    die("Eroare mysql: ".mysqli_error($db));
                                ?>

                                <label for="utilizator_asignat">Utilizator Asignat</label>

                                <select id="utilizator_asignat" name="utilizator_asignat" class="form-control">
                                    <option value="0">Neasignat</option>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <?php if($_SESSION['user_type'] == TIP_UTILIZATOR_ADMIN || $_SESSION['user_id'] == $row['id']): ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['nume'] ?></option>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </select>

                            </div>



                        </fieldset>
                        <div class="mt-2 text-end">
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>

<script>
    $("#formularAdaugareTask").submit(function(event) {
        event.preventDefault();

        $.post("actions.php", $("#formularAdaugareTask").serialize(), function(response) {
            var result = JSON.parse(response);
            if(!result.success) {
                Swal.fire({
                    icon: 'error',
                    text: result.error,
                })
            }
            else {
                location.href = "/taskuri";
            }
        })
    });
</script>

<script>
    (function(g){var h={en:{day:"day",hour:"hour",minute:"minute",second:"second",days:"days",hours:"hours",minutes:"minutes",seconds:"seconds"},es:{day:"d&iacute;a",hour:"hora",minute:"minuto",second:"segundo",days:"d&iacute;s",hours:"horas",minutes:"minutos",seconds:"segundos"}};g.fn.durationPicker=function(m){var a=0,b=g.extend({},{lang:"en",max:59,checkRanges:!1,totalMax:31556952E3,totalMin:6E4,showSeconds:!1,showDays:!0},m);this.each(function(m,q){function e(a,b){return'<div class="bdp-block '+(b?
        "hidden":"")+'"><span id="bdp-'+a+'"></span><br><span class="bdp-label" id="'+a+'_label"></span></div>'}function n(){$mainInput.val()||$mainInput.val(0);moment.locale(b.lang);a=moment.duration(parseInt($mainInput.val(),10));p();l||(d.days.val(a.days()),d.hours.val(a.hours()),d.minutes.val(a.minutes()),d.seconds.val(a.seconds()))}function r(){a=moment.duration({seconds:parseInt(d.seconds.val()),minutes:parseInt(d.minutes.val()),hours:parseInt(d.hours.val()),days:parseInt(d.days.val())});p();$mainInput.val(a.asMilliseconds());
        $mainInput.change()}function k(a,c,f){var e=g('<input class="form-control input-sm" type="number" min="0" value="0">').change(r);f&&e.attr("max",f);d[a]=e;a=g("<div> "+h[b.lang][a]+"</div>");c&&a.addClass("hidden");return a.prepend(e)}function p(){b.checkRanges&&(a=a.asMilliseconds()>b.totalMax?moment.duration(b.totalMax):a,a=a.asMilliseconds()<b.totalMin?moment.duration(b.totalMin):a);c.find("#bdp-days").text(a.days());c.find("#bdp-hours").text(a.hours());c.find("#bdp-minutes").text(a.minutes());
        c.find("#bdp-seconds").text(a.seconds());c.find("#days_label").text(h[b.lang][1==a.days()?"day":"days"]);c.find("#hours_label").text(h[b.lang][1==a.hours()?"hour":"hours"]);c.find("#minutes_label").text(h[b.lang][1==a.minutes()?"minute":"minutes"]);c.find("#seconds_label").text(h[b.lang][1==a.seconds()?"second":"seconds"])}$mainInput=g(q);if("1"!==$mainInput.data("bdp")){var c=g('<div class="bdp-input"></div>');c.append(e("days",!b.showDays));c.append(e("hours"));c.append(e("minutes"));c.append(e("seconds",
        !b.showSeconds));$mainInput.after(c).hide().data("bdp","1");var d=[],l=!1;if($mainInput.hasClass("disabled")||"disabled"==$mainInput.attr("disabled"))l=!0,c.addClass("disabled");if(!l){var f=g('<div class="bdp-popover"></div>');k("days",!b.showDays).appendTo(f);k("hours",!1,23).appendTo(f);k("minutes",!1,59).appendTo(f);k("seconds",!b.showSeconds,59).appendTo(f);c.popover({placement:"bottom",trigger:"click",html:!0,content:f})}n();$mainInput.change(n)}})}})(jQuery);

    $(function() {
        $('#timp_estimativ').durationPicker();

    });
</script>

</body>
</html>