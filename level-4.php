<?php
define('MAX_FILE_SIZE', 104857600); // 100MB in bytes

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['files']['size'][$key] > MAX_FILE_SIZE) {
            echo json_encode(['status' => 'fail', 'message' => 'File exceeds the maximum allowed size of 100MB.']);
            exit;
        }
    }

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
        echo json_encode(['status' => 'fail', 'message' => 'Failed to create ZIP file.']);
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
        #progressBar {
            display: none;
            width: 100%;
            background-color: #f3f3f3;
        }

        #progressBar div {
            width: 0;
            height: 24px;
            background-color: #4caf50;
            text-align: center;
            line-height: 24px;
            color: white;
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
        <div id="progressBar" class="mt-4">
            <div>0%</div>
        </div>
        <span id="progressText" class="text-red-500"></span>
        <div id="statusMessage" class="mt-4 text-center"></div>
    </div>

    <script>
        const MAX_FILE_SIZE = 104857600; // 100MB in bytes

        function validateAndUpload() {
            const fileInput = document.querySelector('input[name="files[]"]');
            let valid = true;

            for (let i = 0; i < fileInput.files.length; i++) {
                if (fileInput.files[i].size > MAX_FILE_SIZE) {
                    valid = false;
                    break;
                }
            }

            if (!valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Salah satu file melebihi ukuran maksimum 100MB!',
                });
            } else if (fileInput.files.length === 0) {
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

            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            progressText.textContent = 'Compressing. Please wait...';
            progressBar.style.display = 'block';
            progressBar.firstElementChild.style.width = '0%';
            progressBar.firstElementChild.textContent = '0%';

            document.getElementById('statusMessage').innerHTML = '';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);

            xhr.upload.onprogress = function(event) {
                if (event.lengthComputable) {
                    const percentComplete = (event.loaded / event.total) * 100;
                    progressBar.firstElementChild.style.width = percentComplete.toFixed(2) + '%';
                    progressBar.firstElementChild.textContent = percentComplete.toFixed(2) + '%';
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const result = JSON.parse(xhr.responseText);

                    if (result.status === 'success') {
                        progressBar.style.display = 'block';
                        progressText.style.display = 'none';
                        progressBar.firstElementChild.style.width = '100%';
                        progressBar.firstElementChild.textContent = 'Complete!';

                        document.getElementById('statusMessage').innerHTML = `
                            File uploaded and compressed. 
                            <a href="?download=${result.zipName}" class="text-blue-500 underline" onclick="deleteFile('${result.zipName}')">Download ZIP</a>
                        `;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while uploading.',
                        });
                        progressBar.style.display = 'none';
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while uploading.',
                    });
                    progressBar.style.display = 'none';
                }
            };

            xhr.onerror = function() {
                progressBar.style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while uploading.',
                });
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