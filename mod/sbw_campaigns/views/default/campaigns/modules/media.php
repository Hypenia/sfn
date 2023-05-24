<?php

$entity = elgg_extract('entity', $vars);

$media = '';
$video = elgg_view('output/player', [
	'href' => $entity->video_url,
	'fallback' => false,
		]);
if ($video) {
	$media = elgg_format_element('div', [
		'class' => 'campaigns-cover-video scraper-card-flex',
			], $video);
} else if ($entity->getIconURL('master')) {
	$image = elgg_view('output/img', [
		'src' => $entity->getIconURL('master'),
	]);
	$media = elgg_format_element('div', [
		'class' => 'campaigns-cover-image',
			], $image);
}

echo $media;