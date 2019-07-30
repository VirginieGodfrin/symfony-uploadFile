<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;

class UploaderHelper
{
    // constante whith folder name
    const ARTICLE_IMAGE = 'article_image';
	// use dependencie injecton to get parameter from 
	private $uploadsPath;

    // RequestStackContext is the service that's used internally by the asset() function to determine the subdirectory.
    // and add a / before uploads
    private $requestStackContext;

    public function __construct(string $uploadsPath, RequestStackContext $requestStackContext)
    {
        $this->uploadsPath = $uploadsPath;
        $this->requestStackContext = $requestStackContext;
    }

    // 2 - getPublicPath take a string $path - that will be something like article_image/astronaut.jpeg - 
    // and it return a string, which will be the actual public path to the file. Inside, return 'uploads/'.$path;.
    // Thanks to this, we can call getPublicPath() from anywhere in our app to get the URL to an uploaded asset. 
    // If we move to the cloud, we only need to change the URL here! Awesome!
    public function getPublicPath(string $path): string
    {
        // needed if you deploy under a subdirectory
        // dump($this->requestStackContext->getBasePath());
        return $this->requestStackContext
            ->getBasePath().'/uploads/'.$path;
    }

    // 5 - and because  uploadArticleImage work now with File obj change the type-hint
	public function uploadArticleImage(File $file): string
	{
		$destination = $this->uploadsPath.'/'.self::ARTICLE_IMAGE;

        // $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        // 6 - getClientOriginalName() doest'n exist in File
        // if $file is an instanceof UploadedFile, we can say $originalFilename = $file->getClientOriginalName(). 
        // Else, set $originalFilename to $file->getFilename() - that's just the name of the file on the filesytem.
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        // 7 - push the pathinfo here
        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->guessExtension();

        $file->move(
            $destination,
            $newFilename
        );

        return $newFilename;
	}

}