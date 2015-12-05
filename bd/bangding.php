<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<title>
		绑定角色
	</title>
	<!-- 新 Bootstrap 核心 CSS 文件 -->
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="../bootstrap/css/bootstrap-theme.min.css">
</head>
<body>

<?php

$fromUsername=$_GET["openid"];

if(isset($_POST["submit"]))
{
	bangding($fromUsername, trim($_POST["id"]), trim($_POST["pwd"]), trim($_POST["type"]));
	exit();
}

function bangding($fromUsername, $uid, $pwd, $type)
{
	require_once '../sql.php';
	$sql = "SELECT `uid` FROM `user_bangding` WHERE `from_user` = '$fromUsername'";
	$result = _select_data($sql);
//	查找是否已存在信息
	while ($rows = mysql_fetch_array($result))
	{
		$data = $rows['uid'];
	}
	if (empty($data))
	{
		$sql = "INSERT INTO `user_bangding` (`from_user`, `uid`, `pwd`, `type`) values ('$fromUsername', '$uid', '$pwd', '$type')";
		$res = _insert_data($sql);
		if($res == 1)
		{
			$contentStr = "绑定成功 ↖点击此处返回";
			echo $contentStr;
		}
		else
		{
			echo "绑定\".$uid.\"失败<br/>请重新绑定~";
		}
	}
	else
	{
		echo "用户".$uid."已存在<br/>请重新绑定~";
	}
}

echo'
	<div class="container">
		<form action="http://wglpt.sinaapp.com/bd/bangding.php?openid='.$fromUsername.'" method="post">
			<h2 class="form-signin-heading">请绑定工号</h2>
			<div class="form-group">
				<label>工号</label>
				<input name="id" type="text" class="form-control" placeholder="输入工号..." required/>
			</div>
			<div class="form-group">
				<label>密码</label>
				<input name="pwd" type="password" class="form-control" placeholder="输入密码..." required/>
			</div>
			<div class="form-group">
				<label>角色</label>
				<select name="type" class="form-control">
					<option value="1">角色1</option>
					<option value="2">角色2</option>
					<option value="3">角色3</option>
				</select>
			</div>
			<div class="form-group">
				<input type="submit" name="submit" value="确定" class="btn btn-success btn-block"/>
			</div>
		</form>
	</div>
	<footer class="footer">
		<div class="container">
			<label>Copyright ©2015<br/>Powered By 范哲铭 & 毕设</label>
		</div>
	</footer>
';

?>

<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<!-- 包括所有已编译的插件 -->
<script src="../bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
