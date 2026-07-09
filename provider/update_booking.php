<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['id']) || $_SESSION['role'] != 'provider') {
    header("Location: ../login.php"); exit();
}

if(isset($_GET['id']) && isset($_GET['status'])) {

    $id          = $_GET['id'];
    $status      = $_GET['status'];
    $provider_id = $_SESSION['id'];

    /* Only allowed statuses */
    $allowed = ['Accepted', 'Completed', 'Cancelled'];
    if(!in_array($status, $allowed)) {
        header("Location: bookings.php"); exit();
    }

    /* Make sure this booking belongs to this provider */
    $check = mysqli_query($conn,
        "SELECT bookings.id FROM bookings
         JOIN services ON bookings.service_id = services.id
         WHERE bookings.id = '$id'
         AND services.provider_id = '$provider_id'"
    );

    if(mysqli_num_rows($check) > 0) {
        mysqli_query($conn,
            "UPDATE bookings SET status='$status' WHERE id='$id'"
        );
        header("Location: bookings.php?msg=" . urlencode("Booking updated to $status!"));
        exit();
    } else {
        header("Location: bookings.php?msg=error");
        exit();
    }
}

header("Location: bookings.php");
exit();
