<?php

namespace App\Controller;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Michelf\MarkdownInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use App\Service\MarkdownHelper;
use App\Service\SlackClient;

class ArticleController  extends AbstractController
{

    private $isDebug;
    public function __construct(bool $isDebug)
    {
        $this->isDebug = $isDebug;

    }
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(ArticleRepository  $repository)
    {

        $articles = $repository->findAllPublishedOrderedByNewest();
        return $this->render('homepage.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/news/{slug}", name="article_show")
     */
    public function show(Article $article, SlackClient $slack)
    {
        if ($article->getSlug() === 'khaaaaaan') {
            $slack->sendMessage('Kahn', 'Ah, Kirk, my old friend...');
        }
        $comments = $article->getComment();
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comments' => $comments,
        ]);

    }
    /**
     * @Route("/news/{slug}/heart", name="article_toggle_heart", methods={"POST"})
     */
    public function toggleArticleHeart(Article $article, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $article->incrementHeartCount();
        $em->flush();
        $logger->info('Article is being hearted!');
        return new JsonResponse(['hearts' => $article->getHeartCount()]);
    }
}
