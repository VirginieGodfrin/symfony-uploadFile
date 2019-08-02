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

        // Before upload check violation , if there is one or more forward the route with a flash message  
        if ($violations->count() > 0) {
            // dd($violations);
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];

            $this->addFlash('error', $violation->getMessage());

            return $this->redirectToRoute('admin_article_edit', [
                'id' => $article->getId(),
            ]);
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

        // Don't render a template but foward a route
        return $this->redirectToRoute('admin_article_edit', [
            'id' => $article->getId(),
        ]);
    }


    /**
     * @Route("/admin/article/references/{id}/download", name="admin_article_download_reference", methods={"GET"})
     */
    public function downloadArticleReference(ArticleReference $reference, UploaderHelper $uploaderHelper)
    {
        $article = $reference->getArticle();
        $this->denyAccessUnlessGranted('MANAGE', $article);

        dd($reference);
    }
}