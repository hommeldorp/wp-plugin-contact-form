<?php
// This file is generated. Do not modify it manually.
return array(
	'contact-slide-in-trigger' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/contact-slide-in-trigger',
		'version' => '0.1.0',
		'title' => 'Contact Slide In Trigger',
		'category' => 'widgets',
		'description' => 'Link that will display contact form when clicked.',
		'example' => array(
			
		),
		'attributes' => array(
			'buttonText' => array(
				'type' => 'string'
			),
			'contactEmail' => array(
				'type' => 'string'
			)
		),
		'supports' => array(
			'color' => array(
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'__experimentalBorder' => array(
				'width' => true,
				'radius' => true
			),
			'html' => false,
			'typography' => array(
				'fontSize' => true,
				'textAlign' => true,
				'appearance' => true
			)
		),
		'textdomain' => 'contact-slide-in-trigger',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view/index.js'
	)
);
