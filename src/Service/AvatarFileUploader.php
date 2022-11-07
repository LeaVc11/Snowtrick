<?php

namespace App\Service;

use App\Entity\Avatar;
use App\Service\FileUploader;

class AvatarFileUploader extends FileUploader
{

    public function deleteAvatarFile(Avatar $avatar)
    {
        $file = $this->publicDirectory . $avatar->getUrl();
        if(file_exists($file) && $avatar->getUrl() != Avatar::DEFAULT_IMG_URL) {
            unlink($file);
        }
    }

}