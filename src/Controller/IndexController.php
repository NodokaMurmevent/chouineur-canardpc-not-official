<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $em, ArticleRepository $articleRepository, SluggerInterface $slugger): Response
    {
        $date = new \DateTime();
        $date->setTimestamp(strtotime('Monday this week'));

        $nbrsChouineur = 0;
        $articlesChouineur = $articleRepository->findByChouineurs();
        foreach ($articlesChouineur as $article) { $nbrsChouineur = $nbrsChouineur + $article->getChouineurs();}

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'articlesRandom' => $articleRepository->findRandomArticle(),
            'lundi' => $date,
            'articlesChouineur' => $articlesChouineur,
            'nbrsChouineur' => $nbrsChouineur,
            'articlesDerniers' => $articleRepository->findWeeklyArticles($date),
            'articlesFree' => $articleRepository->findBy(['isFreeContent' => true]),
            'totalArticles' => $articleRepository->count([]),
            'totalNoLocalImage' => $articleRepository->count(['imageALaUne' => !null,'localImage' => null]),
            'totalImageCassee' => $articleRepository->count(['imageALaUne' => null,'localImage' => null]),
        ]);
    }
}
