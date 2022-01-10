<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client;
use Symfony\Component\Routing\Annotation\Route;

class PopulateController extends AbstractController
{
    #[Route('/articles/get-news-articles', name: 'article_get_news_articles')]
    public function articleFetch(EntityManagerInterface $em, ArticleRepository $articleRepository)
    {

        $page = 1;
        $parPage = 10;

        $last = $articleRepository->findOneBy([], ['createdAt' => 'DESC']);
        $date = $last->getCreatedAt();
        dump($date->format("Y-m-d\TH:i:sO"));
        $jsonData = json_decode(file_get_contents('https://www.canardpc.com/wp-json/wp/v2/posts?after='.$date->format("Y-m-d\TH:i:s").'&page='.$page.'&per_page='.$parPage.'&order=asc'),true);
    
        foreach ($jsonData as $value) {
            $article = new Article();           

            $article->setTitle(strip_tags($value['title']['rendered']));
            $article->setExcerpt(strip_tags($value['excerpt']['rendered']));
            $article->setGuid($value['id']);
            $article->setLink($value['link']);

            $article->setRealCreatedAt(new \DateTimeImmutable($value['date_gmt']));
            $article->setRealUpdatedAt(new \DateTimeImmutable($value['modified_gmt']));

            if ('' !== $value['featured_media']) {
                $article->setImageALaUne($value['featured_media']);
                try {
                    $imageUrl = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/media/'.$value['featured_media']);
                    $imageUrlData = json_decode($imageUrl, true);
                    if ([] != $imageUrlData['media_details']['sizes']) {
                        $article->setImageUrl($imageUrlData['media_details']['sizes']['flex-config-product']['source_url']);
                    } else {
                        $article->setImageUrl('https://cdn.canardware.com/'.$imageUrlData['media_details']['file']);
                    }
                } catch (\Throwable $th) {                    
                    $article->setImageALaUne(null);              
                }  
            }
            $em->persist($article);    
            dump($article);
        }

        // dump($last);
        exit();
        $em->flush();
    }


    #[Route('/articles/populate-from-start', name: 'populate_from_start')]
    public function populate(EntityManagerInterface $em, ArticleRepository $articleRepository): Response
    {
        $offset = $articleRepository->count([]);
        dump($offset);
        $jsonData = json_decode(file_get_contents('https://www.canardpc.com/wp-json/wp/v2/posts?offset='.$offset.'&order=asc&per_page=100'),true);

        foreach ($jsonData as $value) {
            $article = new Article();           

            $article->setTitle(strip_tags($value['title']['rendered']));
            $article->setExcerpt(strip_tags($value['excerpt']['rendered']));
            $article->setGuid($value['id']);
            $article->setLink($value['link']);

            $article->setRealCreatedAt(new \DateTimeImmutable($value['date_gmt']));
            $article->setRealUpdatedAt(new \DateTimeImmutable($value['modified_gmt']));

            if ('' !== $value['featured_media']) {
                $article->setImageALaUne($value['featured_media']);
                try {
                    $imageUrl = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/media/'.$value['featured_media']);
                    $imageUrlData = json_decode($imageUrl, true);
                    if ([] != $imageUrlData['media_details']['sizes']) {
                        $article->setImageUrl($imageUrlData['media_details']['sizes']['flex-config-product']['source_url']);
                    } else {
                        $article->setImageUrl('https://cdn.canardware.com/'.$imageUrlData['media_details']['file']);
                    }
                } catch (\Throwable $th) {
                    //throw $th;     
                    $article->setImageALaUne(null);              
                }  
            }
            $em->persist($article);    
            // dump($article);
        }
        $em->flush();
        exit();
        $em->flush();

        return $this->render('populate/index.html.twig', [
            'controller_name' => 'PopulateController',
        ]);
    }


    // #[Route('/populate', name: 'populate')]
    public function index(EntityManagerInterface $em, ArticleRepository $articleRepository): Response
    {
        $i = 0;
        $a = 0;
        $page = 1;
        $parPage = 10;
        $client = Client::createChromeClient();
        // $header = get_headers("https://www.canardpc.com/wp-json/wp/v2/posts?per_page=".$parPage,true);
        $json = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/posts?page='.$page.'&per_page='.$parPage);
        $jsonData = json_decode($json, true);
        // dump($jsonData);
        while ($a < $parPage - 1) {
            // dump("i".$i);
            // dump("a".$a);
            $article = $articleRepository->findOneByGuid($jsonData[$i]['id']);

            if ($article) {
                if (
                        (!$article->getIs404()) &&
                        ($article->geRealUpdatedAt() < $jsonData[$i]['modified_gmt']) &&
                        (!$article->getIsFreeContent())
                    ) {
                       
                    $article->setRealUpdatedAt(new \DateTimeImmutable($jsonData[$i]['modified_gmt']));

                    $crawler = $client->request('GET', $jsonData[$i]['link']);
                    $chouineur = $crawler->filter('.whines')->filter('p')->text();
                    $article->setChouineurs(intval($chouineur));
                    dump('article mis a jour');
                    dump($article);
                    $em->persist($article);
                    $em->flush();
                    ++$a;
                }
                //    dump("article présent et ignoré");
            } else {
                $article = new Article();

                $article->setExcerpt(strip_tags($jsonData[$i]['excerpt']['rendered']));
                $article->setGuid($jsonData[$i]['id']);
                $article->setLink($jsonData[$i]['link']);
                dump($article->getLink());
                if ('' !== $jsonData[$i]['featured_media']) {
                    $article->setImageALaUne($jsonData[$i]['featured_media']);
                    $imageUrl = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/media/'.$jsonData[$i]['featured_media']);
                    $imageUrlData = json_decode($imageUrl, true);

                    if ([] != $imageUrlData['media_details']['sizes']) {
                        $article->setImageUrl($imageUrlData['media_details']['sizes']['flex-config-product']['source_url']);
                    } else {
                        $article->setImageUrl('https://cdn.canardware.com/'.$imageUrlData['media_details']['file']);
                    }
                }
                $article->setTitle(strip_tags($jsonData[$i]['title']['rendered']));
                $article->setRealCreatedAt(new \DateTimeImmutable($jsonData[$i]['date_gmt']));
                $article->setRealUpdatedAt(new \DateTimeImmutable($jsonData[$i]['modified_gmt']));
                $crawler = $client->request('GET', $article->getLink());

                if ($crawler->filter('.error-404')->count() > 0) {
                    $article->setIs404(true);
                    $article->setChouineurs(0);
                    $article->setIsFreeContent(false);
                } elseif ($access = $crawler->filter('.post-access')) {
                    if ('Accessible à tout le monde' == $access->text()) {
                        $article->setIsFreeContent(true);
                        $article->setChouineurs(0);
                    } elseif ('Accessible uniquement aux abonnés' == $access->text()) {
                        $article->setIsFreeContent(false);
                        $chouineur = $crawler->filter('.whines')->filter('p')->text();
                        $article->setChouineurs(intval($chouineur));
                    }
                }

                dump('nouvel article');
                dump($article);
                $em->persist($article);
                $em->flush();
                ++$a;
            }

            if ($i == $parPage - 1) {
                ++$page;
                $json = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/posts?page='.$page.'&per_page='.$parPage);
                $jsonData = json_decode($json, true);
                dump('nouvelle requette');
                // dump($jsonData);
                $i = 0;
            }
            ++$i;
        }

        exit();

        return $this->render('populate/index.html.twig', [
            'controller_name' => 'PopulateController',
        ]);
    }
}
