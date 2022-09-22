<?php

namespace App\Controller;

use App\Service\MediaWikiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends AbstractController
{

    #[Route('/', name: 'app_wiki_index', methods:['GET','POST'])]
    public function wikiIndex(MediaWikiService $service, Request $request)
    {
        $error = [];
        $defaultData = ['message' => 'Type your message here'];
        $form = $this->createFormBuilder($defaultData)
        ->add('title', TextType::class)
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $output = $this->processWikiMediaApi($service, $form->get('title')->getData());
            return $this->render('wiki/show.html.twig', ['output' => $output]);
        }
        
        

        return $this->render('wiki/index.html.twig', ['error' => $error,
        'form' => $form->createView()]);
    }    

        

   
    #[Route('/wiki-api', name:'wiki_api', methods:['GET'])]
    public function processWikiMediaApi(MediaWikiService $service, $postedTitle )
    {
        //$postedTitle = 'Gates';
        
        //$url = 'https://en.wikisource.org/w/api.php?action=query&prop=revisions&titles='.$postedTitle.'&rvslots=*&rvprop=content&formatversion=2&format=json&list=allpages&apnamespace=0';
        //$url = 'https://en.wikisource.org/w/api.php?action=query&prop=revisions&titles='.$postedTitle.'&rvslots=*&rvprop=content&formatversion=2&format=json&generator=links';
        //$url = 'https://en.wikisource.org/w/api.php?action=query&prop=revisions&titles=Within%20Our%20Gates&rvslots=*&rvprop=content&format=json&generator=links&generator=allpages';
        //$url = 'https://en.wikisource.org/w/api.php?action=query&prop=revisions&titles=mother&rvslots=*&rvprop=content&format=json&generator=links';
        //$url = 'https://en.wikisource.org/w/api.php?action=query&prop=extracts&titles='.$postedTitle.'&rvslots=*&rvprop=content&formatversion=2&format=json';
       // $url = 'https://en.wikisource.org/w/api.php?action=parse&page=Wikisource:Featured texts&prop=text|sections|images&format=json';
       // $url = 'https://en.wikipedia.org/w/api.php?action=parse&prop=text|images|sections&page=Wikipedia:Unusual_articles&format=json';
        try{
            $response = $service->fetchMediaWikiMainText($postedTitle);
        }catch(JsonException $e){
            throw new NotFoundHttpException('Error: '. $e->getMessage());
        }
        if(isset($response['error']))
        {   //dd('error occured');
            $this->addFlash('error', 'There is no record matching the title you specified.');
            return $this->redirectToRoute('app_wiki_index');
        }

        $fullContent = '';
        $allSectionTitle = [];
        $fullContent .= $service->getWikiMainText();
        $sections = $service->getWikiMainSections();
        foreach($sections  as $section )
        {
            try{
                $sectionData = $service->fetchWikiSectionData($section);
            }catch(JsonException $e){
                throw new NotFoundHttpException('Error: '. $e->getMessage());
            }
            $sectionText = $service->getWikiSingleSectionText();
            //dd($section, $sectionData  , $response);
            $allSectionTitle[] = $section['line'];//$service->getWikiSingleSectionTitle();
            $fullContent .= $sectionText ;    
        }
        
            return $fullContent;
        
    }
}
