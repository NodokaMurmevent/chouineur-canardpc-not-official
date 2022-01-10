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


        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            "articlesRandom" => $articleRepository->findRandomArticle(),
       
            "articlesChouineur" => $articleRepository->findByChouineurs(),
            // "articlesPremium" => $articleRepository->findBy(["isFreeContent" => false]),
            "articlesFree" => $articleRepository->findBy(["isFreeContent" => true]),
            "totalError" => $articleRepository->count(["is404" => true]),
            "totalManquant" => $articleRepository->count(["chouineurs"=>null,"isFreeContent"=>null,"is404"=>null]),
            "totalArticles" => $articleRepository->count([]),
            "totalArticlesWithChouineurs" => $articleRepository->countArticleWithChouineurs(),
            
        ]);
    }

    #[Route('/updating-ten-recent/{token}', name: 'updating_ten_recent')]
    public function scrapping(EntityManagerInterface $em, ArticleRepository $articleRepository,$token = null )
    {
        
        if ($token !== $this->getParameter('app.securetoken')) {throw new AccessDeniedHttpException('No token given or token is wrong.');}
        $date = new \DateTime("now");
        $date->modify('-3 month');
        // dumps($date);
        $articles = $articleRepository->findLastArticleWithChouineurs();
        // dump($articles);
       
        $browser = new HttpBrowser(HttpClient::create());


        foreach ($articles as $key => $article) {
            $article->setlastCheckedAt(new \DateTimeImmutable("now"));
            try {
                sleep(1);           
                    
                $crawler = $browser->request('GET', $article->getLink());        
                $errorGet = false;

            } catch (\Throwable $th) {
                //throw $th;
                $errorGet = true;
            }
           
            if (!$errorGet) {        
              
                if ($crawler->filter('.error-404')->count() > 0) {
                    $article->setIs404(true);
                } elseif ($access = $crawler->filter('.post-access')) {
                    if ('Accessible à tout le monde' == $access->text()) {
                        $article->setIsFreeContent(true);
                    } elseif ('Accessible uniquement aux abonnés' == $access->text()) {
                        $article->setIsFreeContent(false);
                        $chouineur = $crawler->filter('.whines')->filter('p')->text();
                        // dump($chouineur);
                        $article->setChouineurs(intval($chouineur));
                    }
                }
                $em->persist($article);
                // dump($article);
                $em->flush();
            }  
        }   
        // exit();
        return new Response('success'); 
    }
}
