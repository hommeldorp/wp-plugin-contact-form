import {__} from "@wordpress/i18n";
import {Button} from "@wordpress/components";
import {close} from "@wordpress/icons";
import {useRef, useState} from "@wordpress/element";

function FormPanel({ isOpen, closePanel }) {
	const formRef = useRef(null);
	const [formState, setFormState] = useState({
		isPending: false,
		isSuccess: false,
		error: '',
	})

	const sendMessage = async (e) => {
		e.preventDefault();
		const form = e.target;

		const formData = new FormData(form);

		setFormState({ ...formState, error: '', isSuccess: false, isPending: true });

		const endpoint = '/wp-json/contact-slide-in/v1/message';
		const result = await fetch(endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(Object.fromEntries(formData))
		})

		if (!result?.ok) {
			setFormState({ ...formState, isPending: false, isSuccess: false, error: 'Failed to send message' });
		} else {
			formRef.current.reset();
			setFormState({ ...formState, isPending: false, isSuccess: true });
		}
	}

	return (
		<div className={`contact-slide-in-panel ${isOpen ? 'open' : ''}`} aria-hidden={!isOpen}>
			<header>
				<h3>{ __('Contact us', 'contact-form') }</h3>
				<Button icon={ close } className="wp-element-button wp-block-button__link close-button" onClick={closePanel} title={__('Close', 'contact-form')}></Button>
			</header>

			<form onSubmit={ sendMessage } ref={formRef}>
				<fieldset disabled={formState.isPending}>
					<div className="form-field">
						<label htmlFor="contact-name">{ __('Name', 'contact-form') }</label>
						<input name="name" required={true} minLength={2} placeholder=" " type="text" id="contact-name" />
					</div>

					<div className="form-field">
						<label htmlFor="contact-email">{ __('E-mail', 'contact-form') }</label>
						<input name="email" type="email" id="contact-email" />
					</div>

					<div className="form-field">
						<label htmlFor="contact-message">{ __('Message', 'contact-form') }</label>
						<textarea name="message" required={true} minLength={20} placeholder=" " id="contact-message" rows="10"></textarea>
					</div>

					{ formState.isSuccess && <p className="success-message">{ __('Message sent successfully', 'contact-form') }</p> }
					{ formState.error && <p className="has-color-dark-red">{ formState.error }</p> }

					<button className="wp-element-button wp-block-button__link has-medium-font-size"
					        type="submit"
					>{ formState.isPending ? __('Sending...', 'contact-form') :  __('Send', 'contact-form') }</button>
				</fieldset>
			</form>
		</div>
	)
}

export default FormPanel
