$(document).ready(function()
{
	$('#personalization-customize').click(function() { window.open(this.href, 'Personlization', 'location=0,status=1,scrollbars=1,width=350,height=400'); return false; });
    $('#multi_from').click(function() { swap_multi_from(); });
    $('#multi_subject').click(function() { swap_multi_subject(); });
    $('#type').change(function() { set_message_inputs(); });
	$('.from-new').click(
		function ()
		{
			$('#from-clone').clone(true).css('display', '').attr("id","").insertBefore($('#from-link'));	
			return false;
		});

	$('.from-remove').click(
		function ()
		{
			$(this).parent().remove();
			return false;
		});
	$('.subject-new').click(
		function ()
		{
			$('#subject-clone').clone(true).css('display', '').attr("id","").insertBefore($('#subject-link'));	
			return false;
		});

	$('.subject-remove').click(
		function ()
		{
			$(this).parent().remove();
			return false;
		});
	$('.suppression-new').click(
		function ()
		{
			$('#suppression-clone').clone(true).css('display', '').attr("id","").insertBefore($('#suppression-link'));	
			return false;
		});

	$('a.tracked-link-edit').click(
		function ()
		{
			window_open(this.href, 'Tracked Link Edit', 400, 150);
			return false;
		});

	$('a.image-edit').click(
		function ()
		{
			window_open(this.href, 'Image Edit', 400, 150);
			return false;
		});


	$('.suppression-remove').click(
		function ()
		{
			$(this).parent().remove();
			return false;
		});
	$("#bodies > ul").tabs();

	$('#draft-submit').click(
		function ()
		{
			var got_aol 	= false;
			var got_norm 	= false;

			$("#domains > span > input").each(
				function ()
				{			
					if (this.checked == true)
					{
						var breaker = false;
						$(this).next("label:contains('AOL')").each(function () {
							got_aol = true;
							breaker = true;
						});

						if (breaker == false)
							got_norm = true;
					}
				}
			);

			ret = true;

			if (got_aol == true && got_norm == true)
			{
				ret = confirm("You are mixing AOL whitelisted with none whitelisted are you sure you want to continue?");
			}

			return ret;
		}
	);

	$('#form-body_text_check').change(
		function()
		{
			if (this.checked != true)
				$('#form-body_text').attr('disabled', 'disabled');
			else
				$('#form-body_text').removeAttr('disabled');
		});
	$('#form-body_text_check').change();

	$('#form-body_html_check').change(
		function()
		{
			if (this.checked != true)
				$('#form-body_html').attr('disabled', 'disabled');
			else
				$('#form-body_html').removeAttr('disabled');
		});
	$('#form-body_html_check').change();

	$('#form-body_aol_check').change(
		function()
		{
			if (this.checked != true)
				$('#form-body_aol').attr('disabled', 'disabled');
			else
				$('#form-body_aol').removeAttr('disabled');
		});
	$('#form-body_aol_check').change();

	$('#form-body_yahoo_check').change(
		function()
		{
			if (this.checked != true)
				$('#form-body_yahoo').attr('disabled', 'disabled');
			else
				$('#form-body_yahoo').removeAttr('disabled');
		});
	$('#form-body_yahoo_check').change();


	$('.link-new').click(
		function ()
		{
			$('#link-clone').clone(true).css('display', '').attr("id","").insertBefore($('#link-links'));	
			return false;
		});

	$('.link-remove').click(
		function ()
		{
			$(this).parent().remove();
			return false;
		});
	$('.link-auto').click(
		function ()
		{
			var callback = function (data, textStatus) {
				for (i=0;i<data.length;i++)
					$('#link-clone').clone(true).css('display', '').attr("id","").insertBefore($('#link-links')).find('input').attr("value",data[i]);

			};
  			jQuery.post('/cp/scheduling/draft.php?action=links_find', $('#draft-form').serialize(), callback, "json") ;
			return false;
		});
	$('.link-image-auto').click(
		function ()
		{
			var callback = function (data, textStatus) {
				for (i=0;i<data.length;i++)
					$('#link-clone').clone(true).css('display', '').attr("id","").insertBefore($('#link-links')).find('input').attr("value",data[i]);
			};
  			jQuery.post('/cp/scheduling/draft.php?action=links_find', $('#draft-form').serialize()+'&links_find_images=1', callback, "json") ;
			return false;
		});

	$('.ip-group-title a').click(
		function ()
		{
			var obj = $(this).parent().next();
			//alert(obj.css('display', 'block'));

			if (obj.css('display') == 'none')
			{
				obj.css('display', 'block');
				$(this).children('img').attr('src', '/images/misc/minus.gif');
			}
			else
			{
				obj.css('display', 'none');
				$(this).children('img').attr('src', '/images/misc/plus.gif');
			}
		}
	);

	$('.ip-group-checkbox').click(
		function ()
		{
			if (this.checked == true)
				$(this).parent().next().children('span').children('input').attr('checked', true);
			else
				$(this).parent().next().children('span').children('input').attr('checked', false);
		}
	);
});

