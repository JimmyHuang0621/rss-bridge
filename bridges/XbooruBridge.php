<?php
class XbooruBridge extends BridgeAbstract{

    public $maintainer = "mitsukarenai";
    public $name = "Xbooru";
    public $uri = "http://xbooru.com/";
    public $description = "Returns images from given page";

    public $parameters = array( array(
        'p'=>array(
            'name'=>'page',
            'type'=>'number'
        ),
        't'=>array('name'=>'tags')
    ));

    public function collectData(){
        $param=$this->parameters[$this->queriedContext];
	$page = 0;$tags='';
        if (isset($param['p']['value'])) {
		$page = (int)preg_replace("/[^0-9]/",'', $param['p']['value']);
		$page = $page - 1;
		$page = $page * 50;
        }
        if (isset($param['t']['value'])) {
            $tags = urlencode($param['t']['value']);
        }
        $html = $this->getSimpleHTMLDOM("http://xbooru.com/index.php?page=post&s=list&tags=$tags&pid=$page") or $this->returnServerError('Could not request Xbooru.');


	foreach($html->find('div[class=content] span') as $element) {
		$item = array();
		$item['uri'] = 'http://xbooru.com/'.$element->find('a', 0)->href;
		$item['postid'] = (int)preg_replace("/[^0-9]/",'', $element->getAttribute('id'));
		$item['timestamp'] = time();
		$thumbnailUri = $element->find('img', 0)->src;
		$item['tags'] = $element->find('img', 0)->getAttribute('alt');
		$item['title'] = 'Xbooru | '.$item['postid'];
		$item['content'] = '<a href="' . $item['uri'] . '"><img src="' . $thumbnailUri . '" /></a><br>Tags: '.$item['tags'];
		$this->items[] = $item;
	}
    }

    public function getCacheDuration(){
        return 1800; // 30 minutes
    }
}
