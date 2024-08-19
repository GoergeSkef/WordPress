jQuery(document).ready(function($) {
    // Log the AJAX URL and nonce for debugging
    console.log(generic_ajax_url, generic_nonce);
	let selectedFeatures = [];
    let currentPage = 1; // Initial page for feature pagination

    // Define fetchCachedData function
    function fetchCachedData(data, callback) {
        let cacheKey = 'mySiteCache_' + JSON.stringify(data);
        let cachedData = localStorage.getItem(cacheKey);
        if (cachedData) {
            console.log('Using cached data');
            callback(JSON.parse(cachedData));
        } else {
            $.ajax({
                url: generic_ajax_url,
                type: 'POST',
                data: Object.assign({}, data, { nonce: generic_nonce }),
                success: function(response) {
                    console.log('Fetching new data');
                    localStorage.setItem(cacheKey, JSON.stringify(response));
                    callback(response);
                }
            });
        }
    }
    
    // Function to update selected features based on checkboxes
    function updateSelectedFeatures() {
        selectedFeatures = $('input[name="features[]"]:checked').map(function() {
            return $(this).val();
        }).get();
    }
   
	// Delegate checkbox change event to the static parent
    $('#features-list').on('change', 'input[name="features[]"]', function() {
        updateSelectedFeatures();
        fetchFilteredPosts(); // Make sure this is the intended behavior
    });

    // Modify the event handler to trigger on any change, including from an initial empty state
	$('#payment_period').on('change', function() {
		var paymentPeriodValue = $(this).val();
		// Trigger fetchFilteredPosts even if the paymentPeriodValue is empty
		fetchFilteredPosts();
	});
	$('#free_trial, #free_version').on('change', function() {
		fetchFilteredPosts(); // Trigger the AJAX call whenever any of the checkboxes change state
	});


    $('#category-filter-dropdown, input[type="checkbox"][name^="deployment"], input[type="checkbox"][name^="support"], input[type="checkbox"][name^="features"], #max_price, #min_price, input[type="checkbox"][name^="trial"], input[type="checkbox"] [name^="free_version"]').on('change', function() {
        fetchFilteredPosts();
    });

    function fetchFilteredPosts() {
		updateSelectedFeatures();
        // Collect the category selection
        var category = $('#category-filter-dropdown').find('option:selected').val();

        // Collect selected deployment options
        var deployment = $('input[name="deployment[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        // Collect selected support options
        var support = $('input[name="support[]"]:checked').map(function() {
            return $(this).val();
        }).get();
		
		var min_price = $('#min_price').val();
        var max_price = $('#max_price').val();
        var payment_period = $('#payment_period').val();
        var free_trial = $('#free_trial').is(':checked') ? $('#free_trial').val() : '';
        var free_version = $('#free_version').is(':checked') ? $('#free_version').val() : '';
		
								
    	var data = {
            action: 'load_generic_posts',
            category: category,
            deployment: deployment,
            support: support,
            features: selectedFeatures,
            min_price: min_price,
            max_price: max_price,
            payment_period: payment_period,
            free_trial: free_trial,
            free_version: free_version,
        };
		
		
        // Use fetchCachedData instead of direct AJAX call
        fetchCachedData(data, function(response) {
            console.log("AJAX Response: ", response);
            var html = '';
            if (!response.length) {
                console.log("No matches condition met.");
                $('#posts-grid-contain').html('<p>Unfortunately, there are no software solutions with the required criteria.</p>');
            } else {
                response.forEach(function(item) {
						const uniqueId = item.ID || index; // Fallback to index if ID isn't available
						
						html += '<div class="card-container">';
						html += '<div class="software-card">';
						
						
						html += '<div class="">';
						html += '<img src="' + item.image + '" alt="' + item.title + '">';
						
						html += '<div class="software-info">';
						html += '<h2>' + item.title + '</h2>';
						if (item.rating) {
							html += '<div class="software-rating">';
							html += '<div class="s-rating-label">Overall Rating:</div>';
							html += '<div class="star-rating" style="--rating: ' + item.rating + '"></div>';
							html += '<span class="rating-text">' + item.rating + '</span>';
							html += '</div>';
							
						} else {
							html += '<div class="star-rating no-rating"></div>';
							html += '<span class="rating-text">No rating</span>';
						}
						html += '</div>';
						html += '</div>';
						
						
						html += '<div class="software-tabs">';
						html += `<button class="tab-link active" data-tabname="Overview-${uniqueId}" onclick="openTab(event, 'Overview-${uniqueId}')">Overview</button>`;
    					html += `<button class="tab-link" data-tabname="ProsCons-${uniqueId}" onclick="openTab(event, 'ProsCons-${uniqueId}')">Pros and Cons</button>`;
    					html += `<button class="tab-link" data-tabname="Features-${uniqueId}" onclick="openTab(event, 'Features-${uniqueId}')">Features</button>`;
						html += '</div>';
						
						// Truncate overview text if it's longer than 300 characters
						let overview = item.content;
						if (overview.length > 250) {
							overview = overview.substring(0, 250) + '...';
						}
						
						html += `<div class="tab-content" data-tabname="Overview-${uniqueId}" style="display:block;"><h3> Product Introduction </h3><p class="card-product-description">${overview}</p></div>`;
						const pros = item.pros_html;
						const cons = item.cons_html;
						html += `<div class="tab-content" data-tabname="ProsCons-${uniqueId}" style="display:none;"><div class="card-pros"><h3> Pros </h3>${pros}</div> <div class="card-cons"><h3> Cons </h3>${cons}</div></div>`;
						const features = item.features_html;
						html += `<div class="tab-content" data-tabname="Features-${uniqueId}" style="display:none;"><h3> Top Features </h3>${features}</div>`;
						

						
						
						html += '<a href="' + item.link + '" class="software-link">';
						html += '<button class="review-button"> Read More </button>';
						html += '</a>';
						html += '<a href="' + item.affiliate_link + '" class="software-link">';
						html += '<button class="affiliate-button"> Get Started </button>';
						html += '</a>';
						
						html += '</div>';
					});
					$('#posts-grid-contain').html(html);
				
            }
        });
    }
	
	// Initial fetch
    fetchFilteredPosts();
	
	
	// Function to fetch and display features based on the current page and search term
    function fetchFeatures(page = 1, search = '') {
        $.ajax({
            url: generic_ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'load_generic_features', // Ensure this matches your PHP AJAX action
                page: page,
                search: search,
                nonce: generic_nonce,
                request_type: 'fetch_features' // Additional parameter to specify the request type
            },
            success: function(response) {
                // Update the features list
                $('#features-list').html(response.features_html);

                // Update pagination
                generatePaginationNumbers(page, response.total_pages);

                // Update current page
                currentPage = page;
            },
            error: function(xhr, status, error) {
                console.error("Error fetching features: ", status, error);
            }
        });
    }

    function generatePaginationNumbers(currentPage, totalPages) {
        let paginationHtml = '';
        let startPage, endPage;
    
        if (totalPages <= 5) {
            // display all pages if total pages is less than or equal to 5
            startPage = 1;
            endPage = totalPages;
        } else {
            // more than 5 total pages so calculate start and end pages
            if (currentPage <= 3) {
                startPage = 1;
                endPage = 4;
            } else if (currentPage + 2 >= totalPages) {
                startPage = totalPages - 3;
                endPage = totalPages;
            } else {
                startPage = currentPage - 1;
                endPage = currentPage + 2;
            }
        }
    
        // First page and previous arrow
        if (currentPage > 1) {
            paginationHtml += `<span class="page-number" data-page="1">1</span>`;
            paginationHtml += `<span class="page-number" data-page="${currentPage - 1}">&laquo;</span>`;
        }
    
        // Pagination logic
        if (startPage > 1) {
            paginationHtml += '... ';
        }
    
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `<span class="page-number ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</span>`;
        }
    
        if (endPage < totalPages) {
            paginationHtml += '... ';
        }
    
        // Last page and next arrow
        if (currentPage < totalPages) {
            paginationHtml += `<span class="page-number" data-page="${currentPage + 1}">&raquo;</span>`;
            paginationHtml += `<span class="page-number" data-page="${totalPages}">${totalPages}</span>`;
        }
    
        $('#pagination-numbers').html(paginationHtml);
    }
    

    // Event listener for pagination number click
    $(document).on('click', '.page-number', function() {
        const page = $(this).data('page');
        const searchQuery = $('#feature_search').val();
        fetchFeatures(page, searchQuery);
    });


	// Call this function whenever features are successfully loaded and when their state changes
	function setupFeatureCheckboxes() {
		$('input[name="features[]"]').on('change', function() {
			updateSelectedFeatures();
			fetchFilteredPosts(); // Refetch posts whenever the feature selection changes
		});
	}
	
    // Event listener for search input
    $('#feature_search').on('input', function() {
        const searchQuery = $(this).val();
        fetchFeatures(1, searchQuery); // Reset to page 1 with new search
    });

    // Initial fetch of features
    fetchFeatures(currentPage);
	
	

    // Setup initial feature checkboxes
    setupFeatureCheckboxes();
	


});

document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.getElementsByClassName('dropdown-filter');

    for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].querySelector('.dropbtn').addEventListener('click', function() {
            // Find the next sibling with the class 'dropdown-content' and toggle its display
            var content = this.nextElementSibling;
            if (content.style.display === 'block') {
                content.style.display = 'none';
                this.parentNode.classList.remove('active');
            } else {
                content.style.display = 'block';
                this.parentNode.classList.add('active');
            }
        });
    }

    
    var all_dropdowns = document.getElementsByClassName('dopdown-filters-container');

    for (var i = 0; i < all_dropdowns.length; i++) {
        all_dropdowns[i].querySelector('.mobile-filters').addEventListener('click', function() {
            // Find the next sibling with the class 'dropdown-content' and toggle its display
            var filters = document.getElementsByClassName('dropdown-filter')
            for (var j = 0; j < filters.length; j++) {
                filters[j].style.display = filters[j].style.display === 'block' ? 'none' : 'block';
            }
        });
    }

});



function openTab(event, tabName) {
    const parentElement = event.currentTarget.closest('.software-card');

    // Hide all tab contents in this container
    const tabContents = parentElement.querySelectorAll(".tab-content");
    tabContents.forEach(tc => tc.style.display = "none");

    // Remove active class from all tab links in this container
    const tabLinks = parentElement.querySelectorAll(".tab-link");
    tabLinks.forEach(link => link.classList.remove("active"));

    // Show the clicked tab content
    const tabContentToShow = parentElement.querySelector(`.tab-content[data-tabname="${tabName}"]`);
    if(tabContentToShow) {
        tabContentToShow.style.display = "block";
        event.currentTarget.classList.add("active");
    } else {
        console.error('Tab content not found for name: ' + tabName);
    }
}


document.addEventListener("DOMContentLoaded", function() {
    // Directly open the first tab of each card-container without relying on click events.
    var cardContainers = document.querySelectorAll(".card-container");
    cardContainers.forEach(function(container) {
        var firstTabLink = container.querySelector(".tab-link");
        var firstTabName = firstTabLink.getAttribute('data-tabname'); // Assuming data-tabname attribute holds the tab name
        openTabDirectly(container, firstTabName);
    });
});

