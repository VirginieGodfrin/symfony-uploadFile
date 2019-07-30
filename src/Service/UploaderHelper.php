<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;

class UploaderHelper
{
    // constante whith folder name
    const ARTICLE_IMAGE = 'article_image';
	// use dependencie injecton to get parameter from 
	private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    // 2 - getPublicPath take a string $path - that will be something like article_image/astronaut.jpeg - 
    // and it return a string, which will be the actual public path to the file. Inside, return 'uploads/'.$path;.
    // Thanks to this, we can call getPublicPath() from anywhere in our app to get the URL to an uploaded asset. 
    // If we move to the cloud, we only need to change the URL here! Awesome!
    public function getPublicPath(string $path): string
    {
        return 'uploads/'.$path;
    }

	public function uploadArticleImage(UploadedFile $uploadedFile): string
	{
		$destination = $this->uploadsPath.'/'.self::ARTICLE_IMAGE;

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
	}

}