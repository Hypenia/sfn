<?php
/**
 * Elgg video view
 *	Licence : GNU2
 *	Copyright : 
 */
elgg_load_library('elgg:videos:embed');

//$mobile = detectmobile();

if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
$mobile = true;
}else{
$mobile = false;
}

$full = elgg_extract('full_view', $vars, FALSE);
$video = elgg_extract('entity', $vars, FALSE);

if (!$video) {
	return;
}

$owner = $video->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$container = $video->getContainerEntity();
$categories = elgg_view('output/categories', $vars);


$video_url = $video->video_url;
$video_id = $video->youtube_id;

$video_url = str_replace("feature=player_embedded&amp;", "", $video_url);
$video_url = str_replace("feature=player_detailpage&amp;", "", $video_url);

$description = elgg_view('output/longtext', array('value' => $video->description, 'class' => 'pbl'));
$owner_link = elgg_view('output/url', array(
	'href' => "videos/owner/{$owner->username}",
	'text' => $owner->name,
));
$author_text = elgg_echo('byline', array($owner_link));

$tags = elgg_view('output/tags', array('tags' => $video->tags));
$date = elgg_view_friendly_time($video->time_created);

$comments_count = $video->countComments();
//only display if there are commments
if ($comments_count != 0) {
	$text = elgg_echo("comments") . " ($comments_count)";
	$comments_link = elgg_view('output/url', array(
		'href' => $video->getURL() . '#comments',
		'text' => $text,
	));
} else {
	$comments_link = '';
}

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'videos',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$subtitle = "$author_text $categories $comments_link";

if (elgg_is_active_plugin('elggx_fivestar')) {
    $subtitle .= elgg_view('elggx_fivestar/voting', array('entity'=> $vars['entity'], 'min' => true));
}


// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}

if ($full && !elgg_in_context('gallery')) {
	$header = elgg_view_title($video->title);

	$params = array(
		'entity' => $video,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'tags' => $tags,
	);
	$params = $params + $vars;
	$list_body = elgg_view('object/elements/summary', $params);
	$video_info = elgg_view_image_block($owner_icon, $list_body);

	echo <<<HTML
$header
$video_info
HTML;
?>

<div class="video elgg-content mts">
	<div style="margin-left:10px;">
	<?php 
        if($mobile) {
        	echo videoembed_create_embed_object($video_url, $video->guid,280);
        }else{
        	echo videoembed_create_embed_object($video_url, $video->guid,700);
        }
	?>
	<br>
	<?php echo '<span itemprop="description">'. $description . '</span>'; ?>
	</div>
</div>
<?php
} elseif (elgg_in_context('gallery')) {
	echo <<<HTML
	<div class="videos-gallery-item">
	<h3>$video->title</h3>
	<p class='subtitle'>$owner_link $date</p>
</div>
HTML;
} else {
	// brief view
	$excerpt = elgg_get_excerpt($video->description);
	if ($excerpt) {
		$excerpt = "$excerpt";
	}
	$video_icon = videoembed_create_embed_object($video_url, $video->guid,450); 

	$content = "$excerpt";

	$params = array(
		'entity' => $video,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'tags' => $tags,
		'content' => $content,
	);

	$params = $params + $vars;
	$list_body = elgg_view('object/elements/summary', $params);

	if ($mobile == true || (elgg_get_context() == 'widgets')){
		$video_icon = videoembed_create_embed_object($video_url, $video->guid,280);
		echo elgg_view_image_block($video_icon,"");
		echo elgg_view_image_block($list_body,"");
	}else { 
		echo elgg_view_image_block($video_icon, $list_body);
	}	
}
