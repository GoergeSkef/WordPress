jQuery(document).ready(function($) {
    // Log the AJAX URL and nonce for debugging
    console.log('AJAX URL:', generic_ajax_url, 'Nonce:', generic_nonce);

    // Setting the taxonomy selector based on the passed taxonomy name
    var taxonomy = top_ranking_parameters.name.toLowerCase().replace(/&/g, '').replace(/[\s\W-]+/g, '-');
    console.log('Initial Taxonomy:', taxonomy);

    document.addEventListener('DOMContentLoaded', function() {
        var heroH1 = document.querySelector('h1.gb-headline'); // Adjust the selector as needed
        if (heroH1 && top_ranking_parameters && top_ranking_parameters.name) {
            // Format the new H1 text
            var newH1Text = 'Software Comparison for ' + top_ranking_parameters.name + ' Solutions';
            heroH1.textContent = newH1Text;
        } else {
            console.log('Either hero H1 or taxonomy name not found.');
        }
    });
    
    // Fetch and display software for the initial taxonomy
    fetchSoftware(taxonomy);

    function fetchSoftware(taxonomy) {
        console.log('Fetching software for taxonomy:', taxonomy);
        $.ajax({
            url: generic_ajax_url,
            type: 'POST',
            dataType: 'json', // Expecting JSON response
            data: {
                action: 'fetch_generic_rank_software',
                taxonomy: taxonomy,
                nonce: generic_nonce
            },
            success: function(response) {
                console.log('Successful response:', response);
                if(response && response.length) {
                    var html = '';
                    response.forEach(function(item) {
                        html += '<div class="software-item">';
                        html += '<a href="' + item.link + '" class="software-item-link">';
                        html += '<h3>' + item.title + '</h3>';
                        if (item.rating) {
                            html += '<div class="rating-container">';
                            html += '<div class="rating-label">Overall Rating:</div>';
                            html += '<div class="star-rating" style="--rating: ' + item.rating + '"></div>';
                            html += '<span class="rating-text">' + item.rating + '</span>';
                            html += '</div>'; // End of rating-container
                        } else {
                            html += '<div class="star-rating no-rating"></div>';
                            html += '<span class="rating-text">No rating</span>';
                        }
                        html += '<img src="' + item.image + '" alt="' + item.title + '">';
                        html += '</a>'; // End of link tag
                        html += '</div>';
                    });
                    $('#software-grid').html(html);
                } else {
                    console.log('No data found for the selected taxonomy.');
                    $('#software-grid').html('<p>No software found for this category.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText || 'No responseText', 'Status:', status, 'Error:', error);
            }
        });
    }
});
