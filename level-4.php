<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    $compressionLevels = [
        'No Compression' => 0,
        'Minimal Compression' => 1,
        'Low Compression' => 2,
        'Moderate Compression' => 3,
        'Balanced Compression' => 4,
        'Standard Compression' => 5,
        'Enhanced Compression' => 6,
        'High Compression' => 7,
        'Ultra Compression' => 8,
        'Maximum Compression' => 9
    ];
    $compressionLevelName = isset($_POST['compression_level']) ? $_POST['compression_level'] : 'Standard Compression';
    $compressionLevel = $compressionLevels[$compressionLevelName];

    $zip = new ZipArchive();
    $zipName = 'files_' . time() . '.zip';

    if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $fileName = $_FILES['files']['name'][$key];
            $zip->addFile($tmp_name, $fileName);
            $zip->setCompressionName($fileName, $compressionLevel);
        }
        $zip->close();

        echo json_encode(['status' => 'success', 'zipName' => $zipName]);
    } else {
        echo json_encode(['status' => 'fail']);
    }
    exit;
}

if (isset($_GET['download'])) {
    $zipName = basename($_GET['download']);
    $filePath = $zipName;

    if (file_exists($filePath)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        unlink($filePath); // delete the file after download
        exit;
    } else {
        echo 'File not found.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create ZIP File</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        #loader {
            display: none;
            margin: 0 auto;
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #3498db;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold mb-4">Create ZIP File</h1>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="files">Select Files</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="file" name="files[]" multiple required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="compression_level">Compression Level</label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="compression_level">
                    <option value="No Compression">No Compression</option>
                    <option value="Minimal Compression">Minimal Compression</option>
                    <option value="Low Compression">Low Compression</option>
                    <option value="Moderate Compression">Moderate Compression</option>
                    <option value="Balanced Compression">Balanced Compression</option>
                    <option value="Standard Compression" selected>Standard Compression</option>
                    <option value="Enhanced Compression">Enhanced Compression</option>
                    <option value="High Compression">High Compression</option>
                    <option value="Ultra Compression">Ultra Compression</option>
                    <option value="Maximum Compression">Maximum Compression</option>
                </select>
                <p class="text-xs text-gray-600 mt-2">
                    <strong>No Compression:</strong> Tanpa kompresi, file asli tanpa perubahan. <br>
                    <strong>Minimal Compression:</strong> Kompresi sangat ringan, ukuran file hampir sama. <br>
                    <strong>Low Compression:</strong> Kompresi rendah, sedikit mengurangi ukuran file. <br>
                    <strong>Moderate Compression:</strong> Kompresi sedang, ukuran file cukup berkurang. <br>
                    <strong>Balanced Compression:</strong> Kompresi seimbang, mengurangi ukuran file. <br>
                    <strong>Standard Compression:</strong> Kompresi standar, ukuran file terkompresi normal. <br>
                    <strong>Enhanced Compression:</strong> Kompresi lebih tinggi dari standar. <br>
                    <strong>High Compression:</strong> Kompresi tinggi, ukuran file berkurang signifikan. <br>
                    <strong>Ultra Compression:</strong> Kompresi sangat tinggi, ukuran file jauh berkurang. <br>
                    <strong>Maximum Compression:</strong> Kompresi maksimal, ukuran file terkecil.
                </p>
            </div>
            <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="validateAndUpload()">Create ZIP</button>
        </form>
        <div id="loader"></div>
        <div id="statusMessage" class="mt-4 text-center"></div>
    </div>

    <script>
        function validateAndUpload() {
            const fileInput = document.querySelector('input[name="files[]"]');
            if (fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Silakan pilih file terlebih dahulu!',
                });
            } else {
                uploadFiles();
            }
        }

        function uploadFiles() {
            const form = document.getElementById('uploadForm');
            const formData = new FormData(form);

            document.getElementById('loader').style.display = 'block';
            document.getElementById('statusMessage').innerHTML = 'Uploading...';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);

            xhr.onload = function() {
                document.getElementById('loader').style.display = 'none';

                if (xhr.status === 200) {
                    const result = JSON.parse(xhr.responseText);

                    if (result.status === 'success') {
                        document.getElementById('statusMessage').innerHTML = `
                            File uploaded and compressed. 
                            <a href="?download=${result.zipName}" class="text-blue-500 underline" onclick="deleteFile('${result.zipName}')">Download ZIP</a>
                        `;
                    } else {
                        document.getElementById('statusMessage').innerHTML = 'Failed to create ZIP file.';
                    }
                } else {
                    document.getElementById('statusMessage').innerHTML = 'An error occurred while uploading.';
                }
            };

            xhr.onerror = function() {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('statusMessage').innerHTML = 'An error occurred while uploading.';
            };

            xhr.send(formData);
        }

        function deleteFile(fileName) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `?download=${fileName}`, true);
            xhr.send();
        }
    </script>
</body>
</html>
