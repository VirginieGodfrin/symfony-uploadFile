<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;

class UploaderHelper
{
    // constante whith folder name
    const ARTICLE_IMAGE = 'article_image';

    // use Filesystem
    private $filesystem;

    // RequestStackContext is the service that's used internally by the asset() function to determine the subdirectory.
    private $requestStackContext;

    private $logger;

    private $publicAssetBaseUrl;

    public function __construct(
        FilesystemInterface $publicUploadsFilesystem, 
        RequestStackContext $requestStackContext,  
        LoggerInterface $logger, 
        string $uploadedAssetsBaseUrl)
    {
        $this->requestStackContext = $requestStackContext;
        $this->filesystem = $publicUploadsFilesystem;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    // 2 - getPublicPath take a string $path - that will be something like article_image/astronaut.jpeg - 
    // and return a string: the actual public path to the file.
    public function getPublicPath(string $path): string
    {
        // needed if you deploy under a subdirectory
        return $this->requestStackContext
            ->getBasePath().$this->publicAssetBaseUrl.'/'.$path;
    }

	public function uploadArticleImage(File $file, ?string $existingFilename): string
	{

        // If $file is an instanceof UploadedFile $originalFilename = $file->getClientOriginalName(). 
        // Else, set $originalFilename to $file->getFilename() - that's just the name of the file on the filesytem.
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->guessExtension();

        // the stream :
        $stream = fopen($file->getPathname(), 'r');
        // Whrite the stream
        $result = $this->filesystem->writeStream(
            self::ARTICLE_IMAGE.'/'.$newFilename,
            $stream
        );

        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        // close the stream
        if (is_resource($stream)) {
            fclose($stream);
        }
        // delete old ressource after update
        // try catch is necessery if the old ressource in non existance in folder
        // it do not return an exception but a log
        if ($existingFilename) {
            try {
                $result = $this->filesystem->delete(self::ARTICLE_IMAGE.'/'.$existingFilename);
                if ($result === false) {
                    throw new \Exception(sprintf('Could not delete old upload file "%s"', $newFilename));
                }
            } catch (FileNotFoundException $e) {
                $this->logger->alert(sprintf('Old uploaded file "%s" was missing when trying to delete', $existingFilename));
            }
        }

        return $newFilename;
	}

}