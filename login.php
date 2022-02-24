<?php
session_start();
require('functions.php');

if ($_COOKIE['email'] != '') {
$_POST['email'] = $_COOKIE['email'];
$_POST['password'] = $_COOKIE['password'];
$_POST['save'] = 'on';
}

if (!empty($_POST)  && $_SESSION["key"] == $_POST["key"]) {
    unset( $_SESSION["key"] );
    
	// ログインの処理
	if ($_POST['email'] != '' && $_POST['password'] != '') {
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');
        $dbh = db_conn();
        try{
            $sql = 'SELECT * FROM members WHERE email=:email';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
			$member = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if( password_verify($password, $member['password']) && $member['locked'] < 3) {
				// ログイン成功
				session_regenerate_id(true); // 現在のセッションIDを新しく生成したものと置き換える
				                             // セッションハイジャック対策
				$_SESSION['id'] = $member['id'];
				$_SESSION['time'] = time();

				// ログイン情報を記録する
				if ($_POST['save'] == 'on') {
				    setcookie('email', $_POST['email'], time()+60*60*24*14);
				    setcookie('password', $_POST['password'], time()+60*60*24*14);
				}
				
				// locked リセット
				$sql = 'UPDATE members SET locked = :locked WHERE email=:email';
			    $reset = $dbh->prepare($sql);
                $reset->bindValue(':email', $email, PDO::PARAM_STR);
                $reset->bindValue(':locked', 0, PDO::PARAM_STR);
                $reset->execute();
				
				header('Location: index2.php');
				exit();
            }else{
				// ログイン認証失敗
				if(isset($member['locked']) && $member['locked'] >= 3) {
				    // アカウントロック
				    $error['login'] = 'locked';
				}else{
				    // locked カウントアップ
				    $sql = 'UPDATE members SET locked = locked +1 WHERE email=:email';
				    $countup = $dbh->prepare($sql);
                    $countup->bindValue(':email', $email, PDO::PARAM_STR);
                    $countup->execute();
                    $error['login'] = 'failed';
				}
            } 
        }catch (PDOException $e){
            echo($e->getMessage());
            die();
        }
	} else {
		$error['login'] = 'blank';
	}
}
$_SESSION["key"] = md5(uniqid().mt_rand());
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Simple掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>ログイン画面</h1>
		</div>
		<div id="content">
			<div id="lead">
				<p>メールアドレスとパスワードを入力してログインしてください。</p>
				<p>会員登録がまだの方はこちらからどうぞ。</p>
				<p>&raquo;<a href="input.php">会員登録手続きをする</a></p>
			</div>
			<form action="" method="POST">
			    <input type="hidden" name="key" value="<?php echo htmlspecialchars( $_SESSION["key"], ENT_QUOTES );?>">
				<dl>
					<dt>メールアドレス</dt>
					<dd>
						<input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES); ?>"/>
						<?php if ($error['login'] == 'blank'): ?>
							<p class="error">* メールアドレスとパスワードをご記入ください</p>
						<?php endif; ?>
						<?php if ($error['login'] == 'failed'): ?>
							<p class="error">* ユーザーIDあるいはパスワードに誤りがあります。正しく入力ください。</p>
						<?php endif; ?>
						<?php if ($error['login'] == 'locked'): ?>
							<p class="error">* このユーザーはロックされています。お手数ですが管理者に問い合わせてください。</p>
						<?php endif; ?>
					</dd>
					<dt>パスワード</dt>
					<dd>
						<input type="password" name="password" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES); ?>" />
					</dd>
					<dt>ログイン情報の記録</dt>
					<dd>
						<input id="save" type="checkbox" name="save" value="on"><label
						for="save">次回からは自動的にログインする</label>
					</dd>
				</dl>
				<div><input type="submit" value="ログインする" /></div>
			</form>
		</div>

	</div>
</body>
</html>