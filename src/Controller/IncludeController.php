<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles', name: 'articles_')]
class IncludeController extends AbstractController
{
    #[Route('/chouineurs', name: 'chouineurs')]
    public function chouineurs(ArticleRepository $articleRepository): Response
    {      
        return $this->render('includes/articles.html.twig', [
            'devMode' =>($this->getParameter('app.env') == "dev"),
            'articles' => $articleRepository->findByChouineurs(),
        ]);
    }

    #[Route('/recents', name: 'recents')]
    public function recents(ArticleRepository $articleRepository): Response
    {      
        $date = new \DateTime();
        $date->setTimestamp(strtotime('Monday this week'));

        return $this->render('includes/articles.html.twig', [
            'devMode' =>($this->getParameter('app.env') == "dev"),
            'articles' => $articleRepository->findWeeklyArticles($date),
        ]);
    }

    #[Route('/gratuits', name: 'gratuits')]
    public function gratuits(ArticleRepository $articleRepository): Response
    {      
        return $this->render('includes/articles.html.twig', [
            'devMode' =>($this->getParameter('app.env') == "dev"),
            'articles' => $articleRepository->findBy(['isFreeContent' => true]),
        ]);
    }

    #[Route('/randoms', name: 'randoms')]
    public function randoms(ArticleRepository $articleRepository): Response
    {      
        return $this->render('includes/articles.html.twig', [
            'devMode' =>($this->getParameter('app.env') == "dev"),
            'articles' => $articleRepository->findRandomArticle(),
        ]);
    }
}
