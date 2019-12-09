const { Component } = wp.element;
const { __ } = wp.i18n;

/**
* Setting component for description
*/
class DescriptionSetting extends Component {
	/**
	 * Handle description input change
	 *
	 * @param {Object} event input onChange event
	 */
	handleChange = ( event ) => {
		this.props.setAttributes( {
			description: event.target.checked.toString(),
			excerpt: event.target.checked.toString(),
		} );

		if ( ! event.target.checked ) {
			this.props.setAttributes( { raw_description: undefined } );
		}
	}

	/**
	 * Handle raw_description input change
	 *
	 * @param {Object} event input onChange event
	 */
	handleRawChange = ( event ) => {
		this.props.setAttributes( { raw_description: event.target.checked.toString() } );
	}

	/**
	 * @return {ReactElement} Description Setting
	 */
	render() {
		const { attributes } = this.props;
		const description = ( attributes.description === 'true' ) ? true : false;
        const rawDescription = ( attributes.raw_description === 'true' ) ? true : false;

		return (
			<div className={ 'ecs-settings-description' }>
				<div className={ 'ecs-setting-desc' }>
					<input
						id={ 'ecs-setting-desc' }
						type={ 'checkbox' }
						checked={ description }
						onChange={ this.handleChange }
					/>
					<label htmlFor={ 'ecs-setting-desc' }>{ __( 'Show Description', 'the-events-calendar-shortcode' ) }</label>
				</div>

				{ description ? <div className={ 'ecs-setting-raw-desc' }>
					<input
						id={ 'ecs-setting-raw-desc' }
						type={ 'checkbox' }
						checked={ rawDescription }
						onChange={ this.handleRawChange }
					/>
					<label htmlFor={ 'ecs-setting-raw-desc' }>{ __( 'Raw HTML', 'the-events-calendar-shortcode' ) }</label>
				</div> : null }
			</div>
		);
	}
}

export default DescriptionSetting;

