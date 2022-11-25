<?php

namespace App\Service;

use App\Entity\Media;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
    protected string $publicDirectory;
    protected SluggerInterface $slugger;
    protected string $imgRelativeDirectory;

    public function __construct(string $publicDirectory, string $imgRelativeDirectory, SluggerInterface $slugger)
    {
        $this->publicDirectory = $publicDirectory;
        $this->imgRelativeDirectory = $imgRelativeDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $filepath = $this->publicDirectory . $this->imgRelativeDirectory;

        try {
            $file->move($filepath, $fileName);
        } catch (FileException $e) {

            return $e->getMessage();
        }

        return $this->imgRelativeDirectory. '/' . $fileName;
    }
}