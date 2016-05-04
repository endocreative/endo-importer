( function( $ )  {

	$(document).ready( function() {
		
		// custom media uploader for user import page
 
	    var user_custom_uploader;
	 
	 
	    $('#upload_endo_importer_csv_button').click(function(e) {
	 
	        e.preventDefault();
	 
	        //If the uploader object has already been created, reopen the dialog
	        if (user_custom_uploader) {
	            user_custom_uploader.open();
	            return;
	        }
	 
	        //Extend the wp.media object
	        user_custom_uploader = wp.media.frames.file_frame = wp.media({
	            title: 'Choose CSV File',
	            button: {
	                text: 'Choose CSV File'
	            },
	            multiple: false
	        });
	 
	        //When a file is selected, grab the URL and set it as the text field's value
	        user_custom_uploader.on('select', function() {
	            attachment = user_custom_uploader.state().get('selection').first().toJSON();

	            console.log( attachment );

	            $('#endo_importer_csv_file').val(attachment.url);
	        });
	 
	        //Open the uploader dialog
	        user_custom_uploader.open();
	 
	    });

	});

})( jQuery );