<?php

namespace Proxy\Plugin;

use Proxy\Plugin\AbstractPlugin;
use Proxy\Event\ProxyEvent;
use Proxy\Html;

class XHamsterPlugin extends AbstractPlugin {

	protected $url_pattern = 'xhamster.com';
	
	public function onBeforeRequest(ProxyEvent $event){
		// mobile
		$event['request']->headers->set('user-agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36');
	}
	
	private function find_video($html){
		$file = false;
		
		if(preg_match('/"play":"([^"]+)"/', $html, $matches)){
			$file = rawurldecode($matches[1]);
			$file = str_replace('\\', '', $file);
		}
		
		return $file;
	}
	
	public function onCompleted(ProxyEvent $event){
		$response = $event['response'];
		$content = $response->getContent();
		
		// remove ads
		$content = HTML::remove('.ts', $content);
		
		// is this video page?
		$file = $this->find_video($content);
		if($file){
			$player = vid_player($file, 638, 504);
			$player = str_replace('<video', '<video style="display:block;', $player);
			
			$content = HTML::replace_inner('#video_box', $player, $content);
			
			// remove "show comments" button
			$content = HTML::remove('#commentToggle', $content);
			
			// display all comments by default
			$content = str_replace('<div class="comments_block"', '<div style="display:block;" class="comments_block"', $content);
		}
		
		$content = Html::remove_scripts($content);
		
		$response->setContent($content);
	}
}

?>