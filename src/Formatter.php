<?php
/**
 * Formatting data.
 *
 * @package Viewpoint/ViewpointApi;
 */

namespace Viewpoint\ViewpointApi;

/**
 * Format data.
 */
class Formatter {
	/**
	 * Init formatter.
	 *
	 * @param string             $type Type of data.
	 * @param string|array|false $key  Key to extract.
	 */
	public function __construct( public string $type = 'json', public string|array|false $key = false ) {
	}

	/**
	 * Format data based on expected data type.
	 *
	 * @param string $data Data type.
	 * @return mixed
	 */
	public function format( $data ) {
		switch ( $this->type ) {
			case 'json':
				$value = $this->formatJson( $data );
				break;
			case 'xml':
				$value = $this->formatXml( $data );
				break;
			default:
				$value = $data;
		}
		if ( $this->key ) {
			return self::getNestedKeys( $this->key, $value );
		}
		return $value;
	}

	/**
	 * JSON Formatter.
	 *
	 * @param string $data Data.
	 * @return object
	 */
	public function formatJson( $data ) {
		return json_decode( $data );
	}

	/**
	 * XML Formatter.
	 *
	 * @param string $data Data.
	 * @return SimpleXMLElement
	 */
	public function formatXml( $data ) {
		return simplexml_load_string( $data );
	}

	/**
	 * Parse a field from an object of some kind.
	 *
	 * @param string|array           $field Field key or array of nested field keys. Use `xpath:foo` to extract XPath value.
	 * @param SimpleXML|object|array $data  Data object.
	 * @return mixed
	 */
	public static function getNestedKeys( $field, $data ) {
		// If array, get the first level down.
		if ( is_array( $field ) ) {
			$current_field = array_shift( $field );
		} else {
			$current_field = $field;
		}

		$data = self::getDataFromObject( $current_field, $data );

		// Handle user defined fields, since key AND value are stored as attributes. Thanks CCB API.
		if ( $data && 'user_defined_field' === $current_field ) {
			foreach ( $data as $user_defined_field ) {
				if ( $field[0] === (string) $user_defined_field->label ) {
					$custom_field                                 = new \stdClass();
					$custom_field->{ sanitize_title( $field[0] )} = (string) $user_defined_field->selection;
					$data     = $custom_field;
					$field[0] = sanitize_title( $field[0] );
				}
			}
		}

		// If nested fields, recurse with the next level down data.
		if ( is_array( $field ) && 0 < count( $field ) ) {
			return self::getNestedKeys( $field, $data );
		}

		if ( is_a( $data, 'SimpleXMLElement' ) && 1 >= $data->count() ) {
			return (string) $data;
		}

		return $data;
	}

	/**
	 * Get value from a key in a variety of data types.
	 *
	 * @param string                        $field Field key.
	 * @param array|object|SimpleXMLElement $obj Data.
	 * @return mixed
	 */
	private static function getDataFromObject( $field, $obj ) {
		$value = apply_filters( 'viewpoint_api_get_data_from_object', false, $field, $obj );
		if ( $value ) {
			return $value;
		}
		if ( is_a( $obj, 'SimpleXMLElement' ) ) {
			if ( str_starts_with( $field, 'xpath:' ) ) {
				$value = $obj->xpath( str_replace( 'xpath:', '', $field ) );
			} elseif ( isset( $obj->{$field} ) ) {
				$value = $obj->{$field};
			} elseif ( isset( $obj[ $field ] ) ) {
				$value = $obj[ $field ];
			}
		} elseif ( is_object( $obj ) && isset( $obj->{$field} ) ) {
			$value = $obj->{$field};
		} elseif ( is_array( $obj ) && isset( $obj[ $field ] ) ) {
			$value = $obj[ $field ];
		}
		return self::autoCast( $value );
	}


	/**
	 * Attempt to cast a SimpleXML node to a strong type.
	 * Will recursively process arrays.
	 *
	 * @param    SimpleXML $node A single node on the entitiy.
	 * @return   mixed
	 */
	private static function autoCast( $node ) {
		// If the node has children, convert it to an array
		// and recursively process the child nodes.
		if ( is_a( $node, 'SimpleXMLElement' ) && $node->children()->count() ) {
			$array = [];
			// If the node happens to have an id attribute,
			// transform it into a property so that it's not lost.
			$id = false;
			if ( ! empty( $node->attributes() ) ) {
				foreach ( $node->attributes() as $key => $value ) {
					if ( false !== stristr( $key, 'id' ) ) {
						$id = (int) $value;
						break;
					}
				}
			}
			if ( $id ) {
				$array['id'] = $id;
			}
			foreach ( $node->children() as $child ) {
				$array[ $child->getName() ] = self::autoCast( $child );
			}
			return $array;
		} elseif ( is_object( $node ) ) {
			return (object) $node;
		} elseif ( is_array( $node ) ) {
			return (array) $node;
		} elseif ( 'true' === (string) $node ) {
			return true;
		} elseif ( 'false' === (string) $node ) {
			return false;
		} elseif ( strlen( (int) $node ) === strlen( (string) $node ) ) {
			return (int) $node;
		} else {
			return (string) $node;
		}
	}
}
