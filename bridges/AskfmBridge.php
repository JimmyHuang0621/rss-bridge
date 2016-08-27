<?php
class AskfmBridge extends BridgeAbstract{

    public $maintainer = "az5he6ch";
    public $name = "Ask.fm Answers";
    public $uri = "http://ask.fm/";
    public $description = "Returns answers from an Ask.fm user";
    public $parameters = array(
        'Ask.fm username'=>array(
            'u'=>array(
                'name'=>'Username',
                'required'=>true
            )
        )
    );

    public function collectData(){
        $param=$this->parameters[$this->queriedContext];
        $html = '';
        if (isset($param['u']['value'])) {
            $this->request = $param['u']['value'];
            $html = $this->getSimpleHTMLDOM('http://ask.fm/'.urlencode($this->request).'/answers/more?page=0') or $this->returnServerError('Requested username can\'t be found.');
        }
        else {
            $this->returnClientError('You must specify a username (?u=...).');
        }

        foreach($html->find('div.streamItem-answer') as $element) {
            $item = array();
            $item['uri'] = 'http://ask.fm'.$element->find('a.streamItemsAge',0)->href;
            $question = trim($element->find('h1.streamItemContent-question',0)->innertext);
            $item['title'] = trim(htmlspecialchars_decode($element->find('h1.streamItemContent-question',0)->plaintext, ENT_QUOTES));
            $answer = trim($element->find('p.streamItemContent-answer',0)->innertext);
            #$item['update'] = $element->find('a.streamitemsage',0)->data-hint; // Doesn't work, DOM parser doesn't seem to like data-hint, dunno why
            $visual = $element->find('div.streamItemContent-visual',0)->innertext; // This probably should be cleaned up, especially for YouTube embeds
            //Fix tracking links, also doesn't work
            foreach($element->find('a') as $link) {
                if (strpos($link->href, 'l.ask.fm') !== false) {
                    #$link->href = str_replace('#_=_', '', get_headers($link->href, 1)['Location']); // Too slow
                    $link->href = $link->plaintext;
                }
            }
            $content = '<p>' . $question . '</p><p>' . $answer . '</p><p>' . $visual . '</p>';
            // Fix relative links without breaking // scheme used by YouTube stuff
            $content = preg_replace('#href="\/(?!\/)#', 'href="http://ask.fm/',$content);
            $item['content'] = $content;
            $this->items[] = $item;
        }
    }

    public function getName(){
        return empty($this->request) ? $this->name : $this->request;
    }

    public function getURI(){
        return empty($this->request) ? $this->uri : 'http://ask.fm/'.urlencode($this->request);
    }

    public function getCacheDuration(){
        return 300; // 5 minutes
    }

}
