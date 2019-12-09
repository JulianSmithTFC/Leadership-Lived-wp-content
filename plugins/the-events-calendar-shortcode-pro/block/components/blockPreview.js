/* global ecsVars */
const { Component, Fragment } = wp.element;
const { ServerSideRender } = wp.components;

class BlockPreviewPro extends Component {
	/**
	* @return {ReactElement} The block preview (Pro)
	*/
	render() {
		const { attributes } = this.props;
		const serverRender = [ 'columns', 'calendar' ].indexOf( attributes.design ) < 0;

		return (
			<Fragment>
				{ serverRender ? <ServerSideRender
					block={ 'events-calendar-shortcode/block' }
					attributes={ attributes }
				/> : ( <div>
					<p>{ `Data displayed in the ${ attributes.design } design preview is for demonstration purposes only.` }</p>
					<img
						alt={ attributes.design }
						src={ `${ ecsVars.imagesUrl }${ attributes.design }.png` }
					/>
				</div> ) }
			</Fragment>
		);
	}
}

export default BlockPreviewPro;
