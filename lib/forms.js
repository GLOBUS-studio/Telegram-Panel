(function($) {
	'use strict';
        
    $("#formdata").on('submit',(function(e){
		e.preventDefault();

		$("#sendrequestbtn").prop( "disabled", true );
		$(".lds-roller").removeClass("visually-hidden");
		$("#sendrequestbtn").addClass("visually-hidden");

		$.ajax({
		url: "/src/contact-form.php",
		type: "POST",
		data:  new FormData(this),
		contentType: false,
		cache: false,
		processData:false,
		success: function(data){
					$("#contactform-parent").addClass("visually-hidden");
					$("#sendrequestbtn").removeClass("visually-hidden");
					$("#h3").removeClass("visually-hidden");
					$(".delete").addClass("visually-hidden");
					$(".filename").addClass("visually-hidden");
					$(".filename").text('');
                    $("#formdata")[0].reset();
		},
		error: function(data){
					$("#sendrequestbtn").removeClass("visually-hidden");
		} 	        
		}).always(function(data, textStatus, jqXHR) {
			if (data.response == 'success') {
				$("#formdata")[0].reset();
				$("#sendrequestbtn").prop( "disabled", false );
				$(".message").removeClass("visually-hidden");
				$(".lds-roller").addClass("visually-hidden");
				$("#sendrequestbtn").removeClass("visually-hidden");
				setTimeout(function() { 
					$(".message").addClass("visually-hidden");
					$("#openrequestbtn").removeClass("visually-hidden");
				}, 3000);
				return;
			} else if (data.response == 'error' && typeof data.errorMessage !== 'undefined') {
				$("#sendrequestbtn").removeClass("visually-hidden");
				$(".lds-roller").addClass("visually-hidden");
				$(".message").removeClass("visually-hidden");
				$(".message").text(data.errorMessage);
			} else {
				$("#sendrequestbtn").removeClass("visually-hidden");
				$(".lds-roller").addClass("visually-hidden");
				$(".message").removeClass("visually-hidden");
				$(".message").text(data.responseText);
			}
		});
        })
	);
 
}).apply(this, [jQuery]);
