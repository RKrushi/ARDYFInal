/*
═══════════════════════════════════════════════════════════════════
  ARDY Real Estate - Contact Form Handler
  Pure JavaScript Email Solution using EmailJS
═══════════════════════════════════════════════════════════════════

  This file provides the complete email sending functionality for
  the contact form. All emails are sent directly from the browser
  using EmailJS - no backend server required!

  SETUP:
  Follow the instructions in EMAIL-SETUP-GUIDE.md to configure
  your EmailJS account and get your credentials.

═══════════════════════════════════════════════════════════════════
*/

// ═══ CONFIGURATION ═══
// Replace these values with your actual EmailJS credentials
// See EMAIL-SETUP-GUIDE.md for detailed setup instructions
const EMAILJS_CONFIG = {
  publicKey: 'YOUR_PUBLIC_KEY',        // From EmailJS Account page
  serviceID: 'YOUR_SERVICE_ID',        // From EmailJS Email Services
  templateID: 'YOUR_TEMPLATE_ID'       // From EmailJS Email Templates
};

// The admin email address that will receive form submissions
const ADMIN_EMAIL = 'info@ardyrealestatees.com';

// ═══ INITIALIZATION ═══
// Initialize EmailJS when the page loads
(function initEmailJS() {
  try {
    if (typeof emailjs !== 'undefined') {
      emailjs.init(EMAILJS_CONFIG.publicKey);
      console.log('✅ EmailJS initialized successfully');
    } else {
      console.error('❌ EmailJS library not loaded. Check internet connection.');
    }
  } catch (error) {
    console.error('❌ Error initializing EmailJS:', error);
  }
})();

// ═══ FORM UTILITY FUNCTIONS ═══

/**
 * Updates the character counter for the message textarea
 * @param {HTMLElement} el - The textarea element
 */
function cfCnt(el) {
  const counter = document.getElementById('cfCntEl');
  if (counter) {
    counter.textContent = el.value.length + ' / ' + el.maxLength;
  }
}

/**
 * Adds visual feedback to select dropdowns when an option is chosen
 */
function initSelectHighlighting() {
  document.querySelectorAll('#cfForm select').forEach(function(select) {
    select.addEventListener('change', function() {
      this.classList.toggle('picked', !!this.value);
    });
  });
}

// Initialize select highlighting when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSelectHighlighting);
} else {
  initSelectHighlighting();
}

// ═══ FORM VALIDATION ═══

/**
 * Validates all required form fields
 * @returns {boolean} - True if all validations pass
 */
function cfValidate() {
  let isValid = true;
  
  /**
   * Validates a single field and shows/hides error messages
   * @param {string} fieldId - ID of the input field
   * @param {string} errorId - ID of the error message element
   * @param {Function} validator - Function that returns true if valid
   */
  function validateField(fieldId, errorId, validator) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    const value = field.value.trim();
    const passes = validator(value);
    
    // Toggle error styling
    field.classList.toggle('bad', !passes);
    error.classList.toggle('show', !passes);
    
    // Focus first invalid field
    if (!passes && isValid) {
      field.focus();
      isValid = false;
    }
    
    // Remove error styling when user starts typing
    field.addEventListener('input', function() {
      this.classList.remove('bad');
      document.getElementById(errorId).classList.remove('show');
    }, { once: true });
  }
  
  // Validate each field
  validateField('cf_name', 'e_name', function(v) {
    return v.length >= 2;
  });
  
  validateField('cf_email', 'e_email', function(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  });
  
  validateField('cf_phone', 'e_phone', function(v) {
    return v.replace(/\D/g, '').length >= 6;
  });
  
  return isValid;
}

// ═══ FORM SUBMISSION ═══

/**
 * Checks if EmailJS has been properly configured
 * @returns {boolean}
 */
function isEmailJSConfigured() {
  return EMAILJS_CONFIG.publicKey !== 'YOUR_PUBLIC_KEY' &&
         EMAILJS_CONFIG.serviceID !== 'YOUR_SERVICE_ID' &&
         EMAILJS_CONFIG.templateID !== 'YOUR_TEMPLATE_ID';
}

/**
 * Collects form data and returns it as an object
 * @returns {Object} Form data object
 */
function collectFormData() {
  return {
    from_name: document.getElementById('cf_name').value.trim(),
    from_email: document.getElementById('cf_email').value.trim(),
    phone: document.getElementById('cf_phone').value.trim(),
    service_interest: document.getElementById('cf_service').value || 'Not specified',
    budget_range: document.getElementById('cf_budget').value || 'Not specified',
    message: document.getElementById('cf_msg').value.trim() || 'No message provided',
    to_email: ADMIN_EMAIL,
    submission_date: new Date().toLocaleString('en-US', {
      timeZone: 'Asia/Dubai',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    })
  };
}

/**
 * Sets the button to loading state
 * @param {HTMLElement} button - The submit button
 */
function setLoadingState(button) {
  button.classList.add('busy');
  button.disabled = true;
  button.querySelector('.btxt').textContent = 'Sending…';
}

/**
 * Resets the button from loading state
 * @param {HTMLElement} button - The submit button
 */
function cfReset(button) {
  button.classList.remove('busy');
  button.disabled = false;
  button.querySelector('.btxt').textContent = 'Send Message';
}

/**
 * Resets the form to its initial state
 */
function resetForm() {
  const form = document.getElementById('cfForm');
  form.reset();
  
  // Reset character counter
  const counter = document.getElementById('cfCntEl');
  if (counter) counter.textContent = '0 / 1000';
  
  // Reset select highlighting
  document.querySelectorAll('#cfForm select').forEach(function(select) {
    select.classList.remove('picked');
  });
}

/**
 * Shows the success message banner
 */
function showSuccessMessage() {
  const successBanner = document.getElementById('cfOk');
  successBanner.classList.add('show');
  successBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Shows the error message banner
 * @param {string} message - Error message to display (HTML allowed)
 */
function cfShowFail(message) {
  const failBanner = document.getElementById('cfFail');
  const failMessage = document.getElementById('cfFailMsg');
  
  if (message) {
    failMessage.innerHTML = message;
  }
  
  failBanner.classList.add('show');
  failBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Hides all result banners (success and error)
 */
function hideResultBanners() {
  document.getElementById('cfOk').classList.remove('show');
  document.getElementById('cfFail').classList.remove('show');
}

/**
 * Main function to send the contact form
 * Called when user clicks the "Send Message" button
 */
function cfSend() {
  console.log('📧 Contact form submission started...');
  
  // Hide any previous result messages
  hideResultBanners();
  
  // Validate form fields
  if (!cfValidate()) {
    console.log('❌ Validation failed');
    return;
  }
  
  console.log('✅ Validation passed');
  
  // Check if EmailJS is configured
  if (!isEmailJSConfigured()) {
    console.warn('⚠️ EmailJS not configured');
    cfShowFail(
      '⚠️ <strong>EmailJS Not Configured</strong><br>' +
      'Please follow the setup instructions in <code>EMAIL-SETUP-GUIDE.md</code> to configure email sending. ' +
      'For now, you can contact us directly at ' +
      '<a href="mailto:' + ADMIN_EMAIL + '" style="color:inherit;text-decoration:underline">' + 
      ADMIN_EMAIL + '</a>'
    );
    return;
  }
  
  // Check if EmailJS library is loaded
  if (typeof emailjs === 'undefined') {
    console.error('❌ EmailJS library not loaded');
    cfShowFail(
      '❌ <strong>Email Service Unavailable</strong><br>' +
      'The email service could not be loaded. Please check your internet connection and try again. ' +
      'Alternatively, contact us at ' +
      '<a href="mailto:' + ADMIN_EMAIL + '" style="color:inherit;text-decoration:underline">' + 
      ADMIN_EMAIL + '</a>'
    );
    return;
  }
  
  // Get submit button and set loading state
  const submitButton = document.getElementById('cfBtn');
  setLoadingState(submitButton);
  
  // Collect form data
  const formData = collectFormData();
  console.log('📋 Form data collected:', formData);
  
  // Send email via EmailJS
  console.log('📤 Sending email via EmailJS...');
  
  emailjs.send(
    EMAILJS_CONFIG.serviceID,
    EMAILJS_CONFIG.templateID,
    formData
  )
  .then(function(response) {
    console.log('✅ Email sent successfully!', response.status, response.text);
    
    // Reset button state
    cfReset(submitButton);
    
    // Show success message
    showSuccessMessage();
    
    // Reset the form
    resetForm();
    
    // Log success for debugging
    console.log('🎉 Form submission completed successfully');
  })
  .catch(function(error) {
    console.error('❌ EmailJS Error:', error);
    
    // Reset button state
    cfReset(submitButton);
    
    // Determine error message
    let errorMessage = '❌ <strong>Unable to Send Message</strong><br>';
    
    if (error.text) {
      errorMessage += 'Error: ' + error.text + '<br>';
    }
    
    if (error.status === 400) {
      errorMessage += 'Invalid configuration. Please check your EmailJS settings.<br>';
    } else if (error.status === 403) {
      errorMessage += 'Access denied. Please verify your Public Key and Service ID.<br>';
    } else if (error.status === 429) {
      errorMessage += 'Too many requests. Please try again in a few minutes.<br>';
    }
    
    errorMessage += 'Please try again or contact us directly at ' +
      '<a href="mailto:' + ADMIN_EMAIL + '" style="color:inherit;text-decoration:underline">' + 
      ADMIN_EMAIL + '</a>';
    
    // Show error message
    cfShowFail(errorMessage);
  });
}

// ═══ FAQ ACCORDION ═══

/**
 * Toggles FAQ accordion items
 * @param {HTMLElement} questionElement - The clicked FAQ question element
 */
function toggleFaq(questionElement) {
  const answer = questionElement.nextElementSibling;
  const isCurrentlyOpen = questionElement.classList.contains('open');
  
  // Close all FAQ items
  document.querySelectorAll('.faq-q.open').forEach(function(openQuestion) {
    openQuestion.classList.remove('open');
    openQuestion.nextElementSibling.classList.remove('open');
  });
  
  // Open clicked item if it wasn't already open
  if (!isCurrentlyOpen) {
    questionElement.classList.add('open');
    answer.classList.add('open');
  }
}

// ═══ CONSOLE INFO ═══
console.log(
  '%c📧 ARDY Contact Form Ready',
  'color: #C4B693; font-size: 16px; font-weight: bold;'
);
console.log(
  '%cEmailJS Status: ' + (isEmailJSConfigured() ? '✅ Configured' : '⚠️ Not Configured'),
  'color: ' + (isEmailJSConfigured() ? '#27ae60' : '#f39c12') + '; font-size: 12px;'
);

if (!isEmailJSConfigured()) {
  console.log(
    '%c⚠️ Setup required! See EMAIL-SETUP-GUIDE.md for instructions.',
    'color: #f39c12; font-size: 12px;'
  );
}
