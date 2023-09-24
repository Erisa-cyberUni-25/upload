<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>簡易掲示板</title>
    <style>
        /* 全体デザイン */
        * {
            /* リセット */
            margin : 0;
            padding: 0;
        }
        
        ul {
            display: block;/*block要素の横並び指定*/
            margin : 10px;
            padding: 5px;
        }
        
        /* body */
        body {
            padding: 10px;
        }
        /* 文字デザイン */
        h1 {
            color: #aaa;
            font-size: 30px;
            font-family: sans-serif;
        }
        h2 {
            color: #ccc;
            font-size: 20px;
        }
        h3 {
            color: #aaa;
            font-size: 18px;
        }
        
        /* ボタンデザイン */
        div.btn {
            height:50px;
        }
        div.btn a{
            display: inline-block;/*block要素の横並び指定*/
            margin: 10px;
            border-bottom: 5px solid #ddd20a;
            border-radius: 5px;
            padding: 5px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;/*下線をなくす*/
            text-align: center;/*中央ぞろえ*/
        }
        div.btn a:hover{
            margin-top: 13px;
            border-bottom: 0px;
            border-radius: 5px;
            padding: 5px;
            font-size: 16px;
            
        }
        
        /* システムメッセージデザイン */
        div.systemMessage {
            margin: 10px;
            border: 10px ridge #dde;
            padding: 5px;
            background-color: #111;
            font-size: 16px;
            color: #fff;
        }
        
        a.reset {
            color: #000;
            background-color: #fff100;
            border-color: #fff100;
        }

        a.reset:hover {
            color: red;
        }
        
        /* フォームデザイン */
        form{
            margin: 10px;
            border: 10px ridge #dde;
            padding: 10px;
            background-color: #ffe;
        }
        span {
           color: red;
        }
        input, textarea {
            margin: 5px;
            padding: 5px;
        }
        ::placeholder{/*プレースホルダー:入力すると消える*/
            color: #ccc;
        }
        input[type="text"]:focus, textarea:focus{/*入力中*/
            font-weight: 600;
        }
        input[type="submit"]{/*送信ボタン */
            margin: 5px;
            padding: 5px;
        }
            
        /*チェックポイント*/
        div.check{
            margin: 10px;
            padding: 5px;
            background-color: #ccc;
            font-size: 12px;
        }
        
        /*掲示板デザイン*/
        div.log {
            margin: 10px;
            border: 10px ridge #dde;
            padding: 5px;
            background-color: #ffe;
            font-size: 16px;
        }
    </style>
</head>
<body>
<?php

/*自作関数の定義*/

    // DB接続設定   //Mission4-1　データベースへの接続
    function pdo(){
        $dsn = 'mysql:dbname=データベース名;host=localhost';//$dsnの式の中にスペースを入れないこと！
        $user = 'ユーザ名';
        $password = 'パスワード';
        //データベース操作でエラーが発生した場合に警告（Worning: ）として表示するために設定するオプション
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        return $pdo;
    }
	
	//入力・送信チェック : OK => POSTで送信された値, OUT => bool値falseを返す
	function inputCheck($value){
	    
	    if( !empty( $_POST[$value] ) && isset( $_POST[$value] ) ){
            $value = $_POST[$value];//PHPで受信して変数に代入
        }else{
            $value = false;
        }
        
        return $value;
    
	}//inputCheck end

    //テーブルの存在チェック：OK => 既存のテーブル, OUT => bool値falseを返す
    function tableCheck($tbname){
        $value = false;
        $pdo = pdo();
        //SHOW TABLES：データベースのテーブル一覧を呼び出し
        $sql ='SHOW TABLES';
        $result = $pdo->query($sql);
        foreach ($result as $row){
            if( $row[0] == $tbname ){
            $value = $row;
            }else{
                //何もしない
            }
        }
        return $value;
    }

    //テーブルを作成
    function tableCreate($tbname){
        $pdo = pdo();
        //CREATE文：データベース内にテーブルを作成
        $sql = "CREATE TABLE IF NOT EXISTS $tbname"//もしまだテーブルが存在しないなら作成する
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"//自動的にインデックスを割り振る
        . "name char(32),"
        . "comment TEXT,"
        . "date TEXT,"
        . "password TEXT"
        .");";
        $stmt = $pdo->query($sql);//statement
    }

    //テーブルの詳細を確認
    function tableShow($tbname){
        $pdo = pdo();
        //SHOW CREATE TABLE文：作成したテーブルの構成詳細を確認する
        $sql = "SHOW CREATE TABLE $tbname";
        $result = $pdo -> query($sql);
        foreach ($result as $row){
            echo $row[1];
            echo "<br>";
        }
    }
    
    //SHOW TABLES：データベースのテーブル一覧を表示
    function tableShowList(){
        $pdo = pdo();
        $sql ='SHOW TABLES';
        $result = $pdo->query($sql);
        echo "<ul>";
        foreach ($result as $row){
            echo "<li>";
            echo $row[0];
            echo '</li>';
        }
        echo "</ul>";
    }
	
	//テーブルの存在・中身チェック : メッセージを返す
	function tableCheck2($tbname){
        $pdo = pdo();
        //SELECT文：入力したデータレコードを抽出
        $sql = "SELECT * FROM $tbname";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
	    
	    //existsチェック
	    if(tableCheck($tbname)){//ファイルが存在するとき
        
            //!emptyチェック
            if( !empty ( $results ) ){//ファイルの中身が空ではないとき
                $value = "OK";
            }else{
                $value = "empty";
            }
	    }else{
	        $value = "!exists";
	    }
	    
	    return $value;//エラーの種類を戻り値に設定
	}
	
	//テーブルの削除
	function tableDrop($tbname){
	    $pdo = pdo();
	    $sql = "DROP TABLE $tbname";
        $stmt = $pdo->query($sql);
	}
	
	//投稿の検索
	function search($tbname, $where){
        //existsチェック
	    if(tableCheck($tbname)){//データベースが存在するとき
            $pdo = pdo();
            //WHERE句
            $id = $where ; // idがこの値のデータだけを抽出したい、とする
            $sql = "SELECT * FROM $tbname WHERE id=:id ";
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll(); 
            //!emptyチェック
            if( !empty ( $results ) ){//投稿の中身が空ではないとき
                $value = $results;
            }else{
                $value = false;
            }
	    }else{
	        $value = false;
	    }
            
        return $value;
	}// search end
	
	
	//秘密のメッセージ//入力データが特定の文字列のときのみ特別なメッセージが表示されるというギミック(if文)
	function message($name){
	    $members = array("ひなた", "みつき", "りょうま", "ももか", "ゆうと");
            foreach( $members as $member){
                if($name == $member){
                    echo "<br>【秘密のメッセージ】";
                    echo $member." さん、来てくれてありがとう！ ";
                    echo "<br>";
                }
        }
	}//message end
	
	//新規投稿
	function newPost($tbname, $name, $comment, $password){
	    
        echo "【".$tbname."】に保存します";
        echo "<br>";

	    $date = date("Y/m/d H:i:s");//日時の取得
        
        $pdo = pdo();
        //INSERT文：データを入力（データレコードの挿入）
        $sql = "INSERT INTO $tbname (name, comment, date, password) VALUES (:name, :comment, :date, :password)";
        $stmt = $pdo->prepare($sql);
            //bindParamの引数名（:name など）はテーブルのカラム名に併せる
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
        echo "投稿が保存されました";
        echo "<br>";
	}
	
	//削除
	function delPost($tbname, $delNum, $password){
	    
	    echo "【".$tbname."】から投稿を削除します";
	    echo "<br>";

        $pdo = pdo();
        // DELETE文：入力したデータレコードを削除
        $id = $delNum;
        $sql = "DELETE from $tbname WHERE id=:id AND password=:password";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':password', $password, PDO::PARAM_INT);
        $stmt->execute();
        
        echo "投稿が削除されました";
	    
	}

    //投稿編集準備
	function editReady($tbname, $editNum, $password){
        $editData = search($tbname, $editNum);
        if($editData){
            echo $editData[0]["password"];
            echo $password;
            if($editData[0]["password"] == $password){
                echo "【".$tbname."】から編集する投稿を取得しました";//該当の投稿が見つかりました
            }else{
                echo "編集できません";
                $editData = false;
            }
        }else{
            echo "該当する投稿は見つかりませんでした";
        }
        return $editData[0];
    }
	
	//投稿編集
	function editPost($tbname, $editNum, $name, $comment, $password){
	    
	    echo "【".$tbname."】を編集します";
        echo "<br>";

	    $date = date("Y/m/d H:i:s");//日時の取得

        $pdo = pdo();
        //UPDATE文：入力されているデータレコードの内容を編集
        $id = $editNum; //変更する投稿番号
        $sql = "UPDATE $tbname SET name=:name,comment=:comment,date=:date,password=:password WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo "投稿が編集されました";
        
	}
	
	//投稿一覧を表示
	function postView($tbname){
        $pdo = pdo();
        //SELECT文：入力したデータレコードを抽出し、表示する
        $sql = "SELECT * FROM $tbname";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        $count = count($results);
        foreach ($results as $row){
            //$row テーブルのカラム名
            echo $row['id'].' ';
            echo $row['name'].' ';
            echo $row['comment'].' ';
            echo $row['date'].' ';
            echo "<br>";
            echo "<hr>";
        }
        return $count;
	}
	
	/*
	//POST誤送信を防ぐ input最後尾に設定
	function disabled($inputType){
	    if($inputType){
	        //なにもしない
	    }else{
	        echo "disabled";
	    }
	}*/
	
?>
<h1>掲示板var1 byえりさ</h1>
<div class="systemMessage">
<!-- システムメッセージ  -->
<h2>システムメッセージ</h2>
<span>再送防止のため、リセットボタンを押してブラウザを更新してください</span>
<div class="btn"><a href="" class="reset">リセット</a></div>
<br>
<?php
    echo "**********"."<br>";
    echo "テーブル一覧"."<br>";
    tableShowList();
    $tbname = "mission_5_tb";//テーブル指定
    echo "テーブル【".$tbname."】が選択されています。";
    echo "<br>"."**********"."<br>";
    $inputType = "";//入力の目的を判断
    $editNum = "";
    $delNum = "";

    if(tableCheck($tbname)){
        //tableShow($tbname);//確認用
    }else{
        tableCreate($tbname);
    }
    
    if($_SERVER['REQUEST_METHOD']==='POST'){//このフォ―ムから送信があった場合
        $name = inputCheck("name");
        $comment = inputCheck("comment");
        $postNum = inputCheck("postNum");
        $delNum = inputCheck("delNum");
        $editNum = inputCheck("editNum");
        $password = inputCheck("password");
        
        //「フォーム」で処理を分岐させる(if)
        $inputType = "";
        if( $password ){//パスワードが入力されているとき
            //投稿が入力されているとき
            if( $name && $comment && !($postNum) ){//投稿が入力されたとき
                $inputType = "newPost";//新規投稿
            }else if( $name && $comment && $postNum ){
                $inputType = "editPost";//投稿編集
            }//削除番号が指定されているとき
            else if( $delNum ){
                $inputType = "del";//投稿削除
            }//編集番号が指定されているとき
            else if( $editNum ){
                $inputType = "edit";//投稿編集
            }else{//入力に不備があるとき、もしくは何も入力されていないとき
                echo "入力内容に不備があります。";
            }//入力の目的を判断 end
        }else{
            echo "パスワードが入力されていません。";
        }//if( $password ) end
        
    }//if($_SERVER['REQUEST_METHOD']==='POST') end
    
        //投稿フォーム
    	if($inputType == "newPost"){
            
            newPost($tbname, $name, $comment, $password);
                
        }else{
                //何もしない
        }//if($inputType == "newPost") end
        
        //投稿編集フォーム
        if($inputType == "editPost"){
    	
    	    $editNum = $postNum;
            
            editPost($tbname, $editNum, $name, $comment, $password);
                
        }else{
                //何もしない
        }//if($inputType == "newPost") end
        
        //削除フォーム
    	if($inputType == "del"){
    	    
            delPost($tbname, $delNum, $password);
            
        }else{
                //何もしない
        }//if($inputType == "del") end
    
        //編集指定フォーム
        $editData = "";
    	if($inputType == "edit"){
            
            $editData = editReady($tbname, $editNum, $password);
            //var_dump($editData);//確認用
            if ($editData) {
                $editName = $editData['name'];
                //var_dump($editName);//確認用
                $editComment = $editData["comment"];
                $editPass = $editData["password"];
            }else {
                //何もしない
            }
                
        }else{
                //何もしない
        }//if($inputType == "edit") end
?>
</div>
<!-- システムメッセージ　END -->

<hr>
    <h2>Mission4-5</h2>
    <p>INSERT文：データを入力（登録）</p>
    <div class="check">
    <h6>チェックポイント</h6>
    <p>・入力フォームで入力した文字がデータベースに保存されること
    <br>・入力フォームが空の時に送信ボタンを押したときはなにも実行されないこと</p>
    </div>
    
    <form action="" method="post"><!--入力フォームから「POST送信」-->
<?php
    
    if( $editNum && $editData){//投稿が既に入力されているとき//編集内容が入力されているとき
        echo "<h3>編集モード</h3>";
        $name = $editName;
        $comment = $editComment;
        $password = $editPass;
    }else{
        echo "<h3>新規投稿モード</h3>";
        $name = "";
        $comment = "";
        $password = "";
    }
?>
        <input type="text" name="name" placeholder="名前" value='<?php echo $name; ?>' >
        <input type="text" name="comment" placeholder="コメント" value='<?php echo $comment; ?>'>
        <!-- <textarea name="message" rows="5" cols="100" placeholder="メッセージ"></textarea><br> -->
        <input type="hidden" name="postNum" value='<?php  echo $editNum?>' >
        <input type="password" name="password" placeholder="パスワード" value='<?php echo $password; ?>'>
        <input type="submit" name="submit" value="送信">
    </form>
<hr>
    <h2>Mission4-8</h2>
    <p>DELETE文：データを削除（削除）</p>

    <div class="check">
    <h6>チェックポイント</h6>
    <p>・削除番号指定フォーム（削除対象番号入力欄と削除ボタン）が追加されていること
    <br>・指定した削除番号の行がデータベースから削除されること
    <br>・指定した削除番号の行がブラウザ表示からも消えていること</p>
    </div>
    
    <form method='POST' action="">
    <h3>投稿削除</h3>
    <input type='number' name='delNum' placeholder="削除対象番号">
    <input type="password" name="password" placeholder="パスワード">
    <input type='submit' name='submit' value='削除'>
    </form>
    
<hr>
    <h2>Mission4-7</h2>
    <p>UPDATE文：データを変更（編集）</p>
    <h3>ヒント</h3>
    <p>編集の手順
    <br>・編集元のテキストを、投稿フォームに表示させる
    <br>・編集してから送信する
    <br>・上記の送信をされた時点で、これは編集すべきものと判断できる「目印」が要る
    <br>・その目印が送られてきた場合は、編集として処理をする（それ以外は新規投稿として処理する）
</p>

    <div class="check">
    <h6>チェックポイント</h6>
    <p>・編集番号指定フォーム（編集対象番号入力欄と編集ボタン）が追加されていること
    <br>・指定した編集番号の行がデータベース、ブラウザ表示、共に最新に更新されること</p>
    </div>
    
    <form method='POST' action="">
    <h3>投稿編集</h3>
    <input type='number' name='editNum' placeholder="編集対象番号">
    <input type="password" name="password" placeholder="パスワード">
    <input type='submit' name='submit' value='編集'>
    </form>
<hr>
    <h2>Mission4-6</h2>
    <p>SELECT文：データを抽出（絞り込み/表示）</p>
    <div class="check">
    <h6>チェックポイント</h6>
    <p>・データベースに保存している内容が入力フォームの下に表示されること</p>
</div>
<div class="log">
<?php
//3-2　テキストファイルの内容を（デリミタで分割した上で）フォームの下に表示させる
        echo "<h2> 書き込み記録 </h2>";
        if(tableCheck2($tbname)=="OK"){//ファイルを確認
            $count = postView($tbname);
            echo "<h2> "."投稿数　計：".$count."</h2>";
        }else if(tableCheck2($tbname)=="empty"){
            echo "まだ何も投稿されていません";
        }else if(tableCheck2($tbname)=="!exists"){//ファイルが存在しないとき
            echo "<br>書き込み記録が存在しません。";
        }
?>
</div>
<hr>
<h2>Mission3-5</h2>
    <p>新たな機能として、「パスワード機能」をつけてみよう。投稿した本人だけが「編集」「削除」を行えるようにする。</p>
    <div class="check">
    <h6>チェックポイント</h6>
    <p>・新規投稿フォーム、削除フォーム、編集フォームに「パスワード」の入力欄があること
    <br>・新規投稿でテキストファイルにパスワードも保存されていること
    <br>・指定した投稿番号のパスワードが一致した時のみ編集、削除ができること</p>
</body>
</html>