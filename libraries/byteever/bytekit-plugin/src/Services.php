<?php

namespace WooCommerceVariationImages\ByteKit;

defined( 'ABSPATH' ) || exit();

/**
 * Class Container.
 *
 *  A simple Service Container used to collect and organize Services used by the application and its modules.
 *
 * @since   1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package ByteKit/Plugin
 * @license GPL-3.0+
 */
class Services implements \ArrayAccess {
	/**
	 * List of services.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $services = array();

	/**
	 * Service aliases.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Magic method to check if a property is set.
	 *
	 * @param string $key The data key.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->services[ $key ] ) || isset( $this->aliases[ $key ] );
	}

	/**
	 * Magic method to get the plugin data.
	 *
	 * @param string $key The data key.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function &__get( $key ) {
		$value = null;
		$key   = $this->get_alias_name( $key );
		if ( isset( $this->services[ $key ] ) ) {
			$value = $this->services[ $key ];
		}

		return $value;
	}

	/**
	 * Magic method to set the plugin data.
	 *
	 * @param string $key The data key.
	 * @param mixed  $value The data value.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function __set( $key, $value ) {
		return $this->add( $key, $value );
	}

	/**
	 * Add a service to the container.
	 *
	 * @param mixed $name The service name, or an array of services.
	 * @param mixed $service The service to add.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function add( $name, $service = null ) {
		// If the name is an array, add each service.
		if ( is_array( $name ) ) {
			foreach ( $name as $key => $value ) {
				$key = is_numeric( $key ) ? $value : $key;
				$this->add( $key, $value );
			}

			return $this;
		}
		$service    = empty( $service ) ? $name : $service;
		$name       = is_object( $name ) ? get_class( $name ) : $name;
		$class_name = is_object( $service ) ? get_class( $service ) : $service;

		if ( $name !== $class_name && ! isset( $this->aliases[ $name ] ) ) {
			$this->add_alias( $name, $class_name );
		}

		// Pull the service from the container if it exists.
		if ( isset( $this->services[ $class_name ] ) ) {
			return $this->services[ $class_name ];
		}

		if ( is_string( $service ) && class_exists( $service ) ) {
			$reflection = new \ReflectionClass( $service );
			if ( $reflection->isInstantiable() ) {
				$service = $reflection->newInstance();
			}
		}

		$this->services[ $class_name ] = $service;

		return $this->get( $name );
	}

	/**
	 * Get a service from the container by name.
	 *
	 * @param string $name The service name.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function get( $name ) {
		if ( $this->__isset( $name ) ) {
			return $this->__get( $name );
		}

		return null;
	}

	/**
	 * Add an alias to the container.
	 *
	 * @param string $alias The alias.
	 * @param string $name The service name.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_alias( $alias, $name ) {
		$this->aliases[ $alias ] = $name;
	}

	/**
	 * Get the name of the alias.
	 *
	 * @param string $alias The alias.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_alias_name( $alias ) {
		if ( array_key_exists( $alias, $this->aliases ) ) {
			return $this->aliases[ $alias ];
		}

		return $alias;
	}

	/**
	 * Whether an offset exists.
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return $this->__isset( $offset );
	}

	/**
	 * Offset to retrieve.
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->__get( $offset );
	}

	/**
	 * Offset to set.
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->__set( $offset, $value );
	}

	/**
	 * Offset to unset.
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		if ( isset( $this->services[ $offset ] ) ) {
			unset( $this->services[ $offset ] );
		}
	}
}
