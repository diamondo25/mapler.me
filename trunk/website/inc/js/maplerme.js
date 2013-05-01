$(document).ready(function() {
	$('body').on('click', '.mention[status-id]', function() {
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
	
	// Bind click-on-status stuff
	$('body').on('click', '.status[status-id]', function() {
		var statusid = $(this).attr('status-id');
		var uniqueid = $(this).attr('unique-id');
		$.ajax({
			type: 'GET',
			url: '/api/status/' + statusid + '/',
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


var latestStatusUp = -1;
var latestStatusDown = -1;
function TryRequestMore(up, init) {
	$.ajax({
		type: 'GET',
		url: '/api/list/' + (up ? latestStatusUp : latestStatusDown) + '/' + (up ? 'up' : 'back') + '/',
		success: function (data) {
			if (data.error != undefined) {
				alert(data.error);
			}
			else if (data.amount > 0) {
				var oridata = $('#statuslist').html();
				if (up)
					$('#statuslist').html(data.result + oridata);
				else
					$('#statuslist').html(oridata + data.result);

				if (init || !up)
					latestStatusDown = data.firstid;
				if (init || up)
					latestStatusUp = data.lastid;
					
				// if (init) {
				//	setInterval("TryRequestMore(true, false)", 10000);
				// }
			}
		}
	});
}

function GetBlogPosts(up, init) {
	$.ajax({
		type: 'GET',
		url: '/api/blog/',
		success: function (data) {
			if (data.error != undefined) {
				alert(data.error);
			}
			else if (data.amount > 0) {
				var oridata = $('#statuslist').html();
				if (up)
					$('#statuslist').html(data.result + oridata);
				else
					$('#statuslist').html(oridata + data.result);

				if (init || !up)
					latestStatusDown = data.firstid;
				if (init || up)
					latestStatusUp = data.lastid;
			}
		}
	});
}