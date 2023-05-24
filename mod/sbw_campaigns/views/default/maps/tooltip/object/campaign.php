<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Donation;
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}
?>
<div class="campaigns-info-window">
	<h3>
		<?=
		elgg_view('output/url', [
			'href' => $entity->getURL(),
			'text' => $entity->getDisplayName(),
		]);
		?>
	</h3>
	<?= elgg_view('campaigns/modules/media', $vars); ?>

	<?php
	$donations = elgg_get_entities([
		'types' => 'object',
		'subtypes' => Donation::SUBTYPE,
		'container_guids' => $entity->guid,
		'limit' => 10,
		'offset_key' => 'donations',
		'batch' => true,
	]);
	?>
	<ul class="elgg-gallery elgg-gallery-gluid elgg-gallery-users">
		<?php
		foreach ($donations as $donation) {
			?>
			<li class="elgg-item">
				<?php
				if ($entity->anonymous) {
					$user = new ElggUser();
					$title = elgg_echo('campaigns:anonymous');
				} else {
					$users = get_user_by_email($entity->email);
					if ($users) {
						$user = array_shift($users);
						$title = $user->getDisplayName();
					} else {
						$user = new ElggUser();
						$title = $entity->name;
					}
				}

				$title .= " [{$donation->getNetAmount()->format()}]";

				echo elgg_view('output/img', [
					'src' => $user->getIconURL('tiny'),
					'alt' => $title,
					'title' => $title,
				]);

				?>
			</li>
			<?php
		}
		?>
	</ul>
</div>