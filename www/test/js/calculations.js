function showalert(status,statusmessage) {
	var randomclass = generateRandomClass();
	if ( !$(".alertbox")[0] ){		$('body').prepend('<div class="alertbox text-1" ></div>');		}	// create it 
	// if ($(".alert")[0]){			var alertcount = $('.alert').length +1; } else { var alertcount = 1; }

	$('.alertbox').append('<div class="alert body alert-card alert-'+status+' temp '+randomclass+'" role="alert" style="position: relative;display: none;"><button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><strong class="text-capitalize">تنبيه ! </strong>'+statusmessage+'</div>');
	// $(".alert").slideDown(250);		
	// $(".alert").delay(9500).slideUp(250);	
	// setTimeout(  function()   {	
		// $(".alert").remove();	
	// }, 10000);		
	$("." + randomclass).slideDown(250);		
	$("." + randomclass).delay(9500).slideUp(250);	
	setTimeout(  function()   {	
		$("." + randomclass).remove();	
	}, 10000);		
}

	$(document).on("click", ".close", function(){
		if ($(this).is("button")) {		  	//console.log("The selected element is a button.");
		  $(this).closest(".alert-card").remove('');
		} else if ($(this).is("div")) {		//console.log("The selected element is a div.");
		  $(this).closest(".modal").modal('hide');
		}
	});

function generateRandomClass() {
  // Generates a random string (e.g., "0.h8f4q8w4j3").
  // toString(36) converts it to a base-36 representation.
  // substring(2, 9) extracts a specific part to use as the class name.
  return 'random-' + Math.random().toString(36).substring(2, 9);
}

$(document).on("click", ".signout", function(){	//console.log('signout clicked'); 
	$.ajax({	
		type: 'POST',	
		url    : "php/signout.php",
	})
	.done(function(response) {												//console.log(response); 
		if ( response.id == 'success' ) {					
		// Optional: Also sign out of Firebase for immediate sync
		const firebaseConfig = {
		  apiKey: "AIzaSyDZ05k_0OFdlk2IISeh08GoBBlXAKnvE_s",
		  authDomain: "mrah-35885.firebaseapp.com",
		  projectId: "mrah-35885",
		  storageBucket: "mrah-35885.firebasestorage.app",
		  messagingSenderId: "436123048692",
		  appId: "1:436123048692:web:fa19e9386a001dfccfdca1",
		  measurementId: "G-6V5ZQB7JEM"
		};

		firebase.initializeApp(firebaseConfig);
		firebase.auth().signOut().then(() => {
			setTimeout(  function()   {	
				window.location.replace("login.html");		
			}, 2000);		

		});
		
			
		} else {
			console.log("failed");	
		}
	});
	
});

$(document).on("click", ".callcustomizer", function(){	
// $('.callcustomizer').on("click",function(){
	if ( $('.customizer').hasClass('open') ) {
		$('.customizer').hide().toggleClass('open');
	} else {
		$('.customizer').show().toggleClass('open');
	}
});

function fixedwithoutrounding(v, d) {
    return (Math.floor(v * Math.pow(10, d)) / Math.pow(10, d)).toFixed(d);
}

	$(document).on("click", ".nottemphide", function(){
		<!-- $('.loader').modal('show'); -->
		var dropitem = $(this).closest(".dropdown-item");
		var starttime = new Date().getTime();
		$.ajax({
			type: 'POST',
			url    : "php/admin/notification.php",
			data: ({key: 'temphide',id: $(this).attr('id')}),
		})
		.done(function(response) {		console.log(response);
			var end = new Date().getTime();		console.log('Seconds passed', ((end - starttime)/1000));
			var status = response.id;			var statusmessage = response.message;
			showalert(status,statusmessage);
			if ( status == 'success' ) {	console.log('success');
				dropitem.slideUp(250);			setTimeout(  function() { dropitem.remove(); }, 250);		
				var oldbadge = $('.notification-badge').html();
				if ( oldbadge == 1 ){
					$('.notification-badge').html('');
				} else {
					$('.notification-badge').html(Number(oldbadge)-1);
				}
			} else {
			}
		});		
	});

  	$(document).on("click", ".notpermhide", function(){
		<!-- $('.loader').modal('show'); -->
		var dropitem = $(this).closest(".dropdown-item");
		var starttime = new Date().getTime();
		$.ajax({
			type: 'POST',
			url    : "php/admin/notification.php",
			data: ({key: 'permhide',id: $(this).attr('id')}),
		})
		.done(function(response) {		console.log(response);
			var end = new Date().getTime();		console.log('Seconds passed', ((end - starttime)/1000));
			var status = response.id;			var statusmessage = response.message;
			showalert(status,statusmessage);
			if ( status == 'success' ) {	console.log('success');
				dropitem.slideUp(250);			setTimeout(  function() { dropitem.remove(); }, 250);		
				var oldbadge = $('.notification-badge').html();
				if ( oldbadge == 1 ){
					$('.notification-badge').html('');
				} else {
					$('.notification-badge').html(Number(oldbadge)-1);
				}
			} else {
			}
		});		
	});	
	
  	$(document).on("click", ".notreset", function(){
		<!-- $('.loader').modal('show'); -->
		<!-- var dropitem = $(this).closest(".dropdown-item"); -->
		var starttime = new Date().getTime();
		$.ajax({
			type: 'POST',
			url    : "php/admin/notification.php",
			data: ({key: 'notreset'}),
		})
		.done(function(response) {		console.log(response);
			var end = new Date().getTime();		console.log('Seconds passed', ((end - starttime)/1000));
			var status = response.id;			var statusmessage = response.message;		var notnum = response.notnum;
			showalert(status,statusmessage);
			if ( status == 'success' ) {	console.log('success');
				if ( notnum > 0 ) {
					var oldbadge = $('.notification-badge').html();
					$('.notification-badge').html(Number(notnum)+Number(oldbadge));
					console.log(response[0]);
					for (i=0;i<notnum;++i) { 	
						var notid = response[0][i].id; 
						var notcode = response[0][i].code; 
							if ( notcode == 'MAXDEBT') { 	notcode = 'عميل مدين';			notcolor = 'warning';	noticon = 'Money-2'; }
							if ( notcode == 'LOWINV') { 	notcode = 'نفاذ مخزون';		notcolor = 'warning';	noticon = 'Down'; }
							if ( notcode == 'LOWRPRICE') { 	notcode = 'خسائر محتمله';		notcolor = 'danger';	noticon = 'Bar-Chart-5'; }
							if ( notcode == 'LOWWSPRICE') { notcode = 'خسائر محتمله';		notcolor = 'danger';	noticon = 'Bar-Chart-5'; }
							if ( notcode == 'EXPIRED') {	 notcode = 'منتج منتهي';		notcolor = 'danger';	noticon = 'Sand-watch'; }
							if ( notcode == 'EXPIRING') { 	notcode = 'منتج على وشك الانتهاء';	notcolor = 'warning';	noticon = 'Sand-watch-2'; }
						var notmessage = response[0][i].message; 
						var notopsid = response[0][i].opsid; 
						var nottimeadded = response[0][i].timeadded; 
						html = '';						
						html += '<div class="dropdown-item mobiledropdownitem d-flex">';
						html += '<div class="notification-icon mobilenotificationicon">';
						html += '<i class="i-'+noticon+' text-'+notcolor+' mr-1"></i>';
						html += '</div>';
						// html += '<div class="notification-details flex-grow-1">';
						html += '<div class="notification-details flex-grow-1" style="width: 96%;">';
						
						html += '<p class="m-0 d-flex align-items-center">';
						html += '<span class="badge badge-pill badge-'+notcolor+' ml-1 mr-1">'+notcode+'</span>';
						html += '<span class="flex-grow-1"></span>';
						html += '<i class="i-Eye nottemphide text-info mr-2" style="font-size: 20px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="إخفاء مؤقت" id="'+notid+'"></i>';
						html += '<i class="i-Close-Window notpermhide text-danger mr-2" style="font-size: 20px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="حذف التنبيه" id="'+notid+'"></i>';
						html += '<span class="text-small text-muted ml-auto">'+nottimeadded+'</span>';
						html += '</p>';
						html += '<p class="text-small text-muted mobilenotificationmessage m-0">'+notmessage+'</p>';
						// html += '<p class="text-small text-muted m-0"  style="word-wrap: break-word;overflow: auto;height: auto;white-space: pre-line;">'+notmessage+'</p>';
						html += '</div>';
						html += '</div>';
						$('.notification-dropdown').prepend(html);
					}
				}				
			} else {
			}
		});		
	});	
	
function populatenotifications() {			console.log('populatenotifications is up');
		$('.notification-badge').html(notnum);
		// console.log(response[0]);
		for (i=0;i<notnum;++i) { 	
			var notid = notdata[i].id; 
			var notcode = notdata[i].code; 
				if ( notcode == 'MAXDEBT') { 	notcode = 'عميل مدين';			notcolor = 'warning';	noticon = 'Money-2'; }
				if ( notcode == 'LOWINV') { 	notcode = 'نفاذ مخزون';		notcolor = 'warning';	noticon = 'Down'; }
				if ( notcode == 'LOWRPRICE') { 	notcode = 'خسائر محتمله';		notcolor = 'danger';	noticon = 'Bar-Chart-5'; }
				if ( notcode == 'LOWWSPRICE') { notcode = 'خسائر محتمله';		notcolor = 'danger';	noticon = 'Bar-Chart-5'; }
				if ( notcode == 'EXPIRED') {	 notcode = 'منتج منتهي';		notcolor = 'danger';	noticon = 'Sand-watch'; }
				if ( notcode == 'EXPIRING') { 	notcode = 'منتج على وشك الانتهاء';	notcolor = 'warning';	noticon = 'Sand-watch-2'; }
			var notmessage = notdata[i].message; 
			var notopsid = notdata[i].opsid; 
			var nottimeadded = notdata[i].timeadded; 
			html = '';
			html += '<div class="dropdown-item mobiledropdownitem d-flex">';
			html += '<div class="notification-icon mobilenotificationicon">';
			html += '<i class="i-'+noticon+' text-'+notcolor+' mr-1"></i>';
			html += '</div>';
			// html += '<div class="notification-details flex-grow-1">';
			html += '<div class="notification-details flex-grow-1" style="width: 96%;">';
			
			html += '<p class="m-0 d-flex align-items-center">';
			html += '<span class="badge badge-pill badge-'+notcolor+' ml-1 mr-1">'+notcode+'</span>';
			html += '<span class="flex-grow-1"></span>';
			html += '<i class="i-Eye nottemphide text-info mr-2" style="font-size: 20px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="إخفاء مؤقت" id="'+notid+'"></i>';
			html += '<i class="i-Close-Window notpermhide text-danger mr-2" style="font-size: 20px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="حذف التنبيه" id="'+notid+'"></i>';
			html += '<span class="text-small text-muted ml-auto">'+nottimeadded+'</span>';
			html += '</p>';
			html += '<p class="text-small text-muted mobilenotificationmessage m-0">'+notmessage+'</p>';
			// html += '<p class="text-small text-muted m-0"  style="word-wrap: break-word;overflow: auto;height: auto;white-space: pre-line;">'+notmessage+'</p>';
			html += '</div>';
			html += '</div>';
			$('.notification-dropdown').prepend(html);
			
		}
	
}

// Stop Page scrolling past scrollable div
$(document).on("mousewheel DOMMouseScroll", ".notification-dropdown", function(){	
// $( '.notification-dropdown' ).on( 'mousewheel DOMMouseScroll', function ( e ) {  
    var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;

    this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
    e.preventDefault();
});

// no propogation when clicking on DD
$(document).on('click', '.notification-dropdown', function (e) {
  e.stopPropagation();
});
	
	
function a2e(text) {
	return text.replace(
		/[٠١٢٣٤٥٦٧٨٩]/g,
		d => String(d.charCodeAt(0) - 1632)   // ٠ = U+0660 → 48 = '0' in ASCII
	);
}
	
const getRangeShort = (a, b) => 
Array.from({length: Math.abs(b - a) + 1}, (_, i) => Math.min(a, b) + i);

function resetfilter() {
	localStorage.removeItem('sheepFilterState');
	document.querySelectorAll('.filtercheckbox').forEach(cb => { cb.checked = cb.defaultChecked; });
	$('input[name="tagnumberfrom"]').val("0");
	$('input[name="tagnumberto"]').val("1000");
	$('input[name="agefrom"]').val("0");
	$('input[name="ageto"]').val("100");
	$('[name="tagnumberfilter"]').trigger("click");
	console.log('filterreset');
}

// change arabic numerals to english 
$(document).on('input', '.ui.dropdown .search, input[inputmode="numeric"], input[inputmode="decimal"]', function() {
    const $this = $(this);
    const value = $this.val();
   
   const newValue = value.replace(/[٠١٢٣٤٥٦٧٨٩٫]/g, d => 
    '٠١٢٣٤٥٦٧٨٩.'.indexOf(d) === -1 ? '.' : '٠١٢٣٤٥٦٧٨٩.'.indexOf(d)
	);

    if (newValue !== value) {
        const pos = this.selectionStart;
        const diff = value.length - newValue.length;
        $this.val(newValue);
        // Try to preserve cursor position
        this.setSelectionRange(pos - diff, pos - diff);
        console.log('aratoeng – inner search input');
    }
});

// Global fix - Fix IOS Background scrolling when modal is open
(function ($) {
  'use strict';

  var scrollTopWhenModalOpened = 0;

  // When ANY modal is about to show
  $(document).on('show.bs.modal', '.modal', function () {
    if ($('.modal:visible').length === 0) {
      // Only do this when first modal opens (prevents overwriting on stacked ones)
      scrollTopWhenModalOpened = $(window).scrollTop();

      $('body').css({
        position: 'fixed',
        width: '100%',
        top: -scrollTopWhenModalOpened + 'px'
      });
    }
    // Optional: increase z-index for stacked modals so they layer properly
    // $(this).css('z-index', 1050 + $('.modal:visible').length * 10);
  });

  // When ANY modal is fully hidden
  $(document).on('hidden.bs.modal', '.modal', function () {
    // Check if this was the last modal
    if ($('.modal:visible').length === 0) {
      // Restore body & scroll position
      var scrollY = $('body').css('top');
      $('body').css({
        position: '',
        top: '',
        width: ''
      });
      window.scrollTo(0, parseInt(scrollY || '0') * -1 || scrollTopWhenModalOpened);
    }
  });

})(jQuery);