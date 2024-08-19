jQuery(document).ready(function($) {

		var ajaxCache = {};
	
	
		function fetchSuggestions(search_term, taxonomy, inputId) {
			
			var cacheKey = inputId + ':' + taxonomy + ':' + search_term;
			if (ajaxCache[cacheKey]) {
				displaySuggestions(ajaxCache[cacheKey], inputId);
				return;
			}
			
			jQuery.ajax({
				url: generic_ajax_url,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'fetch_generic_search_suggestions',
					search_term: search_term,
					taxonomy: taxonomy,
					nonce: generic_nonce
				},
				success: function(response) {
					if (response.success) {
						//console.log("AJAX success for:", inputId); // Debug which inputId is receiving data
						ajaxCache[cacheKey] = response.data; // Cache the response
						displaySuggestions(response.data, inputId); // Pass the inputId here
					} else {
						console.error('No suggestions found or an error occurred.');
					}
				},
				error: function(textStatus, errorThrown) {
					console.error('AJAX error: ' + textStatus + ', ' + errorThrown);
				}
			});
		}
		
		// Example: Handling keyup for fetching suggestions
		jQuery('.software-search-input').on('keyup', function() {
			var search_term = jQuery(this).val(); // Correctly capturing the search term here
			var taxonomy = jQuery('#taxonomy_selector').val();
			var inputId = jQuery(this).attr('id');

			// Call fetchSuggestions with the captured 'search_term'
			fetchSuggestions(search_term, taxonomy, inputId);
		});
	
		function displaySuggestions(data, inputId) {
			var suggestionsContainerId = inputId === 'software_input_1' ? '#suggestions_container_1' : '#suggestions_container_2';
			var suggestionsContainer = jQuery(suggestionsContainerId);
			
			suggestionsContainer.empty();

			data.forEach(function(item) {
				var suggestionItem = jQuery('<div class="suggestion-item"></div>').text(item.name).data('id', item.id);
				
				suggestionItem.data('id', item.id);
				suggestionsContainer.append(suggestionItem);

				if (item.image) {
					suggestionItem.prepend('<img src="' + item.image + '" alt="" style="width: 20px; height: 20px; margin-right: 5px;">');
				}
				
				suggestionItem.on('click', function() {
					jQuery('#' + inputId).val(item.name); // Populate the input field with the selected suggestion's name
					suggestionsContainer.empty(); // Clear suggestions after selection
				});
				
			});
		}
	


	jQuery(document).ready(function($) {

		// Event handler for user-initiated changes
		$('#taxonomy_selector').on('change', function() {

			//var taxonomy = $(this).val();
			var taxonomyText = $(this).find("option:selected").text();
			var newUrl = "/software-comparison-tool/Compare-" + taxonomyText.replace(/\s+/g, '-') + "-solutions/";

			// Only redirect if the change was user-initiated
			window.location.href = newUrl;
		});
		
		
	});
	
	// Setting the taxonomy selector based on the passed taxonomy name
	jQuery(document).ready(function($) {	
		if (customAjax.name) {
			var lowercaseTaxonomyName = customAjax.name.toLowerCase().replace(/&/g, '').replace(/[\s\W-]+/g, '-');

			$('#taxonomy_selector').val(lowercaseTaxonomyName);
		}		
	});


	jQuery(document).ready(function($) {
		$('.compare_button').on('click', function(e) {
			e.preventDefault(); // Prevent default form submission if applicable


			// Use IDs or class selectors to accurately select the inputs
			var softwarechoice1 = $('#software_input_1').val(); // Assuming IDs for the inputs
			var softwarechoice2 = $('#software_input_2').val();

        	var taxonomyText = $('#taxonomy_selector').find("option:selected").text().trim();

			// Ensure both software choices are populated
			if (softwarechoice1 && softwarechoice2) {
				// Construct the new URL according to the rewrite rule
				var newUrl = "/software-comparison-tool/Compare-" + taxonomyText.replace(/\s+/g, '-') + "-solutions/" + softwarechoice1.replace(/\s+/g, '-') + "-vs-" + softwarechoice2.replace(/\s+/g, '-') + "/";
				window.location.href = newUrl; // Redirect to the new URL
			} else {
				alert("Please select both software options for comparison."); // Inform the user to select both options
			}
		});
	});

	//An Great idea/alternative would be to call taxonomy template using AJAX only when user is making the selection on the spot. Otherwise when direct link is used, the server side template kicks in...
	
});


jQuery(document).ready(function($) {
    $('.dropdown-toggle').click(function() {
        $(this).next('.dropdown-content').slideToggle('fast');
    });

    // Optional: Automatically open all dropdowns on desktop
    if (window.innerWidth >= 768) {
        $('.dropdown-content').show();
    }
});

//Taxonomy Accordion
var acc = document.getElementsByClassName("accordion");
for (var i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.maxHeight) {
       		panel.style.maxHeight = null;
       } else {
		   	panel.style.maxHeight = panel.scrollHeight + "px";
              } 
       });
   }


   
document.addEventListener('DOMContentLoaded', function() {
    var heroH1 = document.querySelector('.gb-container h1#hero-title'); // Adjust the selector as needed
    if (heroH1 && customAjax && customAjax.name) {
		
        // Convert 'taxonomy_name' format from 'business-management' to 'Business Management'
        var formattedName = customAjax.name.split('-').map(function(word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');

        var newH1Text = 'Software Comparison for ' + formattedName + ' Solutions';
        heroH1.textContent = newH1Text;
    } else {
        console.log('Either hero H1 or taxonomy name not found.');
    }
});

