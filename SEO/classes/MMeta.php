<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MMeta extends E\Module
{

    private $title = '';
    private $description = '';
    private $keywords = '';

    public $type = 'article';
    public $imageUris = [];

    public function __construct(EC\Basic\MHead $header, EC\Facebook\MOpenGraph $og)
    {
        parent::__construct();

        $this->header = $header;
    }

    public function addImage($imageUri)
    {
        array_unshift($this->imageUris, $imageUri);
    }

    public function setDescription($description)
    {
        $description = strip_tags($description);
        if (mb_strlen($description) >= 300)
            $description = mb_substr($description, 0, 300);

        $this->description = $description;
    }

    public function setTitle($title)
    {
        $this->title = $title;        
    }


    protected function _preDisplay(E\Site $site)
    {
        $this->header->setTitle($this->title);
        $this->header->setDescription($this->description);
        $this->header->setKeywords($this->keywords);

        $this->header->addTag('meta', [
            'property' => 'og:url',
            'content' => E\Uri::Current(false),
        ], true);
        $this->header->addTag('meta', [
            'property' => 'og:type',
            'content' => $this->type,
        ], true);

        $this->header->addTag('meta', [
            'property' => 'og:title',
            'content' => $this->title,
        ], true);
        $this->header->addTag('meta', [
            'property' => 'og:description',
            'content' => $this->description,
        ], true);

        foreach ($this->imageUris as $imageUri) {
            $this->header->addTag('meta', [
                'property' => 'og:image',
                'content' => $imageUri,
            ], true);
        }
    }

}