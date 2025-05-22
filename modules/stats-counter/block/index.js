// This is the main entry point for the block's JavaScript.
// It will import the edit and save components and register the block type.

// Import WordPress dependencies
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// Import block components
import Edit from './edit';
// import Save from './save'; // Save component can be omitted for server-side rendered blocks

// Import block styles
import './editor.scss';
import './style.scss';

// Import block.json metadata
import blockMetadata from './block.json';

// Register the block
registerBlockType( blockMetadata.name, {
    /**
     * @see ./edit.js
     */
    edit: Edit,

    /**
     * @see ./save.js
     * Save function is omitted because this is a server-side rendered block.
     * The PHP render_callback in class-lacmod-stats-counter-block.php handles the frontend output.
     */
    // save: Save,
    // Alternatively, for server-side rendered blocks, the save function should return null or not be defined.
    save: () => null,
} );
