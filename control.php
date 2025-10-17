<?php
date_default_timezone_set("Asia/Manila"); // Set timezone to Manila

$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");
if(isset($_GET['led'])){
		$sqlLed = "SELECT led FROM control limit 1";
		$queryLed = mysqli_query($conn,$sqlLed);
		$rwLed = mysqli_fetch_assoc($queryLed);
		echo $rwLed['led'];
		if($_GET['led'] == 1){

		$update = "UPDATE `control` SET `led`='1' where id = 1";
		}if($_GET['led'] == 0){

		$update = "UPDATE `control` SET `led`='0' where id = 1";
		}
	if(($_GET['led'] == 0 || $_GET['led'] == 1 )){
		mysqli_query($conn,$update) ;
		echo '<script> window.location="home.php"; </script>';
			}
}
if(isset($_GET['motor'])){
		$sqlLed = "SELECT motor FROM control limit 1";
		$queryLed = mysqli_query($conn,$sqlLed);
		$rwLed = mysqli_fetch_assoc($queryLed);
		echo $rwLed['motor'];		
		if($_GET['motor'] == 1){

		$update1 = "UPDATE `control` SET `motor`='1' where id = 1";
		}if($_GET['motor'] == 0){

		$update1 = "UPDATE `control` SET `motor`='0' where id = 1";
		}

		if( ($_GET['motor'] == 0 || $_GET['motor'] == 1 )){
			mysqli_query($conn,$update1) ;
		echo '<script> window.location="home.php"; </script>';
			}

}
?>