<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Database;
use Framework\Exceptions\ValidationException;

class ReceiptService {
  public function __construct(private Database $db) {
  }

  //File upload validation
  public function validateFile(?array $file) {
    if(!$file || $file['error'] !== UPLOAD_ERR_OK) {
      throw new ValidationException([
        //$file['receipt'] contains error message:
        'receipt' => ["Failed to upload file."]
      ]);
    }

    //File size validation
    $maxFileSizeMB = 3;                                 // set max file size [MB]
    $maxFileSizeBytes = $maxFileSizeMB * 1024 * 1024;   // convert to bytes
    
    if($file['size'] > $maxFileSizeBytes) {
      throw new ValidationException([
        'receipt' => ["File upload is too large."]
      ]);
    }

    //File name validation
    $originalFileName = $file['name'];

    if(!preg_match('/^[A-Za-z0-9\s._-]+$/', $originalFileName)) {
      throw new ValidationException([
        'receipt' => ["Invalid filename."]
      ]);
    }

    //File MIME type validation
    $uploadedMimeType = $file['type'];
    $allowMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    if(!in_array($uploadedMimeType, $allowMimeTypes)) {
      throw new ValidationException([
        'receipt' => ["Invalid file type."]
      ]);
    }
  }
}