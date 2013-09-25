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
	
	$('#post-status').bind("focus", keypress.stop_listening);
	$('#post-status').bind("blur", keypress.listen);
	$('input[type=text]').bind("focus", keypress.stop_listening);
	$('input[type=text]').bind("blur", keypress.listen);
	$('input[type=password]').bind("focus", keypress.stop_listening);
	$('input[type=password]').bind("blur", keypress.listen);
	$('textarea').bind("focus", keypress.stop_listening);
	$('textarea').bind("blur", keypress.listen);
	
	// Bind click-on-status stuff
	$('body').on('click', '.status[status-id]', function(e) {
		var statusid = $(this).attr('status-id');
		var uniqueid = $(this).attr('unique-id');
		
		var replyblock = $(this).find('.reply-list[status-id=' + statusid + ']').first();
		
		if (replyblock.html() != '') {
			replyblock.html('');
		}
		else {
			$.ajax({
				type: 'GET',
				url: '/api/status/' + statusid + '/',
				success: function (data) {
					if (data.error != undefined) {
						alert(data.error);
					}
					else {
						replyblock.html(data.result);
					}
				}
			});
		}
		return false;
	});
	
	// Preventing anchors being unclickable
	$('body').on('click', '.status[status-id] a', function(e) {
		e.stopPropagation();
	});
	
	$('.fademeout').delay(7000).fadeOut(1000, function() {
		$(this).remove();
	});
	
	
	$('body').on('mouseover', '*[status-post-time]', function (e) {
		$(this).attr('title', new Date($(this).attr('status-post-time') * 1000));
	});
	
	$('#statusposter').submit(function () {
		$('#statusposter button[type="submit"]').attr('disabled', 'disabled');
		var data = $(this).serializeArray();
		var replyto = 'hirr';
		data.filter(function (a, b) { if (a.name == 'reply-to') replyto = a.value; });
		$.ajax({
			type: 'POST',
			url: '/api/status/post/',
			data: data,
			success: function (e) {
				if (e.errormsg != undefined) {
					AddMessageToContent('alert', 'An error occurred: ' + e.errormsg, '');
				}
				else {
					AddMessageToContent('info', 'Successfully posted status!', '');
					$('textarea[name="content"]').val(''); // Empty input
					$('select[name="usingface"]').val('');
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
					var url = char_image.css('background-image');
					url = url.replace(/face=([a-z]*)/g, 'face=angry');
					char_image.css('background-image', url);
					
					statusobject.fadeOut(2000, function() {
						$(this).remove();
					});
				}
			}
		});
		
		return false;
	});
	
	
	
	window.IsSyncing = false;
	window.syncer = function (requestOlder, syncbtn) {
		if (typeof syncbtn === 'undefined') syncbtn = false;
		if (!syncbtn && window.IsSyncing) return;
		if (syncbtn && $('#syncbutton').html() == 'Loading more statuses.....') return;
		if (syncbtn) {
			$('#syncbutton').html('Loading more statuses.....');
		}
		window.IsSyncing = true;
		var statuses = [];
		$('div[class~="status"][status-id]').each(function (index) {
			var statusid = parseInt($(this).attr('status-id'));
			if ($.inArray(statusid, statuses) == -1)
				statuses.push(statusid);
		});

		// $('*[status-post-time]').last()
		var request_data = { 'shown-statuses': statuses, 'client-time': serverTickCount, 'url': document.location.href, 'has-statuses': $('#statuslist').length > 0 ? 1 : 0 };
		if (requestOlder) {
			var oldeststatus = serverTickCount;
			$('*[status-post-time]').each(function () {
				if (oldeststatus > $(this).attr('status-post-time'))
					oldeststatus = $(this).attr('status-post-time');
			});
			
			request_data['client-time'] = oldeststatus;
			request_data['older-than'] = true;
		}
		else {
			var oldeststatus = serverTickCount;
			$('*[status-post-time]').each(function () {
				if (oldeststatus < $(this).attr('status-post-time'))
					oldeststatus = $(this).attr('status-post-time');
			});
			
			request_data['client-time'] = oldeststatus;
		}
		$.ajax({
			type: 'POST',
			url: '/ajax/sync/',
			data: request_data,
			success: function (e) {
				var newTitle = window.document.title;
				
				if (e.loggedin == false && window.location.pathname.indexOf('/stream/') != -1) {
					document.location = '/login';
				}
				
				if (newTitle.indexOf(') ') != -1) {
					newTitle = newTitle.substr(newTitle.indexOf(') ') + 2);
				}
				
				if (e.notifications > 0) {
					newTitle = '(' + e.notifications + ') ' + newTitle;
					//$('#notify span').get(0).firstChild.nodeValue = e.notifications;
				}
				
				if (e.membername != undefined)
					window.MemberName = e.membername;

				window.document.title = newTitle;
				
				if (typeof e.server_status !== 'undefined' && $('div[mapler-locale]').length > 0) {
					for (var idx in e.server_status) {
						var elem = e.server_status[idx];
						var status = elem.status;
						var info_element = $('div[mapler-locale="' + idx + '"]');
						if (status == 'offline') {
							info_element.find('span[offline-server]').css('display', '');
							info_element.find('span[online-server]').css('display', 'none');
						}
						else {
							info_element.find('span[offline-server]').css('display', 'none');
							info_element.find('span[online-server]').css('display', '');
							info_element.find('span[version]').html(elem.version);
							info_element.find('span[players]').html(elem.players);
						
						}
					}
				}
				
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
				
				if (e.statuses != undefined) {
					for (var index in e.statuses) {
						var status = e.statuses[index];

						var addAfter = $($('*[uniqueid]').get().reverse()).filter(function() {
							return $(this).attr('uniqueid') > status[2];
						});
						
						var statusAsHTML = $(status[1]);
						statusAsHTML.attr('uniqueid', status[2]);
						
						if (addAfter.length > 0) {
							statusAsHTML.insertAfter(addAfter.first().closest('div[class~="status"]'));
						}
						else {
							if (requestOlder)
								$('#statuslist').append(statusAsHTML);
							else
								$('#statuslist').prepend(statusAsHTML);
						}
					}
					
					if (e.statuses.length == 0 && serverTickCount == 0) {
						// New stuff. no stuff.
						$('#statuslist').html('<p class="lead alert alert-info">No statuses to show!</p>');
					}
				}
				
				serverTickCount = e.time;
				memberName = e.membername;
				memberStatuses = e.memberstatuses;
				
				$('#memberstatuses').html(memberStatuses + ' statuses');
				
				// Update posts

				$('*[status-post-time]').each(
					function (index) {
						$(this).html(time_elapsed_string(serverTickCount - $(this).attr('status-post-time')) + ' ago');
					}
				);
				
				window.IsSyncing = false;
				if (syncbtn) {
					if (requestOlder && e.statuses.length == 0)
						$('#syncbutton').html('Nothing to show you :(').fadeOut(1000);
					else
						$('#syncbutton').html('Load more statuses..');
				}
			}
		});
	};
	
	setInterval(function () { syncer(false); }, 2500);
	syncer(false);
});

var serverTickCount = 0;
var memberName = '';

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
		'minute'	: 60
	};

	for (var name in times) {
		var val = times[name];
		val = parseFloat(time / val);
		val = parseInt(Math.floor(val));
		if (val >= 1.0) {
			return val + ' ' + name + (val > 1 ? 's' : '');
		}
	}
	return 'less than a minute';
}

function ChangePostAvatarFace(newface) {
	var object = $('#statusposter .character');
	var url = object.css('background-image');
	url = url.replace(/face=([a-z]*)/g, 'face=' + newface);
	object.css('background-image', url);
}

jQuery("html").removeClass("no-js").addClass("js");
if (navigator.appVersion.indexOf("Mac") != -1) {
    jQuery("html").addClass("osx")
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
                        if (b.length > 3) {
                            a(".faq li").each(function () {
                                    var d = a(this).find("#searchfor").text();
                                    var e = a(this).find("#searchfor").text();
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
                            a(".faq li").hide()
                        }
                    })
            })()
            keypress.combo("n", function() {
			$('#post').modal({
				show: true
			})
			input.focus();
		    });
		    
		    keypress.combo("p", function() {
			    window.location.href = '//mapler.me/me/';
		    });
    });
    
(function(l){function t(l){return l.replace(/(:|\.)/g,"\\$1")}var e="1.4.11",o={exclude:[],excludeWithin:[],offset:0,direction:"top",scrollElement:null,scrollTarget:null,beforeScroll:function(){},afterScroll:function(){},easing:"swing",speed:400,autoCoefficent:2,preventDefault:!0},r=function(t){var e=[],o=!1,r=t.dir&&"left"==t.dir?"scrollLeft":"scrollTop";return this.each(function(){if(this!=document&&this!=window){var t=l(this);t[r]()>0?e.push(this):(t[r](1),o=t[r]()>0,o&&e.push(this),t[r](0))}}),e.length||this.each(function(){"BODY"===this.nodeName&&(e=[this])}),"first"===t.el&&e.length>1&&(e=[e[0]]),e};l.fn.extend({scrollable:function(l){var t=r.call(this,{dir:l});return this.pushStack(t)},firstScrollable:function(l){var t=r.call(this,{el:"first",dir:l});return this.pushStack(t)},smoothScroll:function(e){e=e||{};var o=l.extend({},l.fn.smoothScroll.defaults,e),r=l.smoothScroll.filterPath(location.pathname);return this.unbind("click.smoothscroll").bind("click.smoothscroll",function(e){var n=this,s=l(this),c=o.exclude,i=o.excludeWithin,a=0,f=0,h=!0,u={},d=location.hostname===n.hostname||!n.hostname,m=o.scrollTarget||(l.smoothScroll.filterPath(n.pathname)||r)===r,p=t(n.hash);if(o.scrollTarget||d&&m&&p){for(;h&&c.length>a;)s.is(t(c[a++]))&&(h=!1);for(;h&&i.length>f;)s.closest(i[f++]).length&&(h=!1)}else h=!1;h&&(o.preventDefault&&e.preventDefault(),l.extend(u,o,{scrollTarget:o.scrollTarget||p,link:n}),l.smoothScroll(u))}),this}}),l.smoothScroll=function(t,e){var o,r,n,s,c=0,i="offset",a="scrollTop",f={},h={};"number"==typeof t?(o=l.fn.smoothScroll.defaults,n=t):(o=l.extend({},l.fn.smoothScroll.defaults,t||{}),o.scrollElement&&(i="position","static"==o.scrollElement.css("position")&&o.scrollElement.css("position","relative"))),o=l.extend({link:null},o),a="left"==o.direction?"scrollLeft":a,o.scrollElement?(r=o.scrollElement,c=r[a]()):r=l("html, body").firstScrollable(),o.beforeScroll.call(r,o),n="number"==typeof t?t:e||l(o.scrollTarget)[i]()&&l(o.scrollTarget)[i]()[o.direction]||0,f[a]=n+c+o.offset,s=o.speed,"auto"===s&&(s=f[a]||r.scrollTop(),s/=o.autoCoefficent),h={duration:s,easing:o.easing,complete:function(){o.afterScroll.call(o.link,o)}},o.step&&(h.step=o.step),r.length?r.stop().animate(f,h):o.afterScroll.call(o.link,o)},l.smoothScroll.version=e,l.smoothScroll.filterPath=function(l){return l.replace(/^\//,"").replace(/(index|default).[a-zA-Z]{3,4}$/,"").replace(/\/$/,"")},l.fn.smoothScroll.defaults=o})(jQuery);