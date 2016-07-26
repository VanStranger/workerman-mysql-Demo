<?php 
	$conn=mysqli_connect("localhost","root","root","test");
	if($conn){
		if(isset($_POST['username']) && isset($_POST['password'])){
			$name=$_POST['username'];
			$pass=$_POST['password'];
			$sql="SELECT UserID,UserName from users where UserName='$name' and Password='$pass'";
			$get=mysqli_query($conn,$sql);
			$row=mysqli_affected_rows($conn);
			if($row==1){
				$result=mysqli_fetch_assoc($get);
				// setcookie("userid",$result['UserID']);
				// setcookie("username",$result['UserName']);
				// echo $result['UserName'];
				// header("Location:index.php");
				echo "<script>window.location.href='./index.php?userid=".$result['UserID']."&username=".$result['UserName']."';</script>";
			}
		}  
	}else{
		echo "连接失败".mysqli_error();
	}
?>