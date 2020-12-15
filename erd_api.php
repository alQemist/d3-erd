<?php
error_reporting (E_ALL);
set_time_limit ( 0 ) ;

require_once ("connectDB.php");
$conn = new createCon();
$conn->connect();
$dbconn = $conn->myconn;
$db = $conn->db;

foreach($_POST AS $key => $value) {
    ${$key} = $value;
};
foreach($_GET AS $key => $value) {
    ${$key} = $value;
};


if(isset($v)){
    $sql = file_get_contents("sql/erd_query_".$v.".sql",true);
    $result = mysqli_query($dbconn, $sql);

    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $myArray[] = $row;
    }
    print_r(json_encode($myArray));
}
elseif(isset($data)){
    $row = explode("-",$data);

    //print_r($row);

    $sql = "UPDATE `data_entity` SET `x`='" . $row[1] . "', `y` ='".$row[2]."' WHERE `id` = '". $row[0]."'"  ;

$results = mysqli_query($dbconn, $sql);
$print_r ($results->num_rows);
}








?>

