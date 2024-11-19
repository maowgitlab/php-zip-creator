<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    $zip = new ZipArchive();
    $zipName = 'files_' . time() . '.zip';

    if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $fileName = $_FILES['files']['name'][$key];
            $zip->addFile($tmp_name, $fileName);
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipName));
        readfile($zipName);

        unlink($zipName);
    } else {
        echo 'Failed to create ZIP file.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create ZIP File</title>
</head>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="files[]" multiple>
        <button type="submit">Create ZIP</button>
    </form>
</body>
</html>
