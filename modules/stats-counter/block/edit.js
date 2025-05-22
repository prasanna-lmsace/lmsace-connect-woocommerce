// This is the edit component for the block.
// It defines how the block appears in the WordPress editor.

// Import WordPress dependencies
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, CheckboxControl } from '@wordpress/components';
import { serverSideRender as ServerSideRender } from '@wordpress/server-side-render'; // Corrected import

// Available statistics options (should match those in PHP and VC element)
const statsOptions = [
    { label: __( 'Total Courses', 'lmsace-connect' ), value: 'total_courses' },
    { label: __( 'Total Users', 'lmsace-connect' ), value: 'total_users' },
    { label: __( 'Active Enrolments', 'lmsace-connect' ), value: 'active_enrolments' },
    { label: __( 'Course Completions', 'lmsace-connect' ), value: 'course_completions' },
    // Add more stats here if they become available
];

/**
 * The edit function describes the structure of your block in the context of the editor.
 * This represents what the editor will render when the block is used.
 *
 * @param {Object} props Props passed to the function.
 * @return {WPElement} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
    const blockProps = useBlockProps();
    const { title, stats_to_show } = attributes;

    const onTitleChange = ( newTitle ) => {
        setAttributes( { title: newTitle } );
    };

    const onStatsToShowChange = ( statValue, checked ) => {
        const newStatsToShow = [ ...stats_to_show ]; // Create a mutable copy
        if ( checked ) {
            if ( !newStatsToShow.includes( statValue ) ) {
                newStatsToShow.push( statValue );
            }
        } else {
            const index = newStatsToShow.indexOf( statValue );
            if ( index > -1 ) {
                newStatsToShow.splice( index, 1 );
            }
        }
        setAttributes( { stats_to_show: newStatsToShow } );
    };

    return (
        <div { ...blockProps }>
            <InspectorControls>
                <PanelBody title={ __( 'Block Settings', 'lmsace-connect' ) }>
                    <TextControl
                        label={ __( 'Title', 'lmsace-connect' ) }
                        value={ title }
                        onChange={ onTitleChange }
                        help={ __( 'Enter an optional title for the stats block.', 'lmsace-connect' ) }
                    />
                    <hr />
                    <p>{ __( 'Statistics to Display:', 'lmsace-connect' ) }</p>
                    { statsOptions.map( ( option ) => (
                        <CheckboxControl
                            key={ option.value }
                            label={ option.label }
                            checked={ stats_to_show.includes( option.value ) }
                            onChange={ ( checked ) => onStatsToShowChange( option.value, checked ) }
                        />
                    ) ) }
                </PanelBody>
            </InspectorControls>
            {/* Server-side render for preview in editor */}
            <ServerSideRender
                block="lmsace-connect/moodle-stats-counter"
                attributes={ attributes }
            />
        </div>
    );
}
