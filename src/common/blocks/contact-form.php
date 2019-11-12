<?php

/*
 * Contact form block
 * ------------------
 */

namespace Formation\Common\Blocks;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM; 
use Formation\Common\Field\Field; 
use Formation\Common\Blocks\Blocks; 

class Contact_Form {

   /*
    * Variables
    * ---------
    *
    * Args for contact form and field blocks.
    *
    * @var array $blocks
    * @see class Blocks for args breakdown.
    */

    public static $blocks = [
        'contact-form' => [
            'attr' => [
                'id' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'subject' => ['type' => 'string'],
                'submit_label' => ['type' => 'string']
            ],
            'default' => [
                'id' => '',
                'email' => '',
                'subject' => '',
                'submit_label' => 'Submit'
            ],
            'render' => [__CLASS__, 'render_contact_form'],
            'handle' => 'contact_form',
            'script' => 'contact-form/form.js'
        ], 
        'contact-form-group' => [
            'attr' => [
                'email_label' => ['type' => 'string']
            ],
            'default' => [
                'email_label' => ''
            ],
            'render' => [__CLASS__, 'render_contact_form_group'],
            'handle' => 'contact_form_group',
            'script' => 'contact-form/group.js'
        ], 
        'contact-form-group-top' => [
            'render' => [__CLASS__, 'render_contact_form_group_top'],
            'handle' => 'contact_form_group_top',
            'script' => 'contact-form/group-top.js'
        ], 
        'contact-form-group-bottom' => [
            'render' => [__CLASS__, 'render_contact_form_group_bottom'],
            'handle' => 'contact_form_group_bottom',
            'script' => 'contact-form/group-bottom.js'
        ], 
        'contact-form-field' => [
            'attr' => [
                'type' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'label' => ['type' => 'string'],
                'placeholder' => ['type' => 'string'],
                'required' => ['type' => 'boolean'],
                'attr' => ['type' => 'string'],
                'options' => ['type' => 'string'],
                'width' => ['type' => 'string'],
                'value' => ['type' => 'string'],
                'label_after' => ['type' => 'boolean'],
                'padding_small' => ['type' => 'boolean'],
                'email_label' => ['type' => 'string']
            ],
            'default' => [
                'type' => 'text',
                'name' => '',
                'label' => '',
                'placeholder' => '',
                'required' => false,
                'attr' => '',
                'options' => '',
                'width' => '100',
                'value' => '',
                'label_after' => false,
                'padding_small' => false,
                'email_label' => ''
            ],
            'render' => [__CLASS__, 'render_contact_form_field'],
            'handle' => 'contact_form_field',
            'script' => 'contact-form/field.js'
        ]
    ];

   /*
	* Constructor
	* -----------
	*/

	public function __construct() {
        // add blocks
        add_action( 'init', [$this, 'register_blocks'] );

        // register meta and routes
        add_action( 'rest_api_init', function() {
            register_rest_route(
                FRM::$namespace,
                '/preview-contact-form',
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'preview_contact_form']
                ]
            );
        } );
    }

   /*
    * Pass blocks to Blocks class
    * ---------------------------
    */

    public function register_blocks() {
        foreach( self::$blocks as $name => $b ) {
            $b['frm'] = true;
            Blocks::$blocks[$name] = $b;
        }
    }

   /*
    * Helpers
    * -------
    *
    * Get associative array from string of key value pairs.
    *
    * @param string $str 
    * @param string $order of key value pairs.
    * @return array 
    */

    public static function get_assoc_array_from_str( $str = '', $order = 'key:value', $indexed = false ) {
        if( !$str )
            return [];

        $array = [];

        $a = explode( "\n", $str );

        if( isset( $a[0] ) ) {
            foreach( $a as $b ) {
                $c = explode( ' : ', $b );

                if( isset( $c[0] ) && isset( $c[1] ) ) {
                    $key = $c[0];
                    $value = $c[1];

                    if( $indexed ) {
                        $array[] = [
                            'label' => $key,
                            'value' => $value
                        ];
                    } else {
                        if( $order == 'key:value' ) {
                            $array[$key] = $value;
                        } else {
                            $array[$value] = $key;
                        }
                    }
                }
            }
        }

        return $array;
    }

   /*
    * Render callbacks
    * ----------------
    *
    * Output contact form.
    *
    * @param array $attributes
    * @param string $content
    * @return string of markup
    */

    public static function render_contact_form( $attributes, $content = '' ) {
        $attr = array_replace_recursive( self::$blocks['contact-form']['default'], $attributes );
        extract( $attr );

        if( !$email )
            $email = get_option( 'admin_email', '' );

        if( !$subject )
            $subject = get_bloginfo( 'name' ) . ' Contact Form';

        if( $id ) {
            // make sure $id not greater than 64 characters
            $id = substr( $id, 0, 40 );

            update_option( FRM::$namespace . '_form_' . $id, [
                'email' => $email,
                'subject' => $subject
            ] );
        } 

        $s_error = FRM::$sprites['Error'];
        $s_success = FRM::$sprites['Success'];

        return FRM::render_form( [
            'id' => $id,
            'data_type' => 'contact',
            'fields' => $content,
            'submit_label' => $submit_label
        ] );
    }

    public static function render_contact_form_group( $attributes, $content ) {
        return "<div class='o-field-group l-100'>$content</div>";
    }

    public static function render_contact_form_group_top( $attributes, $content ) {
        return "<div class='o-field-group__top'>$content</div>";
    }

    public static function render_contact_form_group_bottom( $attributes, $content ) {
        return "<div class='o-field-group__bottom l-flex --wrap'>$content</div>";
    }

    public static function render_contact_form_field( $attributes ) {
        $attr = array_replace_recursive( self::$blocks['contact-form-field']['default'], $attributes );
        extract( $attr );

        $output = '';
        $prefix = FRM::$namespace . '_';

        $field_class = "l-$width";

        if( $label_after )
            $field_class .= ' --l-after';

        if( $padding_small )
            $field_class .= ' --p-sm';

        $field = [
            'name' => FRM::$namespace . '_' . $name,
            'label' => $label,
            'type' => $type,
            'placeholder' => $placeholder,
            'field_class' => $field_class,
            'value' => $value
        ];

        if( $type == 'radio' || $type == 'checkbox' )
            $field['class'] = 'u-hide-input';

        $attr_array = self::get_assoc_array_from_str( $attr );

        $order = $type == 'select' ? 'value:key' : 'key:value';
        $indexed = $type == 'select' ? false : true;

        $options_array = self::get_assoc_array_from_str( $options, $order, $indexed );

        if( $required )
            $attr_array['aria-required'] = true;

        if( $type == 'textarea' && !isset( $attr_array['rows'] ) )
            $attr_array['rows'] = 8;

        if( $email_label ) 
            $attr_array['data-email-label'] = $email_label;

        $field['attr'] = $attr_array;
        $field['options'] = $options_array;

        if( $type == 'radio' || $type == 'checkbox' ) {
            $field['class'] = 'u-hide-input';

            if( $options_array ) {
                $f = $field;
                $field = [];

                foreach( $options_array as $i => $opt ) {
                    $args = $f;
                    $args['label'] = $opt['label'];
                    $args['value'] = $opt['value'];

                    $field[] = $args;
                }
            }
        }

        $render_args = [ 
            'fields' => ( isset( $field[0] ) ? $field : [$field] ),
            'no_group' => true
        ];

        if( $value && $options_array )
            $render_args['value'] = $value;

        Field::render( $render_args, $output );

        return $output;
    }

    public function preview_contact_form( $data ) {
        $req = $data->get_param( 'required' );

        if( is_string( $req ) ) 
            $req = $req == 'true' ? true : false;

        $args = [
            'type' => $data->get_param( 'type' ),
            'name' => $data->get_param( 'name' ),
            'label' => $data->get_param( 'label' ),
            'placeholder' => $data->get_param( 'placeholder' ),
            'required' => $req,
            'attr' => $data->get_param( 'attr' ),
            'options' => $data->get_param( 'options' ),
            'width' => $data->get_param( 'width' )
        ];

        return self::render_contact_form_field( $args );
    }

} // end Contact_Form
