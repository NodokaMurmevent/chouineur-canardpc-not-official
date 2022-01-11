<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
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

        // $r = $articleRepository->findByChouineurs();

        // foreach ($r as $key => $article) {
        //     $url = parse_url($article->getImageUrl());           
        //     if (isset($url['host'])) {
                   
        //         $path_parts = pathinfo($url['path']);
        //         $folder1 = explode('/', $path_parts['dirname'])['1'];
        //         $folder2 = explode('/', $path_parts['dirname'])['2'];
        //         $folderComplet = '/'.$folder1.'/'.$folder2;
        //         $tempFixturesPath = $this->getParameter('kernel.project_dir').'/var/tmp';
        //         // dump($article);
        //         try {
        //             $image = file_get_contents($article->getImageUrl());
        //         } catch (FileException $e) {
        //             // dump($e);
        //             $image = false;
        //         }
        //         $imgRaw = imagecreatefromstring($image);
        //         imagejpeg($imgRaw, $tempFixturesPath.'/tmp.jpg', 100);
        //         imagedestroy($imgRaw);
        //         $file = new File($tempFixturesPath.'/tmp.jpg');

        //         if ($file) {
        //             $safeFilename = strtolower($slugger->slug($article->getTitle()));
        //             $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        //             try {
        //                 $file->move(
        //                     $this->getParameter('articles_directory').$folderComplet,
        //                     $newFilename
        //                 );
        //             } catch (FileException $e) {
        //                 // dump($e);
        //             }

        //             $article->setLocalImage("/uploads/articles".$folderComplet.'/'.$newFilename);
        //             $article->setImageUrl(null);
        //         }
        //         $em->persist($article);              
        //         $em->flush();
        //     }
        // }

   

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'articlesRandom' => $articleRepository->findRandomArticle(),
            'lundi' => $date,
            'articlesChouineur' => $articleRepository->findByChouineurs(),
            'articlesDerniers' => $articleRepository->findWeeklyArticles($date),
            'articlesFree' => $articleRepository->findBy(['isFreeContent' => true]),
            'totalError' => $articleRepository->count(['is404' => true]),
            'totalManquant' => $articleRepository->count(['chouineurs' => null, 'isFreeContent' => null, 'is404' => null]),
            'totalArticles' => $articleRepository->count([]),
            'totalArticlesWithChouineurs' => $articleRepository->countArticleWithChouineurs(),
        ]);
    }
}
