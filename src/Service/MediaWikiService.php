<?php
namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MediaWikiService
{

    private HttpClientInterface $client;
    private array $content;
    private string $apiUrl;
    private array $sectionData;
    

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->content = [];
        $this->apiUrl = 'https://en.wikisource.org/w/api.php?action=parse&prop=text|sections|images&format=json&page=';
        $this->sectionData = [];

    }

    public function fetchMediaWikiMainText($title): array
    {
        $response = $this->client->request(
            'GET',
            $this->apiUrl.$title
        );
        
        $content = $response->getContent();
        $content = $response->toArray();
     
        return $this->content = $content;
    }

    public function getWikiMainText(): string
    {
        return $this->content['parse']['text']['*'];
    }
    
    public function getWikiMainSections(): array
    {
        return $this->content['parse']['sections'];
    }

    public function getWikiMainTitle(): string
    {
        return $this->content['parse']['title'];
    }

    public function fetchWikiSectionData($section): array
    {
        $response = $this->client->request(
            'GET',
            $this->apiUrl.$section['fromtitle'].'&section='.$section['index']-1
         
        );
        
        $content = $response->getContent();  
        $content = $response->toArray();
        return $this->sectionData = $content;
    }

    
    public function getWikiSingleSectionText(): string
    {
        return $this->sectionData['parse']['text']['*'];
    }
}