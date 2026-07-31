import { useState, useEffect, useRef } from '@wordpress/element'
import FormPanel from "./FormPanel";

function ContactSlideIn( { attributes, blockMount }) {
  const [isOpen, setIsOpen] = useState(false)
	const panelRef = useRef(null)

	const openPanel = () => setIsOpen(true)
	const closePanel = () => {
		blockMount.focus();
		setIsOpen(false);
	}

	const togglePanel = () => {
		console.log('togglePanel')
		if (isOpen) {
			closePanel()
		} else {
			openPanel()
		}

		return false;
	}

	useEffect(() => {
		// capture the event it only triggers on button itself, not the panel
		blockMount.addEventListener('click', togglePanel, { capture: true })

		return () => {
			blockMount.removeEventListener('click', togglePanel)
		}
	}, [blockMount])

	useEffect(() => {
		if (!isOpen) {
			return;
		}

		const handleKeyDown = (event) => {
			if (event.key === 'Escape') {
				closePanel();
			}
		}

		document.addEventListener('keydown', handleKeyDown);

		if (panelRef.current) {
			panelRef.current.focus();
		}

		return () => {
			document.removeEventListener('keydown', handleKeyDown);
		}
	}, [isOpen])

  return (
	  <>
			<FormPanel isOpen={isOpen} closePanel={closePanel} />
      <span>{ attributes.buttonText }</span>
    </>
  )
}

export default ContactSlideIn
