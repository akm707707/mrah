  var firebaseConfig = {
    apiKey: "AIzaSyDbiXfjhwSDEdIa4FKD21Sc2moUcZjqBRM",
    authDomain: "emarah-df6f8.firebaseapp.com",
    databaseURL: "https://emarah-df6f8.firebaseio.com",
    projectId: "emarah-df6f8",
    storageBucket: "emarah-df6f8.appspot.com",
    messagingSenderId: "1023382792114",
    appId: "1:1023382792114:web:05247635f6d2c4266ea6ab",
    measurementId: "G-SPQ745RGF2"
  };
  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
  firebase.analytics();



  /**
   * Set up UI event listeners and registering Firebase auth listeners.
   */
  window.onload = function() {
    // Listening for auth state changes.
    firebase.auth().onAuthStateChanged(function(user) {
      if (user) {
        // User is signed in.
        var uid = user.uid;
        var email = user.email;
        var photoURL = user.photoURL;
        var phoneNumber = user.phoneNumber;
        var isAnonymous = user.isAnonymous;
        var displayName = user.displayName;
        var providerData = user.providerData;
        var emailVerified = user.emailVerified;
      }
      updateSignInFormUI();				// Show or hide sign in form
      updateSignOutButtonUI();			//Show Logout Button
      updateVerificationCodeFormUI();	// Show or hide verification form
    });

    document.getElementById('sign-out-button').addEventListener('click', onSignOutClick); 
    document.getElementById('verification-code-form').addEventListener('submit', onVerifyCodeSubmit);

    // [START appVerifier]
	firebase.auth().languageCode = 'ar';
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('sign-in-button', {
      'size': 'invisible',
      'callback': function(response) {
        // reCAPTCHA solved, allow signInWithPhoneNumber.
        onSignInSubmit();
      }
    });
    // [END appVerifier]

    recaptchaVerifier.render().then(function(widgetId) {
      window.recaptchaWidgetId = widgetId;
     // updateSignInButtonUI();
    });
  };

  /**
   * Function called when clicking the Login/Logout button.
   */
  function onSignInSubmit() {
    if (isPhoneNumberValid()) {												// chick if phone number is valid
      window.signingIn = true;
     // updateSignInButtonUI();												// Disable Log in button if phone number isinvalid
      var phoneNumber = getPhoneNumberFromUserInput();						// Get Phone Number from input form
      var appVerifier = window.recaptchaVerifier;
      firebase.auth().signInWithPhoneNumber(phoneNumber, appVerifier)
          .then(function (confirmationResult) {
            // SMS sent. Prompt user to type the code from the message, then sign the
            // user in with confirmationResult.confirm(code).
            window.confirmationResult = confirmationResult;
            window.signingIn = false;
          //  updateSignInButtonUI();										// Disable Log in button if phone number isinvalid
            updateVerificationCodeFormUI();									// Show or hide verification form
          //updateVerifyCodeButtonUI();
            updateSignInFormUI();											// Show or hide phone number form
          }).catch(function (error) {
            // Error; SMS not sent
			$('body').append('<div class="alert alert-card alert-danger temp" role="alert" ><strong class="text-capitalize">تنبيه ! </strong>خطأ في الخادم حاول لاحقاً<button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>');
			$(".alert").slideDown(250);		$(".alert").delay(2000).slideUp(250);	setTimeout(  function()   {	$(".alert").remove();	}, 3000);
			window.signingIn = false;
            updateSignInFormUI();											// Show or hide phone number form
          });
    }
  }

  /**
   * Function called when clicking the "Verify Code" button.
   */
  function onVerifyCodeSubmit(e) {
    e.preventDefault();
    if (!!getCodeFromUserInput()) {
      window.verifyingCode = true;
      //updateVerifyCodeButtonUI();												// Disable Verify button if invalid
      var code = getCodeFromUserInput();										// Get verification code from input form
      confirmationResult.confirm(code).then(function (result) {					// Successful Login
		Proceed();
        var user = result.user;
        window.verifyingCode = false;
        window.confirmationResult = null;
        updateVerificationCodeFormUI();											// Show or hide verification form
      }).catch(function (error) {
		$(".code").each(function(){		$(this).val('');	});
		$('body').append('<div class="alert alert-card alert-danger temp" role="alert" ><strong class="text-capitalize">تنبيه ! </strong>الرقم السري غير صحيح<button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>');
		$(".alert").slideDown(250);		$(".alert").delay(2000).slideUp(250);	setTimeout(  function()   {	$(".alert").remove();	}, 3000);
		window.signingIn = false;
		document.getElementById("V1").focus();			
		window.verifyingCode = false;
       // updateSignInButtonUI();												// Disable Log in button if phone number isinvalid
       // updateVerifyCodeButtonUI();											// Disable Verify button if invalid
      });
    }
  }

  /**
   * Cancels the verification code input.
   */
  function cancelVerification(e) {
    e.preventDefault();
    window.confirmationResult = null;
    updateVerificationCodeFormUI();												// Show or hide verification form
    updateSignInFormUI();														// Show or hide phone number form
  }

  /**
   * Signs out the user when the sign-out button is clicked.
   */
  function onSignOutClick() {										// Sign out
    firebase.auth().signOut();
  }

  /**
   * Reads the verification code from the user input.
   */
  function getCodeFromUserInput() {									// Get verification code from input form
    return document.getElementById('verification-code').value;
  }

  /**
   * Reads the phone number from the user input.
   */
  function getPhoneNumberFromUserInput() {							// Get Phone Number from input form
    return document.getElementById('phone-number').value;
  }

  /**
   * Returns true if the phone number is valid.
   */
  function isPhoneNumberValid() {									// chick if phone number is valid
    var pattern = /^\+[0-9\s\-\(\)]+$/;
    var phoneNumber = getPhoneNumberFromUserInput();
    return phoneNumber.search(pattern) !== -1;
  }

  /**
   * Re-initializes the ReCaptacha widget.
   */
  function resetReCaptcha() {										// Reset Captha
    if (typeof grecaptcha !== 'undefined'
        && typeof window.recaptchaWidgetId !== 'undefined') {
      grecaptcha.reset(window.recaptchaWidgetId);
    }
  }

  /**
   * Updates the state of the Sign-in form.
   */
  function updateSignInFormUI() {
    if (firebase.auth().currentUser || window.confirmationResult) {		// if phone number is submitted
      document.getElementById('sign-in-form').style.display = 'none';
    } else {															// no phone or incorrect number submitted
      resetReCaptcha();
      document.getElementById('sign-in-form').style.display = 'block';
    }
  }

  /**
   * Updates the state of the Verify code form.
   */
  function updateVerificationCodeFormUI() {
    if (!firebase.auth().currentUser && window.confirmationResult) {				// if phone number is submitted
      document.getElementById('verification-code-form').style.display = 'block';
    } else {																		// no phone number submitted
      document.getElementById('verification-code-form').style.display = 'none';
    }
  }

  /*** Updates the state of the Sign out button.   */
  function updateSignOutButtonUI() {
    if (firebase.auth().currentUser) {
      document.getElementById('sign-out-button').style.display = 'block';
    } else {
      document.getElementById('sign-out-button').style.display = 'none';
    }
  }

  /*** Updates the Signed in user status panel. {*/
     
   /*** Logout on window close. {*/
   
   firebase.auth().setPersistence(firebase.auth.Auth.Persistence.SESSION)
  .then(function() {
	return firebase.auth().signInWithPhoneNumber(phoneNumber, appVerifier);
  })
  .catch(function(error) {
    // Handle Errors here.
    var errorCode = error.code;
    var errorMessage = error.message;
  });
