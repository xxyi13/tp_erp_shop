<?php

/**
 * 发送邮件
 * @param $toemail 收件人email
 * @param $subject 邮件主题
 * @param $message 正文
 * @param $from 发件人
 */

function sendmail($toemail, $subject, $message, $from='尊敬的客户',$tsHtml=false) {
	require_once("./PHPMailer/class.phpmailer.php"); //下载的文件必须放在该文件所在目录

	$mail = new PHPMailer(); //建立邮件发送类
	$mail->IsSMTP(); // 使用SMTP方式发送
	if( $tsHtml ) {
		$mail->IsHTML();
	}
	$mail->Host = C('MAIL_HOST'); // 您的企业邮局域名
	$mail->SMTPAuth = true; // 启用SMTP验证功能
	$mail->SMTPSecure = "ssl";
	$mail->Username = C('MAIL_USER'); // 邮局用户名(请填写完整的email地址)
	$mail->Password = C('MAIL_PASS'); // 邮局密码
	$mail->Port=C('MAIL_PORT');

	$mail->From = C('MAIL_USER'); //邮件发送者email地址
	$mail->CharSet = "utf-8";
	$mail->Encoding = "base64";
	$mail->FromName = C('MAIL_NAME');
	$mail->AddAddress($toemail, $from);//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
	$mail->Subject = $subject; //邮件标题
	$mail->Body = $message; //邮件内容
	if(!$mail->Send()){
		$map['status'] = 0;
		$map['errorInfo'] = "邮件发送失败."."错误原因: " . $mail->ErrorInfo;
		//return $map;
		return false;
		exit();
	}
	unset($mail);
	return true;
}

?>