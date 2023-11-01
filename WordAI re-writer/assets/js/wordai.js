/*

jQuery(document).ready(function($) {
    // Add the WordAi button and dropdown/modal to the post editor
    // This is a basic example. You can style and position it as needed.
    var wordaiButton = '<button id="wordai-btn">WordAi</button>';
    var wordaiOptions = `
        <div id="wordai-options" style="display:none;">
            <select id="wordai-mode">
                <option value="rewrite">Rewrite</option>
                <option value="avoid">Avoid AI Detection</option>
            </select>
            <select id="wordai-uniqueness">
                <option value="1">Conservative</option>
                <option value="2">Regular</option>
                <option value="3">Adventurous</option>
            </select>
            <input type="text" id="wordai-protected-words" placeholder="Protected Words (comma separated)">
            <input type="text" id="wordai-custom-synonyms" placeholder="Custom Synonyms (comma separated)">
            <button id="wordai-process">Process with WordAi</button>
        </div>
    `;

    $('#postdivrich').after(wordaiButton + wordaiOptions);

    $('#wordai-btn').click(function() {
        $('#wordai-options').toggle();
    });

    $('#wordai-process').click(function() {
        var data = {
            action: 'process_wordai',
            nonce: wordai_vars.nonce,
            post_id: $('#post_ID').val(),
            mode: $('#wordai-mode').val(),
            uniqueness: $('#wordai-uniqueness').val(),
            protected_words: $('#wordai-protected-words').val(),
            custom_synonyms: $('#wordai-custom-synonyms').val()
        };

        $.post(wordai_vars.ajax_url, data, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });
});

*/

jQuery(document).ready(function($) {
    console.log("WordAi script loaded!"); // Debugging log

    // Function to update uniqueness dropdown based on mode
    function updateUniquenessOptions() {
        console.log("Updating uniqueness options..."); // Debugging log

        var mode = $('#wordai_mode').val();
        var uniquenessDropdown = $('#wordai_uniqueness');
        uniquenessDropdown.empty(); // Clear existing options

        if (mode === 'rewrite') {
            uniquenessDropdown.append('<option value="1">Conservative</option>');
            uniquenessDropdown.append('<option value="2">Regular</option>');
            uniquenessDropdown.append('<option value="3">Adventurous</option>');
        } else if (mode === 'avoid') {
            uniquenessDropdown.append('<option value="change_less">Change Less</option>');
            uniquenessDropdown.append('<option value="change_more">Change More</option>');
        }
    }

    // Initial update
    updateUniquenessOptions();

    // Update on mode change
    $('#wordai_mode').on('change', function() {
        console.log("Mode changed to: " + $(this).val()); // Debugging log
        updateUniquenessOptions();
    });

    $('#wordai_process').on('click', function() {
        console.log("Processing with WordAi..."); // Debugging log

        var data = {
            'action': 'process_wordai',
            'nonce': wordai_vars.nonce,
            'mode': $('#wordai_mode').val(),
            'uniqueness': $('#wordai_uniqueness').val(),
            'post_id': $('#post_ID').val(),
            'protected_words': $('#wordai_protected_words').val(),
            'custom_synonyms': $('#wordai_custom_synonyms').val()
        };

        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });
});
