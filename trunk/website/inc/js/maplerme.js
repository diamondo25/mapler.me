$(document).ready(function() {
	$('.mention[status-id]').click(function() {
		var poster = $(this).attr('poster');
		var mentionlist = $(this).attr('mentions').split(';');
		var input = $('#post-status');
		
		var start = '@' + poster;
		for (var id in mentionlist) {
			if (mentionlist[id] == '') continue;
			start += ' @' + mentionlist[id];
		}
		
		input.val(start + ' ');
		$('.poster').addClass("in");
		$('.poster').css("height", "auto");
		
		$("input[name='reply-to']").attr('value', $(this).attr('status-id'));
		
		input.focus();
		return false;
	});
	
	$('#post-status').keyup(function() {
		if ($(this).val() == '' && $("input[name='reply-to']").attr('value') != -1) {
			// Empty? Reset reply
			$("input[name='reply-to']").attr('value', -1);
		}
	});
	
	$('body').on('click', '.status[status-id]', function() {
		var statusid = $(this).attr('status-id');
		var uniqueid = $(this).attr('unique-id');
		$.ajax({
			type: 'GET',
			url: '/ajax/status.php?type=responses&statusid=' + statusid,
			success: function (data) {
				if (data.error != undefined) {
					alert(data.error);
				}
				else {
					$('.reply-list[status-id=' + statusid + '][unique-id=' + uniqueid + ']').html(data.result);
				}
			}
		
		});
	
	});
});

