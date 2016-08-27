<?php
class BandcampBridge extends BridgeAbstract{

    public $maintainer = "sebsauvage";
    public $name = "Bandcamp Tag";
    public $uri = "http://bandcamp.com/";
    public $description = "New bandcamp release by tag";
    public $parameters = array( array(
        'tag'=>array(
            'name'=>'tag',
            'type'=>'text'
        )
    ));

    public function collectData(){
        $param=$this->parameters[$this->queriedContext];
        $html = '';
        if (isset($param['tag']['value'])) {
            $html = $this->getSimpleHTMLDOM('http://bandcamp.com/tag/'.urlencode($param['tag']['value']).'?sort_field=date') or $this->returnServerError('No results for this query.');
        }
        else {
            $this->returnClientError('You must specify tag (/tag/...)');
        }

        foreach($html->find('li.item') as $release) {
            $script = $release->find('div.art', 0)->getAttribute('onclick');
            $uri = ltrim($script, "return 'url(");
            $uri = rtrim($uri, "')");

            $item = array();
            $item['author'] = $release->find('div.itemsubtext',0)->plaintext . ' - ' . $release->find('div.itemtext',0)->plaintext;
            $item['title'] = $release->find('div.itemsubtext',0)->plaintext . ' - ' . $release->find('div.itemtext',0)->plaintext;
            $item['content'] = '<img src="' . $uri . '"/><br/>' . $release->find('div.itemsubtext',0)->plaintext . ' - ' . $release->find('div.itemtext',0)->plaintext;
            $item['id'] = $release->find('a',0)->getAttribute('href');
            $item['uri'] = $release->find('a',0)->getAttribute('href');
            $this->items[] = $item;
        }
    }

    public function getName(){
        $param=$this->parameters[$this->queriedContext];

        return (!empty($param['tag']['value']) ? $param['tag']['value'] .' - ' : '') .'Bandcamp Tag';
    }

    public function getCacheDuration(){
        return 600; // 10 minutes
    }
}
