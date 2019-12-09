import DesignSetting from './components/designSetting';
import DescriptionSetting from './components/descriptionSetting';
import BlockPreviewPro from './components/blockPreview';

const { addFilter } = wp.hooks;
const { __ } = wp.i18n;

// Add the settings components for the Pro version.
addFilter( 'ecs.settingsConfig', 'events-calendar-shortcode/block', ( settingsConfig ) => {
	const proConfig = {
        description: {
            component: DescriptionSetting,
            label: __( 'Description', 'the-events-calendar-shortcode' ),
            removable: true, // Whether this option can be removed from the settings list
            delAttributes: [
                'description',
                'raw_description',
                'excerpt',
            ], // If the setting needs more than one attribute cleared on remove/switching.
        },
		...settingsConfig,
        design: {
            component: DesignSetting,
            label: __( 'Design', 'the-events-calendar-shortcode' ),
            removable: false,
        },
	};

	return proConfig;
} );

// Replace the BlockPreview component with the Pro version.
addFilter( 'ecs.blockPreview', 'events-calendar-shortcode/block', () => {
	return BlockPreviewPro;
} );
