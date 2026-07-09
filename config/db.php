<?php

$conn = mysqli_connect(
    "127.0.0.1",
    "root",
    "",
    "servgo",
    3307
);

if (!$conn) {
    die("Connection Error: " . mysqli_connect_error());
}
