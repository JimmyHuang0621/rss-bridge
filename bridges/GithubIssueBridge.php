<?php
class GithubIssueBridge extends BridgeAbstract{

  public $maintainer = 'Pierre Mazière';
  public $name = 'Github Issue';
  public $uri = '';
  public $description = 'Returns the issues or comments of an issue of a github project';

  public $parameters=array(
    'global'=>array (
      'u'=>array(
        'name'=>'User name',
        'required'=>true
      ),
      'p'=>array(
        'name'=>'Project name',
        'required'=>true
      )
    ),

    'Project Issues'=>array(),
    'Issue comments'=>array(
      'i'=>array(
        'name'=>'Issue number',
        'type'=>'number',
        'required'=>'true'
      )
    )
  );

  public function collectData(){
    $param=$this->parameters[$this->queriedContext];
    $uri = 'https://github.com/'.$param['u']['value'].'/'.$param['p']['value'].'/issues/'.(isset($param['i']['value'])?$param['i']['value']:'');
    $html = $this->getSimpleHTMLDOM($uri)
      or $this->returnServerError('No results for Github Issue '.$param['i']['value'].' in project '.$param['u']['value'].'/'.$param['p']['value']);

    if(isset($param['i']['value'])){
      foreach($html->find('.js-comment-container') as $comment){

        $item = array();
        $item['author']=$comment->find('img',0)->getAttribute('alt');

        $comment=$comment->firstChild()->nextSibling();

        $item['uri']=$uri.'#'.$comment->getAttribute('id');
        $item['title']=trim($comment->firstChild()->plaintext);
        $item['timestamp']=strtotime($comment->find('relative-time',0)->getAttribute('datetime'));
        $item['content']=$comment->find('.comment-body',0)->innertext;

        $this->items[]=$item;
      }
    }else{
      foreach($html->find('.js-active-navigation-container .js-navigation-item') as $issue){
        $item=array();
        $info=$issue->find('.opened-by',0);
        $item['author']=$info->find('a',0)->plaintext;
        $item['timestamp']=strtotime($info->find('relative-time',0)->getAttribute('datetime'));
        $item['title']=$issue->find('.js-navigation-open',0)->plaintext;
        $comments=$issue->firstChild()->firstChild()
          ->nextSibling()->nextSibling()->nextSibling()->plaintext;
        $item['content']='Comments: '.($comments?$comments:'0');
        $item['uri']='https://github.com'.$issue->find('.js-navigation-open',0)->getAttribute('href');
        $this->items[]=$item;
      }
    }
  }

  public function getCacheDuration(){
    return 600; // ten minutes
  }
}
