var lastSetWindow = null;

function SetItemInfo(event, obj, values) {
	var reqs = values.requirements;
	var item = values.iteminfo;
	var otherinfo = values.other_info;
	var isequip = item.inventory == 0;
	var itemid = item.itemid;
	var gender = parseInt(itemid / 1000) % 10;
	
	var GetObj = function (val) {
		return document.getElementById(val);
	};
	
	var GetQualityColor = function (q) {
		switch (q) {
			case -1: return '#BCBCBC';
			case 0: return '';
			case 1: 
				if (gender == 0 || gender == 1)
					return '#188AFF';
				else
					return '#FFA15C'; // Standard color
			case 2: return '#FF61C2';
			case 3: return '#14F5FD';
			case 4: return '#00FF37';
			case 5: return '#7F00FF';
		}
	};
	
	var hasStatsSet = false;
	
	var SetObjText = function(name, value, def_value, ignoreZero) {
		var isSet = !(value == '' || value == undefined);
		if (ignoreZero && value == 0)
			isSet = true;

		if (isSet)
			hasStatsSet = true;
		GetObj('item_info_row_' + name).style.display = isSet ? '' : 'none';
		GetObj('item_info_row_' + name).style.color = '';
		if (isSet && typeof def_value !== 'undefined') {
			var diff = value - def_value;
			if (diff > 0) {
				//	Added
				value += ' (' + def_value + ' + ' + diff + ')';
				GetObj('item_info_row_' + name).style.color = 'limegreen';
			}
			else if (diff < 0) {
				// Lost
				value += ' (' + def_value + ' - ' + -diff + ')'; // - --10 = - 10 wooop
				GetObj('item_info_row_' + name).style.color = 'orange';
			}
		}
		GetObj('item_info_' + name).innerHTML = value;
	};
	
	var SetObjTextIsEquip = function(name, value) {
		GetObj('item_info_req_row_' + name).style.display = !isequip && (value == 0 || value == '' || value == undefined) ? 'none' : '';
		GetObj('item_info_req_' + name).innerHTML = value;
	};

	GetObj('item_info_title').innerHTML = obj.getAttribute('item-name');
	
	if (item.itemid >= 5000000 && item.itemid < 5010000) { // is pet
		var nametoshow = item.name;
		if (nametoshow != obj.getAttribute('item-name'))
			nametoshow += ' (' + obj.getAttribute('item-name') + ')';
		GetObj('item_info_title').innerHTML = nametoshow;
	}
	
	if (isequip && item.scrolls != 0) {
		GetObj('item_info_title').innerHTML += ' (+' + item.scrolls + ')';
	}
	
	if (isequip && (gender == 0 || gender == 1)) {
		GetObj('item_info_title').innerHTML += ' (' + (gender == 0 ? 'Male' : 'Female') + ')';
	}
	
	GetObj('item_info_title').style.color = GetQualityColor(otherinfo.quality);
	
	
	var iconinfo = $(obj).find('img');
	var popupicon = $('#item_info_icon');
	popupicon.attr('src', iconinfo.attr('src'));
	
	var offset_top = 0, offset_left = 0, size_rate = (72 / 32);
	offset_left = parseFloat(iconinfo.css('margin-left')); // Original Offsets
	offset_top = parseFloat(iconinfo.css('margin-top'));
	offset_left *= size_rate;
	offset_top *= size_rate;
	
	popupicon.css('margin-left', offset_left + 'px');
	popupicon.css('margin-top', offset_top + 'px');
	popupicon.attr('width', iconinfo.width() * size_rate);
	popupicon.attr('height', iconinfo.height() * size_rate);
	
	
	var wepcatname = 0;
	if (iconinfo.attr('src').indexOf('Weapon/') != -1) {
		// Itz a weapon
		wepcatname = GetWeaponCategoryName(itemid);
	}
	SetObjText('weaponcategory', wepcatname, undefined, false);
	
	
	
	SetObjTextIsEquip('reqlevel', reqs.level);
	SetObjTextIsEquip('reqstr', reqs.str);
	SetObjTextIsEquip('reqdex', reqs.dex);
	SetObjTextIsEquip('reqint', reqs.int);
	SetObjTextIsEquip('reqluk', reqs.luk);
	SetObjTextIsEquip('reqpop', reqs.pop);
	SetObjTextIsEquip('itemlevel', item.itemlevel);
	SetObjTextIsEquip('itemexp', item.itemexp);

	$('.item_req_stats').css('display', !isequip && $('.item_req_stats > span[style=""]').length == 0 ? 'none' : 'block');
	
	
	SetObjText('str', item.str, values.default_stats.str, false);
	SetObjText('dex', item.dex, values.default_stats.dex, false);
	SetObjText('int', item.int, values.default_stats.int, false);
	SetObjText('luk', item.luk, values.default_stats.luk, false);
	
	SetObjText('maxhp', item.maxhp, values.default_stats.maxhp, false);
	SetObjText('maxmp', item.maxmp, values.default_stats.maxmp, false);
	SetObjText('weaponatt', item.weaponatt, values.default_stats.weaponatt, false);
	SetObjText('weapondef', item.weapondef, values.default_stats.weapondef, false);
	SetObjText('magicatt', item.magicatt, values.default_stats.magicatt, false);
	SetObjText('magicdef', item.magicdef, values.default_stats.magicdef, false);
	SetObjText('acc', item.acc, values.default_stats.acc, false);
	SetObjText('avo', item.avo, values.default_stats.avo, false);
	SetObjText('jump', item.jump, values.default_stats.jump, false);
	SetObjText('speed', item.speed, values.default_stats.speed, false);
	SetObjText('hands', item.hands, values.default_stats.hands, false);
	SetObjText('slots', item.slots, undefined, isequip);
	SetObjText('hammers', item.hammers, undefined, isequip);
	
	var stars = parseInt(item.statusflag / 0x100);
	SetObjText('enchantments', stars, undefined, isequip);
	
	GetObj('item_stats_block').style.display = isequip && hasStatsSet ? 'block' : 'none';
	
	var description = descriptions[itemid];

	if (description != undefined) {
		GetObj('item_info_description').style.display = '';
		GetObj('item_info_description').innerHTML = description;
	}
	else {
		GetObj('item_info_description').style.display = 'none';
	}

	var extrainfo = '';
	// extrainfo += '<span>Quality: ' + otherinfo.quality + '</span>';

	if (otherinfo.oneofakind)
		extrainfo += '<span>One of a Kind</span>';

	if (otherinfo.questitem)
		extrainfo += '<span>Quest item</span>';

	if (otherinfo.locked)
		extrainfo += '<span>Sealed untill ' + otherinfo.expires + '</span>';
	else if (otherinfo.expires != '')
		extrainfo += '<span>Expires on ' + otherinfo.expires + '</span>';
	if (otherinfo.skiped)
		extrainfo += '<span>Prevents slipping</span>';
	if (otherinfo.coldprotection)
		extrainfo += '<span>Cold prevention</span>';
	if (otherinfo.tradeblock) {
		var tradeInfo = 'Untradable';
		switch (otherinfo.tradeblock) {
			case 0x10: tradeInfo = 'Use the Sharing Tag to move an item to another character on the same account once.'; break;
			case 0x20: tradeInfo = 'Use the Scissors of Karma to enable an item to be traded one time'; break;
			case 0x21: tradeInfo = 'Use the Platinum Scissors of Karma to enable an item to be traded one time'; break;
			case 0x30: tradeInfo = 'Trade disabled when equipped'; break;
			case 0x10: tradeInfo = 'Can be traded once within an account (Cannot be traded after being moved)'; break;
		}
		extrainfo += '<span>' + tradeInfo + '</span>';
	}
	if (otherinfo.karmad)
		extrainfo += '<span>1 time trading (karma\'d)</span>';

	//extrainfo += '<span>ITEMID ' + itemid + '</span>';


	GetObj('item_info_extra').innerHTML = extrainfo;
	GetObj('item_info_extra').style.display = extrainfo == '' ? 'none' : 'block';

	// Classes

	var reqjob = reqs.job;
	if (reqjob == 0) reqjob = 255; // All classes
	SetJob(0, reqjob, 0x80); // Beginner
	SetJob(1, reqjob, 0x01); // Warrior
	SetJob(2, reqjob, 0x02); // Magician
	SetJob(3, reqjob, 0x04); // Bowman
	SetJob(4, reqjob, 0x08); // Thief
	SetJob(5, reqjob, 0x10); // Pirate
	
	
	
	
	GetObj('potentials').innerHTML = ''; // Clear potentials
	GetObj('bonus_potentials').innerHTML = ''; // Clear potentials
	GetObj('nebulite_info').innerHTML = ''; // Clear nebulite info

	var potentiallevel = Math.round(reqs.level / 10);
	if (potentiallevel == 0) potentiallevel = 1;
	potentiallevel += stars;
	
	var haspotential = false;
	var hasbonuspotential = false;
	var hasnebulite = false;
	
	var GetNebuliteType = function (itemid) {
		var val = parseInt(itemid / 1000);
		switch (val) {
			case 0: return 'D';
			case 1: return 'C';
			case 2: return 'B';
			case 3: return 'A';
			case 4: return 'S';
			default: return '? ' + val;
		}
	};
	
	if (isequip) {
		if ((item.statusflag & 0x0001) != 0) {
			var row = GetObj('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td width="150px">Hidden Potential.</td> </tr>';
			haspotential = true;
		}
		if ((item.statusflag & 0x0020) != 0 && item.potential4 == 0 && item.potential5 == 0 && item.potential6 == 0) { // Note that this is the correct code. lol
			var row = GetObj('bonus_potentials').insertRow(-1);
			row.innerHTML = '<tr> <td width="150px">Hidden Bonus Potential.</td> </tr>';
			hasbonuspotential = true;
		}
		
		if (item.nebulite1 == 0) {
			GetObj('nebulite_info').innerHTML = '<span style="color: blue">You can mount a nebulite on this item</span>';
			hasnebulite = true;
		}
		else if (item.nebulite1 > 0) {
			var nebuliteinfo = nebuliteInfo[item.nebulite1];
		
			var text = ReplaceIGNText(nebuliteinfo.description, nebuliteinfo.info);
			GetObj('nebulite_info').innerHTML = '<span style="color: green">[' + GetNebuliteType(item.nebulite1) + '] ' + text + '</span>';
			hasnebulite = true;
		}

		for (var i = 1; i <= 6; i++) {
			var isbonus = i >= 4;
			var potentialid = item['potential' + i];
			if (potentialid == 0) continue;
			var potentialinfo = potentialDescriptions[potentialid];
			if (potentialinfo.name == null) continue;
			
			if (isbonus) hasbonuspotential = true;
			else haspotential = true;
			
			var maxlevel = GetMaxPotentialLevel(potentialinfo.levels);
			var level = potentiallevel > maxlevel ? maxlevel : potentiallevel;

			var leveldata = potentialinfo.levels[level];

			var text = ReplaceIGNText(potentialinfo.name, leveldata);

			var row = GetObj(isbonus ? 'bonus_potentials' : 'potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + text + '</td> </tr>';
		}
	}


	GetObj('item_info_potentials').style.display = isequip && haspotential ? 'block' : 'none';
	GetObj('item_info_bonus_potentials').style.display = isequip && hasbonuspotential ? 'block' : 'none';
	GetObj('item_nebulite_info_block').style.display = isequip && hasnebulite ? 'block' : 'none';

	var potentialName = obj.getAttribute('potential');
	GetObj('item_info').setAttribute('class', potentialName != null ? 'potential' + potentialName : '');

	GetObj('extra_item_info').innerHTML += '';
	
	var starstext = '';
	var i = 0;
	for (; i < stars; i++)
		starstext += (i % 5 == 0 && i != 0 ? '&nbsp;' : '') + '<img src="/inc/img/ui/Item/Equip/Star/Star.png" />';
	
	GetObj('item_info_stars').innerHTML = starstext;

	
	GetObj('item_info').style.display = 'block';
	GetObj('req_job_list').style.display = isequip ? 'block' : 'none';
	
	lastSetWindow = GetObj('item_info');
	
	MoveWindow(event);
}

function GetMaxPotentialLevel(potentialLevels) {
	for (var i = 1; ; i++) {
		if (potentialLevels[i] == undefined)
			return i - 1;
	}
	return 1; // wut
}

function ReplaceIGNText(input, strings) {
	for (var str in strings) {
		input = input.replace('#' + str, strings[str]);
	}
	return input;
}

function SetJob(id, flag, neededflag) {
	var correct = (flag & neededflag) == neededflag;
	if (neededflag == 0x80 && flag != 255)
		correct = false;
	document.getElementById('item_info_reqjob_' + id).setAttribute("class", "req_job" + (correct ? ' needed_job' : ''));

}

function HideItemInfo() {
	document.getElementById('item_info').style.display = 'none';
}

function MoveWindow(event) {
	var expectedTop = event.pageY + 10;
	var expectedBottom = expectedTop + parseInt(lastSetWindow.clientHeight) + 10;
	var screenBottom = $(window).height() + window.scrollY;
	if (screenBottom < expectedBottom) {
		expectedTop -= (expectedBottom - screenBottom);
	}
	
	var expectedLeft = event.pageX + 10;
	var expectedRight = expectedLeft + parseInt(lastSetWindow.clientWidth) + 10;
	var screenRight = $(window).width() + window.scrollX;
	if (screenRight < expectedRight) {
		expectedLeft -= (expectedRight - screenRight);
	}
	
	lastSetWindow.style.top = expectedTop + 'px';
	lastSetWindow.style.left = expectedLeft + 'px';
}

function ShowCashEquips(show) {
	document.getElementById('normal_equips').style.display = show ? 'none' : 'block';
	document.getElementById('cash_equips').style.display = !show ? 'none' : 'block';
}





var lastid = -1;
function ChangeInventory(id) {
	id -= 1;
	if (lastid != -1)
		document.getElementById('inventory_' + lastid).style.display = 'none';
	lastid = id;
	document.getElementById('inventory_' + lastid).style.display = 'block';
}

var lastidskill = -1;
function ChangeSkillList(id) {
	if (lastidskill != -1) {
		document.getElementById('bookname_' + lastidskill).style.display = 'none';
		document.getElementById('skilllist_' + lastidskill).style.display = 'none';
		document.getElementById('skillsp_' + lastidskill).style.display = 'none';
	}
	lastidskill = id;
	document.getElementById('bookname_' + lastidskill).style.display = 'block';
	document.getElementById('skilllist_' + lastidskill).style.display = 'block';
	document.getElementById('skillsp_' + lastidskill).style.display = 'block';
}

var lastpet = -1;
function ChangePet(id) {
	if (lastpet != -1) {
		document.getElementById('pet_' + lastpet).style.display = 'none';
	}
	lastpet = id;
	document.getElementById('pet_' + lastpet).style.display = 'block';
}

function GetWeaponCategoryName(id) {
	var catid = Math.floor(id / 10000) % 100;
	
	switch (catid) {
		case 21: return 'Magic Wand';
		case 22: return 'Soul Shooter';
		case 30: return 'One-handed Sword';
		case 31: return 'One-handed Axe';
		case 32: return 'One-handed Mace';
		case 33: return 'Dagger';
		case 34: return 'Katara';
		case 36: return 'Cane';
		case 37: return 'Wand';
		case 38: return 'Staff';
		case 40: return 'Two-handed Sword';
		case 41: return 'Two-handed Axe';
		case 42: return 'Two-handed Mace';
		case 43: return 'Spear';
		case 44: return 'Pole Arm';
		case 45: return 'Bow';
		case 46: return 'Crossbow';
		case 47: return 'Claw';
		case 48: return 'Knuckle';
		case 49: return 'Gun';
		case 50: return 'Herbalism tool';
		case 51: return 'Mining tool';
		case 52: return 'Dual Bowgun';
		case 53: return 'Canon';
		case 70: return undefined;
		default: return 'FIXMEH: ' + catid;
	}

}

$(document).ready(function () {
	ChangeInventory(1);
	ChangeSkillList(1);
	ChangePet(0);
	ShowCashEquips(false);
	
	
	$('.visibility-toggler').change(function (e) {
		var clickedObj = $(this);
		var name = clickedObj.attr('name');
		var option = clickedObj.attr('option');
		var ishidden = clickedObj[0].checked;
		$.ajax({
			url: '/api/character/visibility',
			type: 'GET',
			data: {name: name, what: option, shown: !ishidden},
			success: function (data) {
				clickedObj.checked = !ishidden;
			}
		});
	});
});


function ToggleTogglers() {
	$('.visibility-toggler').each(function (elem) { 
		$(this).toggle();
	});
}