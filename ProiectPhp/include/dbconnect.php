<?php
session_start();
$db = mysqli_connect("localhost", "root", "","acab");

const TIP_UTILIZATOR_ANGAJAT = 0;
const TIP_UTILIZATOR_ADMIN = 1;