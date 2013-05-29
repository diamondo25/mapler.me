$(document).ready(function() {
	$('body').on('click', '.mention[status-id]', function() {
		var poster = $(this).attr('poster');
		var mentionlist = $(this).attr('mentions').split(';');
		var input = $('#post-status');
		
		var start = '@' + poster;
		for (var id in mentionlist) {
			if (mentionlist[id] == '') continue;
			if (mentionlist[id].toLowerCase() == window.MemberName.toLowerCase()) continue;
			if (start.indexOf(mentionlist[id]) != -1) continue;
			start += ' @' + mentionlist[id];
		}
		
		input.val(start + ' ');

		$('#post').modal({
			show: true
		})
		
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
	
	$('.fademeout').delay(7000).fadeOut(1000, function() {
		$(this).remove();
	});
	
	$(window).scroll(function() {
		if ($(window).scrollTop() + $(window).height() == $(document).height()) {
			if (didinit)
				TryRequestMore(false, false);
		}
	});
	
	$('#statusposter').submit(function () {
		$('#statusposter button[type="submit"]').attr('disabled', 'disabled');
		$.ajax({
			type: 'POST',
			url: '/api/status/post/',
			data: $(this).serialize(),
			success: function (e) {
				if (e.errormsg != undefined) {
					AddMessageToContent('alert', 'An error occurred: ' + e.errormsg, '');
				}
				else {
					AddMessageToContent('info', 'Successfully posted status!', '');
					$('textarea[name="content"]').val(''); // Empty input
					$('input[name="reply-to"]').attr('value', '-1');
					$('#post').modal('hide');
				}
				$('#statusposter button[type="submit"]').removeAttr('disabled');
			}
		});
		return false;
	});
	
	$('body').on('click', '.deletestatus', function () {
		if (!confirm('Are you sure you want to delete this status?')) return false;
		// var statusobject = $(this).parent('div[status-id]'); // Bugged? Doesn't work
		var statusobject = $(this).parent().parent();
		var statusid = statusobject.attr('status-id');
		
		$.ajax({
			type: 'GET',
			url: '/api/status/delete/' + statusid + '/',
			success: function (e) {
				if (e.errormsg != undefined) {
					AddMessageToContent('alert', 'An error occurred: ' + e.errormsg, '');
				}
				else {
					var char_image = statusobject.find('div[class="character"]');
					var char_image_url = char_image.css('background-image');
					char_image_url = char_image_url.replace('url(', '');
					char_image_url = char_image_url.replace(')', '');
					char_image_url = char_image_url.replace(/"/g, '');
					
					char_image.css('background-image', 'url("' + char_image_url + '?madface")');
					
					statusobject.fadeOut(2000, function() {
						$(this).remove();
					});
				}
			}
		});
		
		return false;
	});
	
	var syncer = function () {
		var statuses = [];
		$('div[class~="status"][status-id]').each(function (index) { statuses.push(parseInt($(this).attr('status-id'))); });

		$.ajax({
			type: 'POST',
			url: '/ajax/sync/',
			//data: { 'shown-statuses': statuses, 'last-level': latestLevelTL },
			data: { 'shown-statuses': statuses},
			success: function (e) {
				serverTickCount = e.time;
				
				var newTitle = window.document.title;
				
				if (newTitle.indexOf(') ') != -1) {
					newTitle = newTitle.substr(newTitle.indexOf(') ') + 2);
				}
				
				if (e.notifications > 0) {
					newTitle = '(' + e.notifications + ') ' + newTitle;
					$('#notify span').get(0).firstChild.nodeValue = e.notifications;
				}
				
				if (e.membername != undefined)
					window.MemberName = e.membername;

				window.document.title = newTitle;
				
				serverTickCount = e.time;
				if (e.status_info != undefined) {
					// Check posts

					if (e.status_info.deleted != undefined) {
						for (var id in e.status_info.deleted) {
							var postid = e.status_info.deleted[id];
							$('div[class~="status"][status-id="' + postid + '"]').fadeOut(2000, function() {
								$(this).remove();
							});
						}
					}

					if (e.status_info.reply_count != undefined) {
						for (var postid in e.status_info.reply_count) {
							var count = e.status_info.reply_count[postid];
							$('.status[status-id="' + postid + '"]').find('.status-reply-count').html(count);
						}
					}
				}

				//if (e.last_level != undefined) {
				//	latestLevelTL = e.last_level;
				//	for (var levelid in e.levels) {
				//		var level = e.levels[levelid];
				//		var derp = $('*[status-post-time]').filter(function () {
				//			return ($(this).attr('status-post-time') >= level[0]);
				//		});
				//		if (derp.length == 0) continue; // yep.
				//		derp.last().parent().parent().before(level[1]);
				//	}
				//}

				// Update posts

				$('a[status-post-time]').each(
					function (index) {
						$(this).html(time_elapsed_string(serverTickCount - $(this).attr('status-post-time')) + ' ago');
					}
				);
				
				console.log(e);
			}
		});
	};
	
	setInterval(syncer, 10000);
	syncer();
});

var serverTickCount = 0;

var latestLevelTL = -1;

var latestStatusUp = -1;
var latestStatusDown = -1;
var didinit = false;
function TryRequestMore(up, init) {
	$.ajax({
		type: 'GET',
		url: '/api/list/' + (up ? latestStatusUp : latestStatusDown) + '/' + (up ? 'up' : 'back') + '/',
		success: function (data) {
			if (data.errormsg != undefined) {
				alert(data.errormsg);
			}
			else if (data.amount > 0) {
				if (up)
					$('#statuslist').prepend(data.result);
				else
					$('#statuslist').append(data.result);

				if (init || !up)
					latestStatusDown = data.firstid;
				if (init || up)
					latestStatusUp = data.lastid;
				if (init)
					didinit = true;
					
				if (init) {
					setInterval("TryRequestMore(true, false)", 10000);
				}
			}
		}
	});
}

function GetBlogPosts(up, init) {
	$.ajax({
		type: 'GET',
		url: '/api/blog/',
		success: function (data) {
			if (data.errormsg != undefined) {
				alert(data.errormsg);
			}
			else if (data.amount > 0) {
				if (up)
					$('#statuslist').prepend(data.result);
				else
					$('#statuslist').append(data.result);

				if (init || !up)
					latestStatusDown = data.firstid;
				if (init || up)
					latestStatusUp = data.lastid;
			}
		}
	});
}

function AddMessageToContent(type, text, location) {
	var obj = $('<p></p>');
	obj.addClass('lead');
	obj.addClass('alert');
	obj.addClass('alert-' + type);
	
	obj.html(text);
	
	obj = $('<div class="span12"></div>').html(obj);
	obj = $('<div class="row"></div>').html(obj);
	
	obj.delay(7000).fadeOut(1000, function() {
		$(this).remove();
	});
	
	$('div[class="container main"]').prepend(obj);
}

function time_elapsed_string(time) {
	if (time <= 1) return 'moments';

	var times = {
		'year' 		: 12 * 30 * 24 * 60 * 60,
		'month' 	: 30 * 24 * 60 * 60,
		'day'		: 24 * 60 * 60,
		'hour'		: 60 * 60,
		'minute'	: 60,
		'second'	: 1
	};

	for (var name in times) {
		var val = times[name];
		val = parseFloat(time / val);
		val = parseInt(Math.round(val));
		if (val >= 1.0) {
			return val + ' ' + name + (val > 1 ? 's' : '');
		}
	}
}

jQuery("html").removeClass("no-js").addClass("js");
if (navigator.appVersion.indexOf("Mac") != -1) {
    jQuery("html").addClass("osx")
}
if ($.browser.opera) {
    $(".fade").removeClass("fade");
    $(".slide").removeClass("slide")
}
jQuery(document).ready(function (a) {
        (function () {
                a('<i id="back-to-top" class="icon-chevron-up"></i>').appendTo(a("body"));
                a(window).scroll(function () {
                        if (a(this).scrollTop() != 0) {
                            a("#back-to-top").fadeIn()
                        } else {
                            a("#back-to-top").fadeOut()
                        }
                    });
                a("#back-to-top").click(function () {
                        a("body,html").animate({
                                scrollTop: 0
                            }, 600)
                    })
            })();
        (function () {
                a(".faq input[type=text]").keyup(function () {
                        var b = a(this).val().toLowerCase();
                        if (b.length > 2) {
                            a(".faq li").each(function () {
                                    var d = a(this).find("h3").text();
                                    var e = a(this).find("p").text();
                                    var c = (d + e).toLowerCase();
                                    if (c.indexOf(b) == -1) {
                                        a(this).hide()
                                    } else {
                                        a(this).show()
                                    }
                                });
                            if (!a(".faq li:visible").length) {
                                a(".no-results").show()
                            } else {
                                a(".no-results").hide()
                            }
                        } else {
                            a(".faq li").show()
                        }
                    })
            })()
    });