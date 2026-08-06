import { StrictMode } from 'react'

import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import ContactSlideIn from './ContactSlideIn.jsx'

const BLOCK_SELECTOR = '.wp-block-hommeldorp-contact-form';

domReady(() => {
	const blockMounts = document.querySelectorAll(BLOCK_SELECTOR);

	blockMounts.forEach((mount) => {
		const attributes = JSON.parse(mount.dataset.attributes);
		createRoot(mount).render(
			<StrictMode>
				<ContactSlideIn attributes={attributes} blockMount={mount} />
			</StrictMode>,
		)
	});
});

