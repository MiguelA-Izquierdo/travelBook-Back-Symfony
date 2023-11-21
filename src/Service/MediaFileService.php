<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaFileService
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        $url = $this->urlGenerator->generate('show_image', ['filename' => $fileName], UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
