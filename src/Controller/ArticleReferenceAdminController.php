<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Article;
use App\Service\UploaderHelper;
use App\Entity\ArticleReference;

class ArticleReferenceAdminController extends BaseController
{
	/**
     * @Route("/admin/article/{id}/references", name="admin_article_add_reference", methods={"POST"})
     * @IsGranted("MANAGE", subject="article")
     */
    public function uploadArticleReference(EntityManagerInterface $em, Article $article, Request $request, UploaderHelper $uploaderHelper)
    {
    	/** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('reference');

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

        return $this->redirectToRoute('admin_article_edit', [
            'id' => $article->getId(),
        ]);
    }
}