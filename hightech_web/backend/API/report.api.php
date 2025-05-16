<?php 
header("Content-type: application/json; charset=utf-8");
if(isset($_POST['path']) || isset($_GET['path'])) {
    include('../classes/Database.class.php');
    include('../classes/Program.class.php');
    $Program = new Program();
}else {
    echo json_encode([
        'status' => 'error',
        'msg' => 'notfound path'
    ]);
}

if($_POST['path'] == 'monthReport' && isset($_POST['month']) && isset($_POST['year'])) {
    $month = $Program->XssProtection($_POST['month']);
    $year = $Program->XssProtection($_POST['year']);
}
?>