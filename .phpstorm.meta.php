<?php

namespace PHPSTORM_META {

	override( \Starter_Plugin\Plugin::get(), map( [
		'lifecycle'          => \Starter_Plugin\Lifecycle::class,
		'background_updater' => \Starter_Plugin\Utilities\Background_Updater::class,
		'settings'           => \Starter_Plugin\Settings::class,
	] ) );
}
