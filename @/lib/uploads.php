<?php

function uploadFileWithTimestampAsName(array $file, string $destination, string $suffix = ''): string
{
  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $fileName = time() . '_' . $suffix . '.' . $extension;
  $filePath = "$destination/$fileName";
  move_uploaded_file($file['tmp_name'], $filePath);

  return $fileName;
}
