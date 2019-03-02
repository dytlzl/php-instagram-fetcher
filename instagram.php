<?php
require_once dirname(__FILE__) . '/reasonable_parser.php';
class instagram{
    function __construct($id){
        $this->url = "https://www.instagram.com/${id}/";
        $this->posts_data = array();
    }
    private function fetch_json($url){
        $html = file_get_contents($url);
        $html = mb_convert_encoding($html, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $json = parse($html, 'window._sharedData = ', ';</script>');
        $array_json = json_decode($json, true);
        return $array_json;
    }
    private function fetch_post($array){
        $post_data = array(
            'shortcode' => $array['shortcode'],
            'typename' => $array['__typename'],
            'text' => $array['edge_media_to_caption']['edges'][0]['node']['text'],
            'timestamp' => $array['taken_at_timestamp'],
            'date' => date('Y年n月j日 H:i', $array['taken_at_timestamp']),
            'display_url' => $array['display_url']
        );
        $post_data += $this->fetch_media($post_data['typename'], $post_data['shortcode']);
        return $post_data;
    }
    private function fetch_media($typename, $shortcode){
        if($typename == 'GraphSidecar'){
            return $this->fetch_sidecar($shortcode);
        }elseif($typename == 'GraphVideo'){
            return $this->fetch_video($shortcode);
        }else{
            return array();
        }
    }
    private function fetch_sidecar($shortcode){
        $url = "https://www.instagram.com/p/${shortcode}/";
        $array_json = $this->fetch_json($url);
        $sidecar = array();
        $edges = $array_json['entry_data']['PostPage'][0]['graphql']['shortcode_media']['edge_sidecar_to_children']['edges'];
        foreach($edges as $value){
            $sidecar[] = ($value['node']['display_url']);
        }
        return array('sidecar' => $sidecar);
    }
    private function fetch_video($shortcode){
        $url = "https://www.instagram.com/p/${shortcode}/";
        $array_json = $this->fetch_json($url);
        $video = $array_json['entry_data']['PostPage'][0]['graphql']['shortcode_media']['video_url'];
        return array('video' => $video);
    }
    public function get_posts_data(){
        return $this->posts_data;
    }
    public function main(){
        $array_json = $this->fetch_json($this->url);
        $edges = $array_json['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
        foreach($edges as $value){
            $this->posts_data[] = $this->fetch_post($value['node']);
        }
    }
}
