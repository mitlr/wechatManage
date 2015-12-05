<?php
require_once './sql.php';

define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
	public function valid()
	{
		$echoStr = $_GET["echostr"];

		//valid signature , option
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}

	public function responseMsg()
	{
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr))
		{
			/* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
			   the best way is to check the validity of xml by yourself */
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content);
			$time = time();
			$event = $postObj->Event;
			$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
			$imageTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[news]]></MsgType>//消息类型为news（图文）
							<ArticleCount>1</ArticleCount>//图文数量为1（单图文）
							<Articles>
							<item>//第一张图文消息
							<Title><![CDATA[%s]]></Title> //标题
							<Description><![CDATA[%s]]></Description>//描述
							<PicUrl><![CDATA[%s]]></PicUrl>//打开前的图片链接地址
							<Url><![CDATA[%s]]></Url>//点击进入后显示的图片链接地址
							</item>
							</Articles>
							</xml> ";

			if (!empty($event))
			{
				$msgType = "text";
				$contentStr = "关注事件";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				echo $resultStr;
			}

			$sql = "SELECT flag_id FROM user_flags WHERE from_user = '$fromUsername'";
			$result = _select_data($sql);
			while ($rows = mysql_fetch_array($result))
			{
				$user_flag = $rows[flag_id];
			}
			if (trim($keyword) != $user_flag && is_numeric($keyword))
			{
				$user_flag = '';
				$sql = "DELETE FROM user_flags WHERE from_user = '$fromUsername'";
				_delete_data($sql);
			}
			if (empty($user_flag))
			{
//				用户绑定对应角色
				if ($keyword == '1' || $keyword == '绑定')
				{
					$sql = "SELECT `uid` FROM `user_bangding` WHERE `from_user` = '$fromUsername'";
					$result = _select_data($sql);
//					查找是否已存在信息
					while ($rows = mysql_fetch_array($result))
					{
						$data = $rows['uid'];
					}

					if (empty($data))
					{
						$msgType = "text";
						$contentStr = '<a href="http://wglpt.sinaapp.com/bd/bangding.php?openid=' . $fromUsername . '">点击绑定角色~</a>';
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}
					else
					{
						$msgType = "text";
						$contentStr = "用户".$data."已存在\n请重新绑定~";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}

				}

				elseif ($keyword == '2' || $keyword == '修改权限')
				{
					$sql = "SELECT * FROM `user_bangding` WHERE `from_user` = '$fromUsername'";
					$res = _select_data($sql);
					while ($rows = mysql_fetch_array($res))
					{
						$data = $rows['type'];
					}
					if ($data == 1)
					{
						$msgType = "text";
						$contentStr = '<a href="http://wglpt.sinaapp.com/bd/bangding.php?openid=' . $fromUsername . '">点击进入修改权限~</a>';
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}
					else
					{
						$msgType = "text";
						$contentStr = "暂无权限！\n请联系管理员";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}
				}

//				用户解除绑定
				elseif ($keyword == '#' || $keyword == '解绑' || $keyword == '解除绑定')
				{
					$sql = "SELECT * FROM `user_bangding` WHERE `from_user` = '$fromUsername'";
					$res = _select_data($sql);
					while ($rows = mysql_fetch_array($res))
					{
						$data = $rows['uid'];
					}

					if(!empty($data))
					{
						$sql1 = "DELETE FROM `user_bangding` WHERE `from_user` = '$fromUsername'";
						$res1 = _delete_data($sql1);

						if($res1 == 1)
						{
							$msgType = "text";
							$contentStr = '解绑工号成功~';
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
							echo $resultStr;
						}
						else
						{
							$msgType = "text";
							$contentStr = '解绑工号失败！';
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
							echo $resultStr;
						}

					}
					else
					{
						$msgType = "text";
						$contentStr = '未绑定工号！';
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}
				}

				else
				{
					echo "Input something...";
				}
			}

		}
		else
		{
			echo "";
			exit;
		}
	}

	private function checkSignature()
	{
		// you must define TOKEN by yourself
		if (!defined("TOKEN"))
		{
			throw new Exception('TOKEN is not defined!');
		}

		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		// use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>