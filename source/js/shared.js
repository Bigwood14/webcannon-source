function window_open (href, title, width, height, attribs)
{
	var winl = (screen.width-width)/2;
	var wint = (screen.height-height)/2;

	if (attribs == '')
		attribs = 'toolbar=1,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0';

	window.open(href, title, attribs+',width='+width+',height='+height+',top='+wint+',left='+winl);
}

$(document).ready(function()
{
	$('.html-image').click(
		function ()
		{
			window.open(this.href, 'HTML Image', 'location=0,status=1,scrollbars=1,width=350,height=200');

			return false;
		});

	$('input.check-all').click(
		function ()
		{
			var checked = this.checked;
			if (checked == true)
				$(this).parents('table.ip-group').find('input[@type=checkbox]').attr('checked', 'checked');
			else
				$('input[@type=checkbox]').removeAttr('checked');
		}
	);
});	
