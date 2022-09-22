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
            $fullContent .= $sectionText ;    
        }
        
            return $fullContent;
        
    }
}
