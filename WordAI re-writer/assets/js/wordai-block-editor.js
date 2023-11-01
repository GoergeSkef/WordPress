const { registerPlugin } = wp.plugins;
const { PluginMoreMenuItem } = wp.editPost;
const { dispatch } = wp.data;

registerPlugin('wordai-plugin', {
    render: function() {
        return (
            <PluginMoreMenuItem
                icon="admin-plugins"
                onClick={ () => {
                    // Show the modal when the menu item is clicked
                    dispatch('core/edit-post').openGeneralSidebar('wordai-plugin/wordai-sidebar');
                } }
            >
                WordAi Options
            </PluginMoreMenuItem>
        );
    }
});
