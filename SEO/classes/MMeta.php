<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Basic\MHead;
use EC\Strings\HStrings;

class MMeta extends E\Module {

    private MHead $header;

    private $title = '';
    private $description = '';
    private $keywords = '';

    public $type = 'article';
    public $imageUris = [];

    public function __construct(MHead $header) {
        parent::__construct();

        $this->header = $header;
    }

    public function addImage($imageUri) {
        if (in_array($imageUri, $this->imageUris))
            return;

        array_unshift($this->imageUris, $imageUri);
    }

    public function setDescription($description) {
        $description = strip_tags($description);
        $description = HStrings::RemoveCharacters($description, 
                HStrings::GetCharsRegexp([ 'digits', 'letters' ], '\\., '));
        $description = HStrings::RemoveDoubles($description, ' ');
        if (mb_strlen($description) > 300)
            $description = mb_substr($description, 0, 297) . '...';

        $this->description = $description;
    }

    public function setKeywords($keywords) {
        $this->keywords = $keywords;
    }

    public function setTitle($title) {
        $title = strip_tags($title);
        $title = HStrings::RemoveCharacters($title, 
                HStrings::GetCharsRegexp([ 'digits', 'letters' ], '\\., '));
        $title = HStrings::RemoveDoubles($title, ' ');

        $this->title = $title;        
    }


    protected function _preDisplay(E\Site $site) {
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