<?php
class FlickrTagBridge extends BridgeAbstract{

	public $maintainer = "erwang";
	public $name = "Flickr TagUser";
	public $uri = "http://www.flickr.com/";
	public $description = "Returns the tagged or user images from Flickr";

    public $parameters = array(
        'By keyword' => array(
            'q'=>array('name'=>'keyword')
        ),

        'By username' => array(
            'u'=>array('name'=>'Username')
        ),
    );

    public function collectData(){
        $param=$this->parameters[$this->queriedContext];
        $html = $this->getSimpleHTMLDOM('http://www.flickr.com/search/?q=vendee&s=rec') or $this->returnServerError('Could not request Flickr.');
        if (isset($param['q']['value'])) {   /* keyword search mode */
            $this->request = $param['q']['value'];
            $html = $this->getSimpleHTMLDOM('http://www.flickr.com/search/?q='.urlencode($this->request).'&s=rec') or $this->returnServerError('No results for this query.');
        }
        elseif (isset($param['u']['value'])) {   /* user timeline mode */
            $this->request = $param['u']['value'];
            $html = $this->getSimpleHTMLDOM('http://www.flickr.com/photos/'.urlencode($this->request).'/') or $this->returnServerError('Requested username can\'t be found.');
        }

        else {
            $this->returnClientError('You must specify a keyword or a Flickr username.');
        }

        foreach($html->find('span.photo_container') as $element) {
            $item = array();
            $item['uri'] = 'http://flickr.com'.$element->find('a',0)->href;
            $thumbnailUri = $element->find('img',0)->getAttribute('data-defer-src');
            $item['content'] = '<a href="' . $item['uri'] . '"><img src="' . $thumbnailUri . '" /></a>'; // FIXME: Filter javascript ?
            $item['title'] = $element->find('a',0)->title;
            $this->items[] = $item;
        }
    }

    public function getCacheDuration(){
        return 21600; // 6 hours
    }
}

