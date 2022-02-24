<?php
session_start();
require('functions.php');
    
if(isset($_SESSION['flg']) && $_SESSION['flg'] == "ok"){
    $dbh = db_conn();
    
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION["key"] == $_POST["key"]){
      if(isset($_POST["id"])){
         if(! empty($_POST["id"] )) {
            $id = $_POST["id"];
         } else {
             $error['error'] = "blank";
         }
      } else {
          $error['error'] = "uncheck";
      }
      
    // locked リセット
	$sql = 'UPDATE members SET locked = :locked WHERE id=:id';
    $reset = $dbh->prepare($sql);
    $reset->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
    $reset->bindValue(':locked', 0, PDO::PARAM_INT);
    $reset->execute();
  } 
    
    $data = [];
    try{
        $sql = "SELECT * FROM members";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $count = 0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
            $count++;
        }
    }catch (PDOException $e){
        echo($e->getMessage());
        die();
    }
    $_SESSION["key"] = md5(uniqid().mt_rand());
    
    }else{
        header('Location: opedisp.php');
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>アカウント一覧画面</title>
  
  <link rel="stylesheet" href="style.css" />
</head>
<body>
    
<div id="wrap">
	<div id="head">
        <h1>Simple掲示板</h1>
    </div>
<hr>
<div id="content">
  	<div id="lead">
  	    <?php if ($error['error'] == 'blank'): ?>
			<p class="error">* IDが空です</p>
		<?php endif; ?>
		<?php if ($error['error'] == 'uncheck'): ?>
			<p class="error">* 解除するデータを選択してください</p>
		<?php endif; ?>
        <p>データ件数：<?php echo $count;?>件</p>
    </div>
        <form action="" method="post" class="row">
        <input type="hidden" name="key" value="<?php echo htmlspecialchars( $_SESSION["key"], ENT_QUOTES );?>">
        <table border=1>
            <tr><th>id</th><th>名前</th><th>メールアドレス</th><th>連続ログイン失敗回数</th><th>選択対象</th></tr>
            <?php foreach($data as $row): ?>
            <tr>
            <td><?php echo $row['id'];?></td>
            <td><?php echo $row['user'];?></td>
            <td><?php echo $row['email'];?></td>
            <td align="right"><?php echo $row['locked'];?></td>
            <td>
                <label><input type="radio" name="id" id="edit" value="<?php echo $row['id'];?>">選択</label>
            </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p style="margin:8px;">
        <p>アカウントロックを解除するデータを選択してください</p>
        <div class="button-wrapper">
            <button type="button" onclick="location.href='opedisp.php'">戻る</button>
	        <button type="submit" class="btn btn--naby btn--shadow">解除する</button>
        </div>
</form>
    </div>
</div>
</body>
</html>