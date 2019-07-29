<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;

class UploaderHelper
{
	// use dependencie injecton to get parameter from 
	private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

	public function uploadArticleImage(UploadedFile $uploadedFile): string
	{
		$destination = $this->uploadsPath.'/article_image';

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
	}

}