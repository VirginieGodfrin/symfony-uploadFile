<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Article;
use App\Service\UploaderHelper;
use App\Entity\ArticleReference;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;

class ArticleReferenceAdminController extends BaseController
{
	/**
     * @Route("/admin/article/{id}/references", name="admin_article_add_reference", methods={"POST"})
     * @IsGranted("MANAGE", subject="article")
     */
    public function uploadArticleReference(
        EntityManagerInterface $em, 
        Article $article, 
        Request $request, 
        UploaderHelper $uploaderHelper,
        ValidatorInterface $validator)
    {
    	/** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('reference');

        // By default, Dropzone uploads a field called file. But in the controller, we're expecting it to be called reference.
        // dump($uploadedFile);

        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank(),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain'
                    ]
                ]) 
            ]
        );

        if ($violations->count() > 0) {
            // return json violation because this controller is an API endpoint
            return $this->json($violations, 400);
        }

        // get the file name with uploadHelper
        $filename = $uploaderHelper->uploadArticleReference($uploadedFile);
        // Create the new articleref with article arg
        $articleReference = new ArticleReference($article);

        $articleReference->setFilename($filename);

        $articleReference->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);

        $articleReference->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');

        dump($articleReference);

        $em->persist($articleReference);
        $em->flush();

        // And also return a json response
        // json() serialize articleReference , by default it's serialize all the proprieties that have getter methods
        // and also the articleReferences propertie's article because: getArticleReferences()
        // This is why we create an serialisation group
        // return $this->json($articleReference);
        return $this->json(
            $articleReference, // the data
            201, // the statut code
            [], // the header
            [
                'groups' => ['main'] // the context 
            ]
        );
    }


    /**
     * @Route("/admin/article/references/{id}/download", name="admin_article_download_reference", methods={"GET"})
     */
    public function downloadArticleReference(ArticleReference $reference, UploaderHelper $uploaderHelper)
    {
        $article = $reference->getArticle();
        $this->denyAccessUnlessGranted('MANAGE', $article);

        // We use StreamedResponse obj to return a strem to the user
        // Add a use statement and bring $reference and $uploaderHelper into the callback's scope so we can use them
        $response = new StreamedResponse(function() use ($reference, $uploaderHelper) {
            // write php://output in the stream
            $outputStream = fopen('php://output', 'wb');

            $fileStream = $uploaderHelper->readStream($reference->getFilePath(), false);

            //  Now we have a "write" stream and a "read" stream, 
            //  we use a function called stream_copy_to_stream() to...
            //  Copy $fileStream to $outputStream.
            stream_copy_to_stream($fileStream, $outputStream);
        });

        // Set the header response to tell the browser what kind of file is it.
        $response->headers->set('Content-Type', $reference->getMimeType());

        // force the browser to download the file and to do that we need Content-Disposition.
        $disposition = HeaderUtils::makeDisposition(
            // the browser got two way to upload :
            //      download the file : HeaderUtils::DISPOSITION_ATTACHMENT 
            //      open it in the browser: HeaderUtils::DISPOSITION_INLINE.
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $reference->getOriginalFilename()
        );

        // dd($disposition);
        // and this 'format' to the header
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}