<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */


$buttonText = ! empty( $attributes['buttonText'] )
	? $attributes['buttonText']
	: esc_html__( 'Contact Us', 'contact-slide-in-trigger' );

$dataAttributes = <<<JSON
{
  "buttonText": "$buttonText"
}
JSON;

?>
<button <?php echo get_block_wrapper_attributes(); ?> data-attributes="<?php echo esc_attr( $dataAttributes ); ?>"></button>
