<?php

/*
 * Render fields in front end and admin
 * ------------------------------------
 */

namespace Formation\Common\Field;

/*
 * Imports
 * -------
 */

use function Formation\additional_script_data;
use \Formation\Formation as FRM;
use Formation\Common\Field\File_Upload;

class Field {

   /*
    * Variables
    * ---------
    *
    * Default arguments to render fields.
    *
    * @var array $default 
    */

    private static $default = [
        'render' => [
            'name' => '',
            'fields' => [],
            'index' => 0,
            'data' => '',
            'multi' => false,
            'copy' => false,
            'hidden' => false
        ],
        'field' => [
            'name' => false,
            'type' => 'text',
            'label' => false,
            'label_hidden' => false,
            'label_class' => '',
            'label_above' => true,
            'attr' => [],
            'field_class' => '',
            'class' => '',
            'placeholder' => '',
            'options' => [],
            'hidden' => false,
            'before' => '',
            'after' => '',
            'value' => '',
            /* file */
            'file_type' => 'file', // or image
            'accept' => '',
            /* richtext */
            'rows' => 4,
            'quicktags' => false,
            'wpautop' => false,
            'p_tags' => true,
            'toolbar' => 'bold,italic,separator,bullist,numlist,blockquote,separator,link'
        ]
    ];

   /*
    * Multi buttons markup.
    *
    * @var array $multi_buttons 
    */

    public static $multi_buttons = [
        'add' => 
            '<button type="button" class="o-multi__button --add" data-type="add" onclick="multi( this )">' .
                '<span class="u-visually-hidden">Add Input</span>' .
                '<span class="dashicons dashicons-plus o-multi__icon"></span>' .
            '</button>',
        'remove' => 
            '<button type="button" class="o-multi__button --remove" data-type="remove" onclick="multi( this )">' .
                '<span class="u-visually-hidden">Remove Input</span>' .
                '<span class="dashicons dashicons-minus o-multi__icon"></span>' .
            '</button>'
    ];

   /*
    * Multi field copies to pass to front end.
    *
    * @var array $localize_data
    */

    public static $localize_data = [
        'multi' => [],
        'files' => []
    ];

   /*
    * Helper methods
    * --------------
    *
    * Get base name without keys or indexes.
    *
    * @param string $name 
    * @return string base name
    */

    public static function get_top_level_name( $name ) {
        if( strpos( $name, '[%i]' ) !== false )
            $name = explode( '[', $name )[0];

        if( strpos( $name, '[]' ) !== false )
            $name = explode( '[]', $name )[0];

        if( strpos( $name, '[' ) !== false )
            $name = explode( '[', $name )[0];

        return $name;
    }

   /*
    * Replace %i placeholder with actual index.
    *
    * @param string $name
    * @param int $index 
    * @return string indexed name
    */

    public static function index_name( $name, $index ) {
        return str_replace( 
            array( '%i' ),
            array( $index ),
            $name 
        );
    }

   /*
    * Remove brackets from name to create id ( tinymce doesn't allow brackets in id ).
    *
    * @param string $id
    * @return string
    */

    public static function format_id( $id ) {
        $id = str_replace( 
            array( '[', ']' ),
            array( '_' ),
            $id 
        );

        return rtrim( $id, '_' );
    }

   /*
    * Get value in data array from name ( lorem[%i][ipsum] )
    *
    * @param array $array
    * @param string $key 
    * @return string/array value of $key in $array
    */

    public static function get_array_value( $array, $key ) {
        if( !$key ) return false;

        $key = str_replace( 
            array( '[]', '[', ']' ), 
            array( '', '.', '', ), 
            $key 
        );

        $key = explode( '.', $key );

        $value = $array;

        foreach( $key as $k ) {
            if( isset( $value[$k] ) ) {
                $value = $value[$k];
            } else {
                $value = false;
                break;
            }
        }

        return $value;
    }

   /*
    * Recursively filter out required empty multi fields. 
    *
    * @param array $array Contains multi fields value.
    * @param array $required Contains required keys.
    */

    public static function filter_multi_fields( &$array, $required = [] ) {
        if( !is_array( $array ) )
            return;

        foreach( $array as $k => &$v ) {
            if( is_array( $v ) ) {
                $unset = false;

                // check if values required and if empty remove parent
                foreach( $v as $q => $r ) {
                    if( in_array( $q, $required ) && !$r )
                        $unset = true;
                }

                if( $unset )
                    unset( $array[$k] ); 

                self::filter_multi_fields( $v );

                continue;
            }
        }
    }

   /*
    * Render methods
    * --------------
    *
    * Output sections of fields.
    *
    * @param array $args @see self::$default['render']
    * @return string of markup.
    */

    public static function render( $args = [], &$output ) {
        $args = array_replace_recursive( self::$default['render'], $args );
        extract( $args );

        $count = 1;
        $top_level_name = '';

        // if single field passed in
        if( !$fields )
            $fields = [$args];

        if( $name && $multi ) {
            $name = FRM::get_namespaced_str( $name );

            // get top level name in case an array
            $top_level_name = Field::get_top_level_name( $name );

            if( is_array( $data ) && isset( $data[$top_level_name][0] ) )
                $count = count( $data[$top_level_name] );
        }

        if( is_admin() ) {
            if( $top_level_name ) {
                $hide = $hidden ? " style='display: none;'" : '';
                $output .= "<div class='o-field-section o-field-section--$top_level_name'$hide>";
            }
        } else {
            $output .= '<div class="o-field-group l-flex --align-center --wrap">';
        }    

        if( isset( $args['label'] ) && !isset( $args['label_hidden'] ) )
            $output .= '<div class="o-field-group__label">' . $args['label'] . '</div>';

        if( $multi ) 
            $output .= '<div class="o-multi">';
        
        $copy_output = '';

        for( $i = 0; $i < $count; $i++ ) {
            if( $multi ) {
                $mi_start = 
                    '<div class="o-multi__item" data-name="' . $top_level_name . '">' .
                        '<div class="o-multi__fields l-flex --wrap">';

                if( $i === 0 ) 
                    $copy_output .= $mi_start;

                $output .= $mi_start;
            }

            foreach( $fields as $f ) {
                if( isset( $f['fields'] ) ) {
                    if( $f['fields'] ) {
                        self::render( $f, $output );
                        continue;
                    }
                }

                self::render_field( 
                    $f, 
                    $output,
                    $i,
                    $data,
                    false, // copy
                    $multi
                );

                if( $multi && $i === 0 ) {
                    self::render_field( 
                        $f, 
                        $copy_output,
                        $i,
                        $data,
                        true, // copy
                        $multi
                    );
                }
            }

            if( $multi ) {
                $mi_end =  
                    '</div>' .
                    '<div class="o-multi__buttons l-flex">' . 
                        self::$multi_buttons['add'];

                $output .= $mi_end;

                if( $i > 0 )
                    $output .= self::$multi_buttons['remove'];

                if( $i === 0 )
                    $copy_output .= $mi_end . self::$multi_buttons['remove'];

                $mi_end =
                        '</div>' . 
                    '</div>';

                $output .= $mi_end;

                if( $i === 0 )
                    $copy_output .= $mi_end;
            }
        }

        if( $multi ) {
            $output .= '</div>';

            if( $copy_output )
                self::$localize_data['multi'][$top_level_name] = $copy_output;
        }

        if( is_admin() ) {
            $output .= '</div>';
        } else {
            $output .= '</div>';
        }    

        additional_script_data( FRM::$namespace, self::$localize_data, true );

        return $output;
    }

   /*
    * Output fields.
    *
    * @param array $args @see self::$default['render'] and self::$default['field']
    * @param string $output Append to it as loop in render method.
    */

    public static function render_field( $args = [], &$output, $index = 0, $data = '', $copy = false, $multi = false ) {
        $args = array_replace_recursive( self::$default['field'], $args );
        extract( $args );

        $name = FRM::get_namespaced_str( $name );

        if( $multi ) {
            $attr['data-name'] = $name;
            $attr['data-id'] = self::format_id( $name );
        }

        $name = $multi && !$copy ? self::index_name( $name, $index ) : $name;

        $id = self::format_id( $name );
        $classes = 'o-field__' . $type . ' js-input' . ( $class ? " $class" : '' );
        $placeholder = $placeholder ? 'placeholder="' . $placeholder . '"' : '';
        $checkbox_radio = $type === 'checkbox' || $type === 'radio';

        if( !is_admin() ) {
            $classes .= ( FRM::$classes['input'] ? ' ' . FRM::$classes['input'] : '' );
            $field_class .= ( FRM::$classes['field'] ? ' ' . FRM::$classes['field'] : '' );
            $label_class .= ( FRM::$classes['label'] ? ' ' . FRM::$classes['label'] : '' );
        } 
       
        $req = '';

        if( is_array( $data ) ) {
            $data_value = self::get_array_value( $data, $name );
        } else {
            $data_value = $data;
        }

        $val = !$value ? $data_value : $value;

        if( $attr ) {
            $attr_formatted = [];

            foreach( $attr as $a => $v ) {
                $attr_formatted[] = $a . '="' . $v . '"';

                if( $a == 'aria-required' && $v == 'true' )
                   $req = ' --req'; 
            }

            $attr = implode( ' ', $attr_formatted );
        } else {
            $attr = '';
        }

        $hidden = $hidden === '100' || ( $hidden && !$val ) ? ' style="display: none;"' : '';

        $output .= 
            "<div class='o-field" . ( $field_class ? " $field_class" : '' ) . " --$type'$hidden>";

        if( $label && !$label_hidden ) {
            $label_class = "o-field__label" . ( $label_class ? " $label_class" : '' ) . $req;

            $output .= '<label>';

            $label = "<div class='$label_class'>$label</div>";

            if( $label_above )
                $output .= $label;
        }

        $output .= $before;

        switch( $type ) {
            case 'text':
            case 'email':
            case 'checkbox':
            case 'radio':
            case 'number':
                if( is_admin() ) {
                    if( $type !== 'checkbox' && $type !== 'number' )
                        $classes .= ' regular-text';

                    if( $type === 'number' )
                        $classes .= ' small-text';
                }

                if( $type === 'text' || $type === 'email' )
                    $classes .= ' o-field__input';
                
                $checked = '';
                $v = $val;

                if( $checkbox_radio ) {
                    if( $data_value == $value ) 
                        $checked = 'checked';

                    $v = $value;
                }

                $output .= sprintf( 
                    '<input name="%1$s" id="%8$s" type="%2$s" value="%4$s" class="%5$s" %6$s %7$s %3$s>', 
                    $name, 
                    $type, 
                    $placeholder, 
                    $v,
                    $classes,
                    $checked,
                    $attr,
                    $id
                );

                if( $checkbox_radio )
                    $output .= '<span class="o-field__control"></span>';

                break;
            case 'file':
                $file_type = strtolower( $file_type );
                $file_type_cap = ucwords( $file_type );
                $file_exists = $val ? true : false;

                $url = '';
                $title = basename( parse_url( $val, PHP_URL_PATH ) );

                self::$localize_data['files'][] = [
                    'id' => $id,
                    'file_type' => $file_type
                ];

                if( $file_type == 'image' )
                    $url = $val;

                $output .= 
                    "<div class='o-file'>" .
                        "<div class='o-file__exists' style='display: " . ( $file_exists ? "block" : "none" ) . "'>" .
                            "<div class='l-flex --align-center --wrap'>" .
                                "<div class='o-file__asset'>" .
                                    "<img class='o-file__image' src='$url' alt='Image preview'>" .
                                "</div>" . 
                                "<div class='o-file__icon'></div>" .
                                "<span class='o-file__name'>$title</span>" .
                                "<button type='button' class='o-file__remove'>" .
                                    "<span class='dashicons dashicons-no-alt'></span>" .
                                    "<span class='u-visually-hidden'>Remove $file_type_cap</span>" .
                                "</button>" .
                            "</div>" .
                        "</div>" .
                        "<div class='o-file__no' style='display: " . ( $file_exists ? "none" : "block" ) . "'>" .
                            "<p style='margin: 0'>" .
                                "<label class='o-file__select u-position-relative" . ( is_admin() ? ' button add-media' : '' ) . "'>" .
                                    "<input type='file' aria-label='Select $file_type_cap' class='u-hide-input' accept='$accept'>" .
                                    "<span>Select $file_type_cap</span>" .
                                    "<span class='o-loader'><span class='spinner is-active'></span></span>" .
                                "</label>" .
                            "</p>" .
                        "</div>" . 
                        "<input name='$name' id='$id' type='hidden' value='$val'>" .
                    "</div>";

                break;
            case 'textarea':
                $output .= sprintf(
                    '<textarea name="%1$s" id="%5$s" class="%2$s" %4$s>%3$s</textarea>', 
                    $name, 
                    $classes,
                    $val,
                    $attr,
                    $id
                );

                break;
            case 'richtext':
                ob_start();

                if( !$p_tags ) {
                    add_filter( 'tiny_mce_before_init', function( $init_settings ) {
                        $init_settings['forced_root_block'] = false;
                        return $init_settings;
                    } );
                }

                wp_editor( html_entity_decode( $val ), $id, [
                    'media_buttons' => false,
                    'wpautop' => $wpautop,
                    'textarea_name' => $name,
                    'textarea_rows' => $rows,
                    'editor_class' => $classes,
                    'tinymce' => array(
                        'toolbar1' => $toolbar,
                        'toolbar2' => '',
                        'toolbar3' => '',
                        'toolbar4' => ''
                    )
                ] );

                $output .= ob_get_clean();

                break;
            case 'select': 
                if( $options ) {
                    $opt = '';

                    foreach( $options as $key => $label ) {
                        $selected = $key == $val ? 'selected' : '';

                        $opt .= sprintf( 
                            '<option value="%s" %s>%s</option>', 
                            $key, 
                            $selected, 
                            $label 
                        );
                    }

                    $output .= sprintf(
                        '<select name="%1$s" id="%5$s" class="%3$s" %4$s>%2$s</select>', 
                        $name, 
                        $opt,
                        $classes,
                        $attr,
                        $id
                    );
                }

                break;
        }

        $output .= $after;

        if( $label && !$label_hidden ) {
            if( !$label_above )
                $output .= $label;

            $output .= '</label>';
        }

        $output .= '</div>';
    }

   /*
    * Output listbox ( substitute for select ).
    *
    * @param array $args {
    *       @type string $options Accepts array {
    *           @type string $value Accepts string.
    *           @type string $label Accepts string.
    *           @type string $selected Accepts boolean.
    *       }
    *       @type string $id Accepts string.
    *       @type string $list_class Accepts string.
    *
    * }
    * @return string of markup
    */

    public static function render_listbox( $args = [] ) {
        $options = $args['options'] ?? [];
        $id = $args['id'] ?? FRM::$namespace . '_' . uniqid();

        $list_id = $id . '_list';
        $list_class = $args['list_class'] ?? '';

        // classes for list
        $list_classes = 'o-listbox__list js-input';

        if( $list_class )
            $list_classes .= " $list_class";

        if( count( $options ) == 0 )
            return '';

        $selected_index_label = '';
        $selected_index_id = '';
        $selected_index = 0;
        $options_output = '';
        $caret = '';

        foreach( $options as $index => $o ) {
            $v = $o['value'];
            $l = $o['label'];
            $s = $o['selected'] ?? false;

            $o_id = $id . '_' . $v;
            $selected = '';

            if( $s ) {
                $selected_index = $index;
                $selected_index_id = $o_id;
                $selected_index_label = $l;
                $selected = ' aria-selected="true"';
            }

            $options_output .= "<li class='o-listbox__item' id='$o_id' data-value='$v' role='option'$selected>$l</li>";
        }

        if( isset( FRM::$sprites['caret'] ) ) {
            $caret_meta = FRM::$sprites['caret'];
            $caret_w = $caret_meta['w'];
            $caret_h = $caret_meta['h'];

            $caret = 
                "<svg class='o-listbox__caret u-flex-shrink-0' width='$caret_w' height='$caret_h' viewBox='0 0 $caret_w $caret_h'>" .
                    "<use xlink:href='#sprite-caret' />" .
                "</svg>";
        }

        return 
            '<div class="o-listbox">' .
                "<button class='o-listbox__btn l-flex --align-center --justify' type='button' aria-haspopup='listbox' aria-labelledby='$id' id='$id'><div class='o-listbox__text u-flex-shrink-0'>$selected_index_label</div>$caret</button>" . 
                '<div class="o-listbox__container">' .
                    "<ul class='$list_classes' id='$list_id' tabindex='-1' role='listbox' aria-labelledby='$id' aria-activedescendant='$selected_index_id'>" . 
                        $options_output . 
                    '</ul>' .
                '</div>' .
            '</div>';
    }

   /*
    * Scripts and styles to enqueue.
    */

    public static function scripts() {
        $path = FRM::$src_path . 'common/assets/public/';

        wp_enqueue_style( 
            FRM::$namespace . '-field-styles', 
            get_template_directory_uri() . $path . 'css/field.css' 
        );

        $handle = FRM::$namespace . '-field-script';
        $nonce_name = FRM::$namespace . '_upload_file_nonce';

        wp_register_script(
            $handle, 
            get_template_directory_uri() . $path . 'js/field.js',
            [],
            NULL,
            true
        );

        wp_localize_script( $handle, FRM::$namespace, [
            $nonce_name => wp_create_nonce( $nonce_name )
        ] );

        wp_enqueue_script( $handle );
    }

   /*
    * Ajax action and callback to upload files.
    */

    public static function file_upload_action() {
        add_action( 'wp_ajax_upload_file', function() {
            try {
                // check upload nonce
                if( !check_ajax_referer( FRM::$namespace . '_upload_file_nonce', FRM::$namespace . '_upload_file_nonce', false ) )
                    throw new \Exception( 'Not allowed' );

                new File_Upload( [
                    'uploads_dir' => FRM::$uploads_dir,
                    'uploads_url' => FRM::$uploads_url,
                    'success' => function( $data ) {
                        echo json_encode( $data );
                    },
                    'error' => function( $err ) {
                        throw new \Exception( $err );
                    }
                ] );

                exit;
            } catch( \Exception $e ) {
                header( http_response_code( 500 ) );
                echo $e->getMessage();
                exit;
            }
        } );
    }

} // end Field
