<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index( ArticleRepository $articleRepository): Response
    {
        $date =new \DateTime();
        $date->setTimestamp(strtotime('Monday this week'));
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            "articlesRandom" => $articleRepository->findRandomArticle(),
            "lundi" => $date,
            "articlesChouineur" => $articleRepository->findByChouineurs(),
            "articlesDerniers" => $articleRepository->findWeeklyArticles($date),
            "articlesFree" => $articleRepository->findBy(["isFreeContent" => true]),
            "totalError" => $articleRepository->count(["is404" => true]),
            "totalManquant" => $articleRepository->count(["chouineurs"=>null,"isFreeContent"=>null,"is404"=>null]),
            "totalArticles" => $articleRepository->count([]),
            "totalArticlesWithChouineurs" => $articleRepository->countArticleWithChouineurs(),
            
        ]);
    }

}
