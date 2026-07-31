/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, TextControl} from "@wordpress/components";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

const i18nDomain = 'contact-form';
const DEFAULT_BUTTON_TEXT = __('Contact Us', 'contact-form');

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { buttonText } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', i18nDomain)}>
					<TextControl
						label={__('Button Text', i18nDomain)}
						placeholder={DEFAULT_BUTTON_TEXT}
						value={buttonText || ''}
						onChange={(value) => setAttributes({ buttonText: value })}
					/>
					{/*<TextControl*/}
					{/*	label={__('E-mail Address', i18nDomain)}*/}
					{/*	placeholder="info@example.com"*/}
					{/*	value={contactEmail || ''}*/}
					{/*	onChange={(value) => setAttributes({ contactEmail: value })}*/}
					{/*/>*/}
				</PanelBody>
			</InspectorControls>

			<button { ...useBlockProps() }>{ buttonText || DEFAULT_BUTTON_TEXT }</button>
		</>
	);
}
