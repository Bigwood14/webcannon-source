$(document).ready(function()
{
	$('.add-content').click(
		function ()
		{
			$('#add-content').toggle();	
			return false;
		});

	$('.view-content').click(
		function ()
		{
			var content_id = this.getAttribute('rel');

			$('#content_'+content_id).toggle();
		});

});

