<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Panther\Client;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index( ArticleRepository $articleRepository): Response
    {
          
        
        // $ids = array_rand(array_column($articleRepository->findAllId(),"id"),4);
// dump($articleRepository->findAllId());
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            "articlesRandom" => $articleRepository->findRandomArticle(),
            // "articlesRandom" => $articleRepository->findRandomArticle(),
            "articlesChouineur" => $articleRepository->findByChouineurs(),
            // "articlesPremium" => $articleRepository->findBy(["isFreeContent" => false]),
            "articlesFree" => $articleRepository->findBy(["isFreeContent" => true]),
            "articlesError" => $articleRepository->findBy(["is404" => true]),
        ]);
    }

    #[Route('/scrapping/{page}', name: 'scrapping')]
    public function scrapping($page = 1 ): Response
    {
        $client = Client::createChromeClient();
        $cache = new FilesystemAdapter();

        $json = file_get_contents("https://www.canardpc.com/wp-json/wp/v2/posts?page=".$page);
        $header = get_headers("https://www.canardpc.com/wp-json/wp/v2/posts?page=".$page,true);

        $jsonData = json_decode($json, true);

        $articles = [];
        foreach ($jsonData as $key => $v) {           
            $id = $v["id"];
            $chouineurCache = $cache->getItem('articles.'.$id);

            if (!$chouineurCache->isHit()) {

                $crawler = $client->request('GET', $v["link"]);
            
                $chouineur = $crawler->filter('.whines')->filter("p")->text();
               
                $articles[$id]["link"]= $v["link"];
                $articles[$id]["title"]= $v["title"]["rendered"];
                $articles[$id]["nombre"]=42;//intval($chouineur);
                $articles[$id]["excerpt"]=$v["excerpt"]["rendered"];

                $chouineurCache->set($articles[$id]);
                $cache->save($chouineurCache); 
            }
            $articles[$id] = $chouineurCache->get();

            

        } 
        // $cache->deleteItem('articles');
        dump($json);
        dump($articles);
        // exit();

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            "articles" => $articles,
            "pages" => $header["X-WP-TotalPages"],
            "total" => $header["X-WP-Total"],
            "page" => $page
        ]);
    }
}
