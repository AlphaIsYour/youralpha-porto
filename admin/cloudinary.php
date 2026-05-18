<?php
// admin/cloudinary.php - Cloudinary unsigned upload helper

function getCloudinaryConfig(): array {
    $envFile = __DIR__ . '/../.env';
    $config = [
        'cloud_name' => '',
        'upload_preset' => '',
    ];
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if ($key === 'CLOUDINARY_CLOUD_NAME') $config['cloud_name'] = $value;
                if ($key === 'CLOUDINARY_UPLOAD_PRESET') $config['upload_preset'] = $value;
            }
        }
    }
    return $config;
}

function cloudinaryUploadWidget(string $fieldId = 'image_url', ?string $currentUrl = null): string {
    $config = getCloudinaryConfig();
    $cloudName = htmlspecialchars($config['cloud_name']);
    $uploadPreset = htmlspecialchars($config['upload_preset']);

    $html = '
    <div class="cloudinary-upload">
        <div id="upload_widget_' . $fieldId . '" class="upload-area">
            <div id="preview_' . $fieldId . '" class="upload-preview">';
    if ($currentUrl) {
        $html .= '<img src="' . htmlspecialchars($currentUrl) . '" alt="Preview">';
    } else {
        $html .= '<span class="upload-placeholder">Click to upload image</span>';
    }
    $html .= '</div>
        </div>
        <input type="hidden" id="' . $fieldId . '" name="' . $fieldId . '" value="' . htmlspecialchars($currentUrl ?? '') . '">
        <button type="button" class="btn btn-sm btn-secondary" onclick="openCloudinaryWidget_' . $fieldId . '()">Choose Image</button>
        <button type="button" class="btn btn-sm btn-danger" onclick="clearImage_' . $fieldId . '()" style="display:' . ($currentUrl ? 'inline-block' : 'none') . '" id="clear_btn_' . $fieldId . '">Remove</button>
    </div>

    <script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script>
    <script>
    function openCloudinaryWidget_' . $fieldId . '() {
        cloudinary.openUploadWidget({
            cloudName: "' . $cloudName . '",
            uploadPreset: "' . $uploadPreset . '",
            sources: ["local", "url", "camera"],
            multiple: false,
            resourceType: "image",
            clientAllowedFormats: ["jpg", "jpeg", "png", "gif", "webp", "svg"],
            maxFileSize: 2000000,
            cropping: false,
            styles: {
                palette: { window: "#fff", sourceBg: "#f8f9fa", windowBorder: "#dcdfe6", tabIcon: "#3498db", link: "#3498db", action: "#27ae60", inProgress: "#f39c12", complete: "#27ae60", error: "#e74c3c" }
            }
        }, function(error, result) {
            if (!error && result && result.event === "success") {
                var url = result.info.secure_url;
                document.getElementById("' . $fieldId . '").value = url;
                document.getElementById("preview_' . $fieldId . '").innerHTML = \'<img src="\' + url + \'" alt="Preview">\';
                document.getElementById("clear_btn_' . $fieldId . '").style.display = "inline-block";
            }
        });
    }
    function clearImage_' . $fieldId . '() {
        document.getElementById("' . $fieldId . '").value = "";
        document.getElementById("preview_' . $fieldId . '").innerHTML = \'<span class="upload-placeholder">Click to upload image</span>\';
        document.getElementById("clear_btn_' . $fieldId . '").style.display = "none";
    }
    </script>';
    return $html;
}
