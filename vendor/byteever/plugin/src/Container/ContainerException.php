<?php

namespace PluginEver\VariationImages\B8\Container;

use Exception;
defined('ABSPATH') || exit;
/**
 * Container exception.
 *
 * Raised during binding resolution or autowiring when a type is
 * unregistered or a class cannot be instantiated.
 *
 * @since 1.0.0
 * @package \B8
 */
class ContainerException extends Exception
{
}