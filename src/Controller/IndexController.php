<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use MatomoTracker;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $em, ArticleRepository $articleRepository, SluggerInterface $slugger): Response
    {
      

        if (!($this->getParameter('app.env') == "dev")) {
            $matomoTracker = new MatomoTracker(42, "https://piwik.imago-design.net");
            $matomoTracker->setTokenAuth($this->getParameter('app.tokenMatomo'));
            $matomoTracker->doTrackPageView("Centre de sauvetage de la chouinerie");
        }
        
        $date = new \DateTime();
        $date->setTimestamp(strtotime('Monday this week'));

        $nbrsChouineur = 0;
        $articlesChouineur = $articleRepository->findByChouineurs();
        foreach ($articlesChouineur as $article) { $nbrsChouineur = $nbrsChouineur + $article->getChouineurs();}

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            
            'lundi' => $date,
            // 'devMode' =>$devmode,
            // 'articlesChouineur' => $articlesChouineur,
            'nbrsChouineur' => $nbrsChouineur,
            // 'articlesDerniers' => $articleRepository->findWeeklyArticles($date),
            // 'articlesFree' => $articleRepository->findBy(['isFreeContent' => true]),
            'totalArticles' => $articleRepository->count([]),
            'totalArticlesFree' => $articleRepository->count(['isFreeContent' => true]),
            'totalArticlesChouineurs' => $articleRepository->countArticleWithChouineurs(),
            'totalWithLocalImage' => $articleRepository->countImageLocal(),
            'totalImageCassee' => $articleRepository->count(['imageALaUne' => null,'localImage' => null]),
        ]);
    }

    #[Route('/chouinage/{id}', name: 'chouinage')]
    public function chouinage(EntityManagerInterface $em, Article $article): RedirectResponse
    {
        // $article->setIsRecentClicked(true);
        // $em->flush();
        // $em->persist($article);

        if (!($this->getParameter('app.env') == "dev")) {
            $matomoTracker = new MatomoTracker(42, "https://piwik.imago-design.net");
            $matomoTracker->setTokenAuth($this->getParameter('app.tokenMatomo'));
            $matomoTracker->doTrackAction($article->getLink(),"link");
        }
        
        return $this->redirect($article->getLink());      
    }
}
