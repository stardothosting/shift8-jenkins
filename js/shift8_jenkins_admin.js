jQuery(document).ready(function() {
	jQuery('#shift8-jenkins-push-schedule').change(function() {    
    	var shift8_jenkins_item=jQuery(this);
    	var shift8_jenkins_href = jQuery('#shift8-jenkins-push').attr('href');
    	var shift8_jenkins_pushurl = new URL(shift8_jenkins_href);
    	shift8_jenkins_pushurl.searchParams.set("schedule", shift8_jenkins_item.val()); 
    	jQuery('#shift8-jenkins-push').attr('href', shift8_jenkins_pushurl);
    });

	jQuery(document).on( 'click', '#shift8-jenkins-push', function(e) {
		e.preventDefault();
		//if (confirm('Are you sure you want to push staging to production?')) {
			var button = jQuery(this);
	    	var url = button.attr('href');
	    	jQuery.ajax({
	        	url: url,
	        	data: {
	            	'action': 'shift8_jenkins_push',
	        	},
	        	success:function(data) {
	            	// This outputs the result of the ajax request
	            	jQuery('.shift8-jenkins-push-progress').html(data);
	        	},
	        	error: function(errorThrown){
	            	console.log('Error : ' + JSON.stringify(errorThrown));
	        	}
			}); 
		//}
	});
});