document.addEventListener('DOMContentLoaded', function () {
  var cookieConsentPopup = document.getElementById('cookieConsentPopup');  
  var acceptButton = document.getElementById('acceptButton');
  var customizeButton = document.getElementById('customizeButton');
  var consentButtons = document.getElementById('consentButtons');
  var consentForm = document.getElementById('consentForm');
  var closeButton = document.getElementById('closeButton');
  var saveButton = document.getElementById('saveButton');
  var backButton = document.getElementById('backButton');
  var cookieOverlay = document.getElementById('cookieOverlay');
  

  acceptButton.addEventListener('click', function () {
      setCookie('essential', 'yes');
      setCookie('analytics', 'yes');
      setCookie('marketing', 'yes');
      activateScripts();
      hideConsentPopup();
  });

  customizeButton.addEventListener('click', function () {
      consentButtons.style.display = 'none';
      consentForm.style.display = 'block';
  });

  saveButton.addEventListener('click', function (event) {
      event.preventDefault();
      var analyticsSwitch = document.getElementById('analyticsSwitch');
      var marketingSwitch = document.getElementById('marketingSwitch');
      var analyticsValue = analyticsSwitch.checked ? 'yes' : 'no';
      var marketingValue = marketingSwitch.checked ? 'yes' : 'no';
      setCookie('essential', 'yes');
      setCookie('analytics', analyticsValue);
      setCookie('marketing', marketingValue);
      activateScripts();
      hideConsentPopup();
  });

  backButton.addEventListener('click', function () {
      consentButtons.style.display = 'block';
      consentForm.style.display = 'none';
  });

  closeButton.addEventListener('click', function () {
      setCookie('essential', 'yes');
      setCookie('analytics', 'yes');
      setCookie('marketing', 'yes');
      activateScripts();
      hideConsentPopup();
  });

  function setCookie(name, value) {
      var expires = new Date();
      expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
      document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
  }

  function activateScripts() {
      var analyticsValue = getCookie('analytics');
      var marketingValue = getCookie('marketing');
      if (analyticsValue === 'yes') {
          // Activate analytics scripts
          console.log('Activating analytics scripts...');
      }
      if (marketingValue === 'yes') {
          // Activate marketing scripts
          console.log('Activating marketing scripts...');
      }
  }

 function hideConsentPopup() {
    cookieConsentPopup.style.display = 'none';
    cookieOverlay.style.display = 'none'; // Hide the overlay
  }

  function showConsentPopup() {
    cookieConsentPopup.style.display = 'block';
    cookieOverlay.style.display = 'block'; // Show the overlay
  }

  // Hide the popup and overlay initially
  hideConsentPopup();

  // Show the popup and overlay when needed
  showConsentPopup();


  function getCookie(name) {
      var cookieName = name + '=';
      var cookies = document.cookie.split(';');
      for (var i = 0; i < cookies.length; i++) {
          var cookie = cookies[i].trim();
          if (cookie.indexOf(cookieName) === 0) {
              return cookie.substring(cookieName.length, cookie.length);
          }
      }
      return '';
  }
});
