<?php
/**
 * Created by SyncroBit.
 * Author: George Partene
 * Version: 0.1
 */

header('Content-Type: application/json');

include "includes/initd.inc.php";

$snapshot = SB_KERNEL::generateSnapShot();
echo json_encode($snapshot);