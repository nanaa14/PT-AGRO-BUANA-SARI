<?php
function upload_image(array $file, string $destination): array {
    if ($file['error'] !== UPLOAD_ERR_OK) return ['ok'=>false,'error'=>'Upload foto gagal.'];
    if ($file['size'] > 2 * 1024 * 1024) return ['ok'=>false,'error'=>'Ukuran foto maksimal 2 MB.'];
    $finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($allowed[$mime])) return ['ok'=>false,'error'=>'Format harus JPG, PNG, atau WEBP.'];
    if (!is_dir($destination)) mkdir($destination,0755,true);
    $name = bin2hex(random_bytes(10)).'.'.$allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $destination.$name)) return ['ok'=>false,'error'=>'Foto tidak dapat disimpan.'];
    return ['ok'=>true,'name'=>$name];
}
?>