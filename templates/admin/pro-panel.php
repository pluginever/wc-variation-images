<?php
/**
 * Pro upgrade panel.
 *
 * @var string             $title    Panel title.
 * @var array<int, string> $features Feature bullet points.
 * @var string             $url      Upgrade CTA URL.
 * @var string             $label    Upgrade CTA label.
 *
 * @package PluginEver\StarterPlugin
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="b8-card promo-panel">
	<div class="b8-card__header">
		<h2 class="b8-card__title"><?php echo esc_html( $title ); ?></h2>
	</div>
	<div class="b8-card__body">
		<ul>
			<?php foreach ( $features as $feature ) : ?>
				<li>&ndash; <?php echo esc_html( $feature ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="b8-card__footer">
		<a class="button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php echo esc_html( $label ); ?>
		</a>
	</div>
</div>
