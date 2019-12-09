import Select from 'react-select';

const { Component, Fragment } = wp.element;
const { __ } = wp.i18n;

/**
* Setting component for Design (Pro)
*/
class DesignSetting extends Component {
	/**
	* Handle design select box
	*
	* @param {Object} option the selected option
	*/
	handleDesignChange = ( option ) => {
		const { setAttributes, attributes } = this.props;

		// clear button settings if moving to non-button design option
		if ( [ 'calendar', 'standard', 'grouped' ].indexOf( option.value ) > -1 ) {
			setAttributes( { button: 'false' } );
		}

		// set default columns if moving to a column design option
		if ( [ 'columns', 'table' ].indexOf( option.value ) > -1 ) {
			setAttributes( { columns: 2 } );
		}

		// set to defaultview back to month when switching from calendar
		if ( 'calendar' === attributes.design ) {
			setAttributes( { defaultview: 'month' } );
            setAttributes( { limit: undefined } );
        }

		// set design option
		setAttributes( { design: option.value } );
	}

	/**
	* Handle columns input
	*
	* @param {Object} option the selected option
	*/
	handleColumnsChange = ( option ) => {
		this.props.setAttributes( { columns: parseInt( option.value ) } );
	}

	/**
	* Handle calendar select
	*
	* @param {Object} option the selected option
	*/
	handleCalendarChange = ( option ) => {
		this.props.setAttributes( { defaultview: option.value } );
	}

	/**
	* Handle button checkbox input
	*
	* @param {Object} event checkbox input onChange event
	*/
	handleButtonCheckbox = ( event ) => {
		const button = event.target.checked ? '' : 'false';
		this.props.setAttributes( { button: button } );
	}

	/**
	* Handle button text input
	*
	* @param {Object} event text input onChange event
	*/
	handleButtonText = ( event ) => {
		this.props.setAttributes( { button: event.target.value } );
	}

	/**
	 * @return {ReactElement} Design Setting
	 */
	render() {
		const { design, button, columns, defaultview } = this.props.attributes;
		// set up select options
		const designOptions = [
			{ label: __( 'Default', 'the-events-calendar-shortcode' ), value: 'default' },
			{ label: __( 'Compact', 'the-events-calendar-shortcode' ), value: 'compact' },
			{ label: __( 'Calendar', 'the-events-calendar-shortcode' ), value: 'calendar' },
			{ label: __( 'Grouped', 'the-events-calendar-shortcode' ), value: 'grouped' },
			{ label: __( 'Columns', 'the-events-calendar-shortcode' ), value: 'columns' },
			{ label: __( 'Table', 'the-events-calendar-shortcode' ), value: 'table' },
			{ label: __( 'Standard', 'the-events-calendar-shortcode' ), value: 'standard' },
		];

		const calendarOptions = [
			{ label: __( 'Month', 'the-events-calendar-shortcode' ), value: 'month' },
			{ label: __( 'List Month', 'the-events-calendar-shortcode' ), value: 'listMonth' },
			{ label: __( 'Basic Week', 'the-events-calendar-shortcode' ), value: 'basicWeek' },
			{ label: __( 'Agenda Week', 'the-events-calendar-shortcode' ), value: 'agendaWeek' },
		];

		const columnOptions = [ 2, 3, 4, 5, 6 ].map( ( value ) => {
			return { label: value, value: value };
		} );

		// retrieve selected options
		const selectedDesign = designOptions.filter( ( option ) => option.value === design );
		const selectedCalendar = calendarOptions.filter( ( option ) => option.value === defaultview );
		const selectedColumn = columnOptions.filter( ( option ) => option.value === columns );

		// build calendar select
		const calendarInput = 'calendar' === design ?
			<div className={ 'ecs-setting-text-field' }>
				<label
					className={ 'ecs-setting-label' }
					htmlFor={ 'ecs-setting-defaultview' }
				>{ __( 'Default View', 'the-events-calendar-shortcode' ) }</label>
				<Select
					id={ 'ecs-setting-defaultview' }
					className={ 'ecs-select' }
					classNamePrefix={ 'select' }
					options={ calendarOptions }
					value={ selectedCalendar }
					onChange={ this.handleCalendarChange }
				/>
			</div> : null;

		// build columns input
		const columnsInput = [ 'columns', 'table' ].indexOf( design ) > -1 ?
			<div className={ 'ecs-setting-text-field' }>
				<label
					className={ 'ecs-setting-label' }
					htmlFor={ 'ecs-setting-limit' }
				>{ __( 'Columns', 'the-events-calendar-shortcode' ) }</label>
				<Select
					className={ 'ecs-select' }
					classNamePrefix={ 'select' }
					options={ columnOptions }
					value={ selectedColumn }
					onChange={ this.handleColumnsChange }
				/>
			</div> : null;

		// build button checkbox
		const buttonChecked = ( button === 'false' ) ? false : true;
		const buttonInput = [ 'calendar', 'standard', 'grouped' ].indexOf( design ) < 0 ?
			<div className={ 'ecs-button' }>
				<input
					id={ 'ecs-setting-btn-checkbox' }
					type={ 'checkbox' }
					checked={ buttonChecked }
					onChange={ this.handleButtonCheckbox }
				/>
				<label
					className={ 'ecs-text-label' }
					htmlFor={ 'ecs-setting-btn-checkbox' }
				>{ __( 'Show a button?', 'the-events-calendar-shortcode' ) }</label>
			</div> : null;

		// build button text input
		const buttonText = buttonChecked && [ 'calendar', 'standard', 'grouped' ].indexOf( design ) < 0 ?
			<div className={ 'ecs-setting-text-field' }>
				<label
					className={ 'ecs-setting-label' }
					htmlFor={ 'ecs-setting-btn-text' }
				>{ __( 'Button Text', 'the-events-calendar-shortcode' ) }</label>
				<input
					id={ 'ecs-setting-btn-text' }
					type={ 'text' }
					placeholder={ __( 'Button Text', 'the-events-calendar-shortcode' ) }
					disabled={ buttonChecked ? '' : 'disabled' }
					value={ buttonChecked ? button : '' }
					onChange={ this.handleButtonText }
				/>
			</div> : null;

		return (
			<Fragment>
				<Select
					className={ 'ecs-select' }
					classNamePrefix={ 'select' }
					options={ designOptions }
					value={ selectedDesign }
					onChange={ this.handleDesignChange }
				/>
				{ calendarInput }

				<div className={ 'ecs-settings-design-details' }>
					{ columnsInput }
					{ buttonInput }
					{ buttonText }
				</div>
			</Fragment>
		);
	}
}

export default DesignSetting;
