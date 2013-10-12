var lastSetWindow = null;

function SetItemInfo(event, obj, values) {
	var reqs = values.requirements;
	var item = values.iteminfo;
	var otherinfo = values.other_info;
	var isequip = item.inventory == 0;
	var itemid = item.itemid;
	var gender = parseInt(itemid / 1000) % 10;
	var haspotential = false;
	var hasbonuspotential = false;
	var hasnebulite = false;
	var itemslots = isequip ? GetItemSlotsById(itemid) : [0];
	
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
	
	var PadReqStat = function (val) {
		if (val == undefined) return undefined;
		var tmp = val.toString();
		for (var i = tmp.length; i < 3; i++)
			tmp = '0' + tmp;
		return tmp;
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
			value = (value < 0 ? '-' : '+') + value;
			if (diff > 0) {
				//	Added
				value += ' <span class="stat-diff-info">(' + def_value + ' + ' + diff + ')</span>';
				GetObj('item_info_row_' + name).style.color = 'cyan';
			}
			else if (diff < 0) {
				// Lost
				//value += ' <span class="stat-diff-info">(' + def_value + ' - ' + -diff + ')</span>'; // - --10 = - 10 wooop
				GetObj('item_info_row_' + name).style.color = '';
			}
		}
		GetObj('item_info_' + name).innerHTML = value;
	};
	
	var SetObjTextIsEquip = function(name, value) {
		var object = $('.req-block div[type="' + name + '"]');
		
		if (!isequip) {
			object.hide();
		}
		else {
			object.show();
			
			var digit1 = Math.floor(value / 100);
			var digit2 = Math.floor(value / 10) % 10;
			var digit3 = value % 10;
			var digits = object.find('div.digit');
			digits.eq(2).attr('nr', digit1);
			digits.eq(1).attr('nr', digit2);
			digits.eq(0).attr('nr', digit3);
			
			digits = digits.add(object);
			
			if (name == 'level') {
				if (value != 0)
					digits.attr('yellow', 'nope');
				else
					digits.removeAttr('yellow');
			}
			else {
			
				if (value != 0)
					digits.removeAttr('disabled');
				else
					digits.attr('disabled', 'nope');
			}
		}
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
	
	if (isequip && (gender == 0 || gender == 1) && Math.floor(itemid / 10000) != 168 /* Bits test */) {
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
	var desc = 'Category:';
	if (itemslots[0] == 11) {
		// Itz a weapon
		wepcatname = GetWeaponCategoryName(itemid);
	}
	else {
		wepcatname = GetItemCategory(itemid);
	}
	SetObjText('weaponcategory', wepcatname, undefined, false);
	$('#item_info_row_weaponcategory > span').first().html(desc);
	
	$('#item_info > .dotline').first().css('display', isequip ? 'block' : 'none');
	
	
	SetObjTextIsEquip('level', reqs.level);
	SetObjTextIsEquip('str', reqs.str);
	SetObjTextIsEquip('dex', reqs.dex);
	SetObjTextIsEquip('int', reqs.int);
	SetObjTextIsEquip('luk', reqs.luk);
	//SetObjTextIsEquip('reqpop', reqs.pop);
	//SetObjTextIsEquip('itemlevel', item.itemlevel);
	//SetObjTextIsEquip('itemexp', item.itemexp);

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
	
	var state = item.statusflag & 0xFF;
	var stars = (item.statusflag >> 8) & 0xFF;
	SetObjText('enchantments', stars, undefined, isequip);
	
	GetObj('item_stats_block').style.display = isequip && hasStatsSet ? 'block' : 'none';
	
	var description = RequestItemInfo('description', itemid);
	if (description != '') description = '<span>' + description + '</span>';
	var description_place = isequip ? 'extra_item_info' : 'item_info_description';
	GetObj('extra_item_info').style.display = 'none';
	GetObj('extra_item_info').innerHTML = '';
	GetObj('item_info_description').style.display = 'none';
	GetObj('item_info_description').innerHTML = '';
	
	
	GetObj('potentials').innerHTML = ''; // Clear potentials
	GetObj('bonus_potentials').innerHTML = ''; // Clear potentials
	GetObj('nebulite_info').innerHTML = ''; // Clear nebulite info
	
	// Get potential info
	if (isequip) {
		if ((item.statusflag & 0x0001) != 0 && item.potential1 == 0 && item.potential2 == 0 && item.potential3 == 0) {
			var row = GetObj('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td width="150px">Hidden Potential.</td> </tr>';
			haspotential = true;
		}
	}

	var extrainfo = '';
	// extrainfo += '<span>Quality: ' + otherinfo.quality + '</span>';

	
	if (isequip) {
		
		if ((state & 0x07) != 0) {
			var itemstatename = '';
			switch (state & 0x07) {
				case 1: itemstatename = 'Rare'; break;
				case 2: itemstatename = 'Epic'; break;
				case 3: itemstatename = 'Unique'; break;
				case 4: itemstatename = 'Legendary'; break;
			}
			extrainfo += '<span style="color: white">(' + itemstatename + ' Item)</span>';
			
		}
		if (item.max_scissors == 0) {
			
		}
	}
	
	if (haspotential)
		extrainfo += '<span style="color: #FF0066;">(Hidden Potential Item)</span>';

	if (otherinfo.oneofakind)
		extrainfo += '<span>One of a Kind</span>';

	if (otherinfo.questitem)
		extrainfo += '<span>Quest item</span>';

	if (otherinfo.expires != '')
		if (otherinfo.expires == 'Permanent') {
			extrainfo += '<span>Permanent Item</span>';
		}
		else {
			if (otherinfo.locked)
				extrainfo += '<span>Sealed untill ' + otherinfo.expires + '</span>';
			else if (itemid > 5000000 && itemid < 5010000)
				extrainfo += '<span>Water of Life dries up on ' + otherinfo.expires + '</span>';
			else
				extrainfo += '<span>Expires on ' + otherinfo.expires + '</span>';
		}
	if (otherinfo.skiped)
		extrainfo += '<span>Prevents slipping</span>';
	if (otherinfo.coldprotection)
		extrainfo += '<span>Cold prevention</span>';
	if (otherinfo.tradeblock) {
		extrainfo += '<span>' + (otherinfo.tradeblock == 0x30 ? 'Trade disabled when equipped' : 'Untradable') + '</span>';
		var tradeInfo = '';
		switch (otherinfo.tradeblock) {
			case 0x10: tradeInfo = 'Use the Sharing Tag to move an item to another character on the same account once.'; break;
			case 0x20: tradeInfo = 'Use the Scissors of Karma to enable an item to be traded one time'; break;
			case 0x21: tradeInfo = 'Use the Platinum Scissors of Karma to enable an item to be traded one time'; break;
			case 0x30: tradeInfo = 'Can be traded once within an account (Cannot be traded after being moved)'; break;
		}
		if (tradeInfo != '') {
			description += '<span style="color: orange;">' + tradeInfo + '</span>';
		}
	}
	if (otherinfo.karmad)
		extrainfo += '<span>1 time trading (karma\'d)</span>';

	//description += '<span>ITEMID ' + itemid + '</span>';
	//description += '<span>Type ' + GetItemCategory(itemid) + '</span>';

	if (item.name != '' && item.name != undefined) {
		if (item.moreflags != undefined && item.moreflags.indexOf('crafted') != -1)
			description += '<span style="color: limegreen;">- Crafted by: ' + item.name + '</span>';
	}

	GetObj('item_info_extra').innerHTML = extrainfo;
	GetObj('item_info_extra').style.display = extrainfo == '' ? 'none' : 'block';
	
	if (description != '') {
		GetObj(description_place).style.display = '';
		if (isequip)
			description = '<div class="dotline"></div>' + description;
		GetObj(description_place).innerHTML = description;
		
	}

	// Classes

	var reqjob = reqs.job;
	if (reqjob == 0) reqjob = 255; // All classes
	SetJob(0, reqjob, 0x80); // Beginner
	SetJob(1, reqjob, 0x01); // Warrior
	SetJob(2, reqjob, 0x02); // Magician
	SetJob(3, reqjob, 0x04); // Bowman
	SetJob(4, reqjob, 0x08); // Thief
	SetJob(5, reqjob, 0x10); // Pirate
	
	
	

	var potentiallevel = Math.round(reqs.level / 10);
	if (potentiallevel == 0) potentiallevel = 1;
	potentiallevel += stars;
	
	
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
		if ((item.statusflag & 0x0020) != 0) { // Note that this is the correct code. lol
			var row = GetObj('bonus_potentials').insertRow(-1);
			row.innerHTML = '<tr> <td width="150px" style="color: orange;">Hidden(?) Bonus Potential.</td> </tr>';
			hasbonuspotential = true;
		}
		
		// GMS has only 1 neb, but can hold 3 lol.
		if ((item.statusflag & 0x0002) == 0x0002 || (item.statusflag & 0x0004) == 0x0004 || (item.statusflag & 0x0008) == 0x0008) { // Neb slot 1,2 or 3 open
			GetObj('nebulite_info').innerHTML = '<span style="color: blue">You can mount a nebulite on this item</span>';
			hasnebulite = true;
		}
		if ((item.statusflag & 0x0010) != 0 && item.nebulite1 > 0) {
			var nebuliteinfo = RequestItemInfo('nebuliteinfo', item.nebulite1);
		
			var text = ReplaceIGNText(nebuliteinfo.description, nebuliteinfo.info);
			GetObj('nebulite_info').innerHTML += '<span style="color: green">[' + GetNebuliteType(item.nebulite1) + '] ' + text + '</span>';
			hasnebulite = true;
		}
		if ((item.statusflag & 0x0020) != 0 && item.nebulite2 > 0) {
			var nebuliteinfo = RequestItemInfo('nebuliteinfo', item.nebulite2);
		
			var text = ReplaceIGNText(nebuliteinfo.description, nebuliteinfo.info);
			GetObj('nebulite_info').innerHTML += '<span style="color: green">[' + GetNebuliteType(item.nebulite1) + '] ' + text + '</span>';
			hasnebulite = true;
		}
		if ((item.statusflag & 0x0040) != 0 && item.nebulite3 > 0) {
			var nebuliteinfo = RequestItemInfo('nebuliteinfo', item.nebulite3);
		
			var text = ReplaceIGNText(nebuliteinfo.description, nebuliteinfo.info);
			GetObj('nebulite_info').innerHTML += '<span style="color: green">[' + GetNebuliteType(item.nebulite1) + '] ' + text + '</span>';
			hasnebulite = true;
		}

		for (var i = 1; i <= 6; i++) {
			var isbonus = i >= 4;
			var potentialid = item['potential' + i];
			if (potentialid == 0) continue;
			var potentialinfo = RequestItemInfo('potentialinfo', potentialid); // potentialDescriptions[potentialid];
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


window.request_cache = [];
function RequestItemInfo(what, id) {
	if (typeof window.request_cache[what] === 'undefined')
		window.request_cache[what] = [];
	if (typeof window.request_cache[what][id] !== 'undefined')
		return window.request_cache[what][id];
	var ret = JSON.parse(
		$.ajax({
			type: 'GET',
			url: '/api/item/' + what + '/' + id + '/',
			async: false
		}).responseText
	);
	if (ret.result != undefined)
		ret = ret.result;
	else
		ret = '';
	window.request_cache[what][id] = ret;
	return ret;
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
	
	$('div.req_job[job="' + id + '"]').attr('able', correct ? 1 : 2);

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
		case 21: return 'Shining Rod';
		case 22: return 'Soul Shooter';
		case 23: return 'Desperado';
		case 24: return 'Whip Blade';
		case 30: return 'One-handed Sword';
		case 31: return 'One-handed Axe';
		case 32: return 'One-handed Mace';
		case 33: return 'Dagger';
		case 34: return 'Katara';
		case 35: return 'Orb';
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
		case 53: return 'Hand Cannon';
		case 54: return 'Katana';
		case 55: return 'Fan';
		case 70: return undefined;
		default: return 'FIXMEH: ' + catid;
	}

}

function GetItemSlotsById(itemid) {
	var itemcatid = Math.floor(itemid / 10000);
	switch (itemcatid) {
		case 100: return [1, 1200, 1300];
		case 101: return [2, 1202, 1302];
		case 102: return [3];
		case 103: return [4];
		case 104:
		case 105: return [5, 1203];
		case 106: return [6, 1204];
		case 107: return [7, 1205];
		case 108: return [8, 1206, 1304];
		case 109:
		case 134:
		case 135: return [10];
		case 110: return [9, 1201, 1301];
		case 111: return [12, 13, 15, 16];
		case 112: return [17, 65];
		case 113: return [50];
		case 114: return [49];
		case 115: return [51];
		case 116: return [52];
		case 117: return [55];
		case 118: return [56];
		
		case 161: return [1100];
		case 162: return [1101];
		case 163: return [1102];
		case 164: return [1103];
		case 165: return [1104];
		case 166: return [53]; // 117?
		case 167: return [54];
		case 119: return [61];
		case 168: {
			var ret = [];
			for (var i = 1500; i < 1525; i++)
				ret.push(i);
			return ret;
		}
		case 190: return [18];
		case 191: return [19];
		case 192: return [20];
		case 194: return [1000];
		case 195: return [1001];
		case 196: return [1002];
		case 197: return [1003];
		
		case 184: return [5100];
		case 185: return [5102];
		case 186: return [5103];
		case 187: return [5104];
		case 188: return [5101];
		case 189: return [5105];
		
		case 180:
			if (itemid == 1802100)
				return [21, 31, 39];
			else
				return [14, 30, 38];
		case 181:
			switch (itemid) {
				case 1812000: return [23, 34, 42];
				case 1812001: return [22, 33, 41];
				case 1812002: return [24];
				case 1812003: return [25];
				case 1812004: return [26, 35, 43];
				case 1812005: return [27, 36, 44];
				case 1812006: return [28, 37, 45];
				case 1812007: return [46, 47, 48];
				case 1812008: return [57, 58, 59];
				case 1812009: return [60];
				case 1812010: return [62, 63, 64];
			}
		case 182: return [21, 31, 39];
		case 183: return [29, 32, 40];
		case 120: return [5000, 5001, 5002];
		default: return [11]; 
	}
}

function GetItemCategory(itemid) {
	var itemcatid = Math.floor(itemid / 10000);
	if (itemcatid == 150)
		return 'Herbalism Tool';
	else if (itemcatid == 151)
		return 'Mining Tool';
	
	var slotid = GetItemSlotsById(itemid);
	switch (slotid[0]) {
		case 1: return 'Hat';
		case 2: return 'Face Accessory';
		case 3: return 'Eye Accessory';
		case 4: return 'Earring';
		case 5: 
			if (itemcatid == 105)
				return 'Overall';
			else
				return 'Top';
		case 6: return 'Bottom';
		case 7: return 'Shoes';
		case 8: return 'Glove';
		case 9: return 'Cape';
		case 10: 
			if (itemcatid == 134)
				return 'Katara';
			else if (itemcatid == 135) {
				var uwotm8 = itemid - 1350000;
				if (uwotm8 < 2100)
					return 'Magic Arrow';
				else if (uwotm8 >= 2100 && uwotm8 < 2200)
					return 'Card';
				else if (uwotm8 >= 2300 && uwotm8 < 2400)
					return 'Core';
				else if (uwotm8 >= 2400 && uwotm8 < 2500)
					return 'Orb';
				else if (uwotm8 >= 2500 && uwotm8 < 2600)
					return 'Dragon Essence';
				else if (uwotm8 >= 2600 && uwotm8 < 2700)
					return 'Soul Ring';
				else if (uwotm8 >= 2700 && uwotm8 < 2800)
					return 'Magnum'; // Ijsjuh
				else if (uwotm8 >= 2800 && uwotm8 < 2900)
					return 'Kodachi';
					
				uwotm8 = Math.floor(itemid / 10);
				switch (uwotm8) {
					case 135220: return 'Medallions';
					case 135221: return 'Rosary';
					case 135222: return 'Iron Chain';
					
					case 135223:
					case 135224:
					case 135225: return 'Magic Book';
					
					case 135226: return 'Arrow Fletching';
					case 135227: return 'Bow Thimble';
					case 135228: return 'Dagger Scabbard';
					case 135229: return 'Charm';
					
					case 135290: return 'Wrist Band';
					case 135291: return 'Far Sight';
					case 135292: return 'Powder Keg';
					case 135293: return 'Mass';
					case 135294: return 'Document';
					case 135295: return 'Magic Marble';
					case 135296: return 'Arrowhead';
					case 135297: return 'Jewel';
				}
				
				if (Math.floor(itemid / 1000) == 1353)
					return 'Controller';
			}
			else if (Math.floor(itemid / 1000) == 1099)
				return 'Demon Aegis';
			else if (Math.floor(itemid / 1000) == 1098)
				return 'Soul Shield';
			else if (Math.floor(itemid / 1000) == 1099)
				return 'Demon Aegis';
			
			return 'Shield'; // hurr
		case 11: return 'Weapon';

		case 12: return 'Ring';
		case 17: return 'Pendant';
		case 18: return 'Tamed Monster';
		case 19: return 'Saddle';
		case 20: return 'Monster Equip';
		case 49: return 'Medal';
		case 50: return 'Belt';
		case 51: return 'Shoulder Decoration';
		case 52: return 'Pocket Item';
		case 53: return 'Android';
		case 54: return 'Mechanical Heart';
		case 55: return 'Codex';
		case 56: return 'Badge';
		case 61: 
			if (Math.floor(itemid / 100) == 11902) 
				return 'Power Source';
			else
				return 'Emblem';
	
		case 1001: return 'Dragon Pendant';
		case 1002: return 'Dragon Wing Accesory';
		case 1003: return 'Dragon Tail Accesory';
		
		case 1104: return 'Mechanic Transistor';
		case 1100: return 'Mechanic Engine';
		case 1101: return 'Mechanic Arm';
		case 1102: return 'Mechanic Leg';
		case 1103: return 'Mechanic Frame';
		case 1500: return 'Bits';
		
		case 5000: 
		case 5001: 
		case 5002: return 'Totem';
		
		// Pets
		case 14:
		case 22:
		case 23:
		case 24:
		case 25:
		case 26:
		case 27:
		case 28:
		case 30:
		case 33:
		case 34:
		case 35:
		case 36:
		case 37:
		case 38:
		case 41:
		case 42:
		case 43:
		case 44:
		case 45:
		case 46:
		case 47:
		case 48:
		case 57:
		case 58:
		case 59:
		case 60:
		case 62:
		case 63:
		case 64:
			return 'Pet Equip';
		case 21:
		case 29:
		case 31:
		case 32:
		case 39:
		case 40:
			return 'Pet Ring';
		default: return 'Dunno :(. Please report if you do!';
	}
}

function GetItemCategoryOLD(islot) {
	switch (islot) {
		case 'Ba': return 'Badge';
		case 'Be': return 'Belt';
		case 'Bi': return 'Bit';
		case 'Cp': return 'Cap';
		case 'Fc': return 'Face';
		case 'Gv': return 'Glove';
		case 'Hr': return 'Hair';
		case 'HrCp': return 'Hair Cap';
		case 'Ma': return 'Overall';
		case 'Mb': return 'Monsterbook'; // Must say which set you chose actually... 'Selecting [name]'
		case 'Me': return 'Medal';
		case 'Pe': return 'Necklace'; // Pendant?
		case 'Pa': return 'Pants';
		case 'Po': return 'Totem'; // Or Pocket?
		case 'Ri': return 'Ring';
		case 'Sd': return 'Saddle';
		case 'Sh': return 'Shoulder Decoration';
		case 'Si': return 'Shield';
		case 'Tm': return 'Taming Mob';
		case 'Wp': return 'Weapon';
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