<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    $compressionLevel = isset($_POST['compression_level']) ? intval($_POST['compression_level']) : 6; // default level

    $zip = new ZipArchive();
    $zipName = 'files_' . time() . '.zip';

    if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $fileName = $_FILES['files']['name'][$key];
            $zip->addFile($tmp_name, $fileName);
        }
        $zip->setCompressionIndex(0, $compressionLevel); // set compression level
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold mb-4">Create ZIP File</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="files">Select Files Want to Compress</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="file" name="files[]" multiple>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="compression_level">Compression Level (0-9)</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="number" name="compression_level" min="0" max="9" value="6">
            </div>
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Create ZIP</button>
        </form>
    </div>
</body>
</html>
