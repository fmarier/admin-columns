<?php

class AC_Settings_Column_CustomFieldType extends AC_Settings_Column
	implements AC_Settings_FormatValueInterface {

	/**
	 * @var string
	 */
	private $field_type;

	protected function define_options() {
		return array( 'field_type' );
	}

	// TODO: move to Pro

	public function get_dependent_settings() {
		$settings = array();

		switch ( $this->get_field_type() ) {
			case 'date' :
				$settings[] = new AC_Settings_Column_Date( $this->column );
				break;
			case 'image' :
			case 'library_id' :
				$settings[] = new AC_Settings_Column_Image( $this->column );
				break;
			case 'excerpt' :
				$settings[] = new AC_Settings_Column_CharacterLimit( $this->column );
				break;
			case 'title_by_id' :
				$settings[] = new AC_Settings_Column_Post( $this->column );
				break;
			case 'user_by_id' :
				$settings[] = new AC_Settings_Column_User( $this->column );
				break;
			case 'link' :
				$settings[] = new AC_Settings_Column_LinkLabel( $this->column );
				break;
		}

		return $settings;
	}

	private function get_description_object_ids( $input ) {
		$description = sprintf( __( "Uses the id from a %s to display information about it.", 'codepress-admin-columns' ), '<em>' . $input . '</em>' );
		$description .= ' ' . __( "Multiple ids should be separated by a comma.", 'codepress-admin-columns' );

		return $description;

	}

	public function get_description() {
		$description = false;

		switch ( $this->get_field_type() ) {
			case 'title_by_id' :
				$description = $this->get_description_object_ids( __( "Post Type", 'codepress-admin-columns' ) );
				break;
			case 'user_by_id' :
				$description = $this->get_description_object_ids( __( "User", 'codepress-admin-columns' ) );
				break;
		}

		return $description;
	}

	public function create_view() {
		$select = $this->create_element( 'select' );

		$select->set_attribute( 'data-refresh', 'column' )
		       ->set_options( $this->get_field_type_options() );

		$select->set_description( $this->get_description() );

		$tooltip = __( 'This will determine how the value will be displayed.', 'codepress-admin-columns' );

		if ( null !== $this->get_field_type() ) {
			$tooltip .= '<em>' . __( 'Type', 'codepress-admin-columns' ) . ': ' . $this->get_field_type() . '</em>';
		}

		$view = new AC_View( array(
			'label'   => __( 'Field Type', 'codepress-admin-columns' ),
			'tooltip' => $tooltip,
			'setting' => $select,
		) );

		return $view;
	}

	/**
	 * Get possible field types
	 *
	 * @return array
	 */
	private function get_field_type_options() {
		$field_types = array(
			'basic'      => array(
				'checkmark'   => __( 'Checkmark (true/false)', 'codepress-admin-columns' ),
				'color'       => __( 'Color', 'codepress-admin-columns' ),
				'count'       => __( 'Counter', 'codepress-admin-columns' ),
				'date'        => __( 'Date', 'codepress-admin-columns' ),
				'excerpt'     => __( 'Excerpt' ),
				'image'       => __( 'Image', 'codepress-admin-columns' ),
				'library_id'  => __( 'Media Library', 'codepress-admin-columns' ),
				'link'        => __( 'Url', 'codepress-admin-columns' ),
				'array'       => __( 'Multiple Values', 'codepress-admin-columns' ),
				'numeric'     => __( 'Numeric', 'codepress-admin-columns' ),
				'has_content' => __( 'Has Content', 'codepress-admin-columns' ),
			),
			'relational' => array(
				'title_by_id' => __( 'Post', 'codepress-admin-columns' ),
				'user_by_id'  => __( 'User', 'codepress-admin-columns' ),
				'term_by_id'  => __( 'Term', 'codepress-admin-columns' ),
			),
		);

		/**
		 * Filter the available custom field types for the meta (custom field) field
		 *
		 * @since NEWVERSION
		 *
		 * @param array $field_types Available custom field types ([type] => [label])
		 */
		$field_types['custom'] = apply_filters( 'ac/column/custom_field/field_types', array() );

		$groups = array(
			'basic'      => __( 'Basic', 'codepress-admin-columns' ),
			'relational' => __( 'Relational', 'codepress-admin-columns' ),
			'custom'     => __( 'Custom', 'codepress-admin-columns' ),
		);

		asort( $field_types['basic'] );
		asort( $field_types['relational'] );
		asort( $field_types['custom'] );

		// Default option comes first
		$field_types['basic'] = array_merge( array( '' => __( 'Default', 'codepress-admin-columns' ) ), $field_types['basic'] );

		$grouped_options = array();
		foreach ( $field_types as $group => $fields ) {

			if ( ! $fields ) {
				continue;
			}

			$grouped_options[ $group ]['title'] = $groups[ $group ];
			$grouped_options[ $group ]['options'] = $fields;
		}

		return $grouped_options;
	}

	/**
	 * @param AC_ValueFormatter $value_formatter
	 *
	 * @return AC_ValueFormatter|AC_Collection
	 */
	public function format( AC_ValueFormatter $value_formatter ) {
		$value = $value_formatter->value;

		switch ( $this->get_field_type() ) {
			case 'image' :
			case 'library_id' :
			case 'title_by_id' :
			case 'user_by_id' :
				$string = ac_helper()->array->implode_recursive( ', ', $value );
				$value_formatter->value = AC_ValueFormatter::cast_ids( ac_helper()->string->string_to_array_integers( $string ) );

				break;
			case "checkmark" :
				$is_true = ! empty( $value ) && 'false' !== $value && '0' !== $value;
				$value_formatter->value = ac_helper()->icon->yes_or_no( $is_true );

				break;
			case "color" :
				$value_formatter->value = ac_helper()->string->get_empty_char();

				if ( $value && is_scalar( $value ) ) {
					$value_formatter->value = ac_helper()->string->get_color_block( $value );
				}

				break;
			case "count" :
				if ( $this->column instanceof AC_Column_Meta ) {
					$value_formatter->value = $value ? count( $value ) : ac_helper()->string->get_empty_char();
				}

				break;
			case "has_content" :
				$value_formatter->value = ac_helper()->icon->yes_or_no( $value, $value );

				break;
			case "term_by_id" :
				if ( is_array( $value ) && isset( $value['term_id'] ) && isset( $value['taxonomy'] ) ) {
					$value_formatter->value = ac_helper()->taxonomy->display( (array) get_term_by( 'id', $value['term_id'], $value['taxonomy'] ) );
				}

				break;
			default :
				$value_formatter->value = ac_helper()->array->implode_recursive( ', ', $value );
		}

		// TODO: obsolete?
		//if ( is_array( $value ) ) {
		//	$value = ac_helper()->array->implode_recursive( ' ', $value );
		//}

		return $value_formatter;
	}

	/**
	 * @return string
	 */
	public function get_field_type() {
		return $this->field_type;
	}

	/**
	 * @param string $field_type
	 *
	 * @return bool
	 */
	public function set_field_type( $field_type ) {
		if ( empty( $field_type ) ) {
			return false;
		}

		foreach ( $this->get_field_type_options() as $types ) {
			if ( array_key_exists( $field_type, $types['options'] ) ) {
				$this->field_type = $field_type;
			}
		}

		return true;
	}

}