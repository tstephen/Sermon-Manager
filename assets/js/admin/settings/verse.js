var settings_dropdown = document.getElementById('verse_bible_version'),
    is_spanish = is_spanish_bible_version(settings_dropdown.options[settings_dropdown.selectedIndex].value);

if (is_spanish && !sm_settings_params.is_wp_spanish) {
    settings_dropdown.style = 'text-decoration: line-through';
}

settings_dropdown.addEventListener('change', function () {
    if (!sm_settings_params.is_wp_spanish) {
        var current_spanish = is_spanish_bible_version(settings_dropdown.value);

        settings_dropdown.style = current_spanish ? 'text-decoration: line-through' : '';
        if (settings_dropdown.nextElementSibling !== null) {
            settings_dropdown.nextElementSibling.style = current_spanish ? '' : 'display: none';
        } else if (is_spanish_bible_version(settings_dropdown.value)) {
            var new_desc = document.createElement('span');

            new_desc.className = 'description';
            new_desc.innerHTML = sm_settings_params.bible_spanish_note;

            settings_dropdown.parentNode.appendChild(new_desc);
        }
    }
});

/**
 * Checks if bible version is Spanish
 *
 * @param {string} value Version to check
 *
 * @returns {boolean} True if it is, false otherwise
 */
function is_spanish_bible_version(value) {
    var spanish_versions = ['LBLA95', 'NBLH', 'NVI', 'RVR60', 'RVA'];
    return spanish_versions.indexOf(value) !== -1;
}