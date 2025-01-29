// used in configure-email.vue and configure-print.vue to handle function related to the track_and_tag_events attribute of a Communication object

export default {
    /**
     * Translate the track_and_tag_events attribute to the UI
     */
    setActionTags() {
        var selected_tags =  this.communication.track_and_tag_events

        if (!selected_tags) selected_tags = [];
        for (let action_tag of this.action_tags) {
            action_tag.selected = null
            if (action_tag.name in selected_tags) {
                action_tag.value = selected_tags[action_tag.name] != null
                action_tag.selected = selected_tags[action_tag.name];
            }
        }
    },
    /**
     * Update the track_and_tag_events from UI (prepare for AJAX PUT call)
     */
    updateTrackAndTagEvents() {
        var selected_tags = this.action_tags.reduce(function(map, tag) {
            if (tag.value && tag.selected) map[tag.name] = tag.selected
            // else delete map[tag.name]
            else map[tag.name] = null
            
            return map
        }, this.communication.track_and_tag_events)
        
        this.communication.track_and_tag_events = selected_tags
    }
}
