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
	
	var SetObjText = function(name, value, ignoreZero) {
		if (ignoreZero == undefined) ignoreZero = false;
		var isSet = !(value == '' || value == undefined);
		if (ignoreZero && value == 0)
			isSet = true;

		if (isSet)
			hasStatsSet = true;
		GetObj('item_info_row_' + name).style.display = isSet ? '' : 'none';
		GetObj('item_info_' + name).innerHTML = value;
	};
	
	var SetObjTextIsEquip = function(name, value) {
		GetObj('item_info_req_row_' + name).style.display = !isequip && (value == 0 || value == '' || value == undefined) ? 'none' : '';
		GetObj('item_info_req_' + name).innerHTML = value;
	};

	GetObj('item_info_title').innerHTML = obj.getAttribute('item-name');
	if (isequip && item.scrolls != 0) {
		GetObj('item_info_title').innerHTML += ' (+' + item.scrolls + ')';
	}
	
	if (isequip && (gender == 0 || gender == 1)) {
		GetObj('item_info_title').innerHTML += ' (' + (gender == 0 ? 'Male' : 'Female') + ')';
		
	}
	
	GetObj('item_info_title').style.color = GetQualityColor(otherinfo.quality);
	
	GetObj('item_info_icon').src = obj.src;
	
	SetObjTextIsEquip('reqlevel', reqs.level);
	SetObjTextIsEquip('reqstr', reqs.str);
	SetObjTextIsEquip('reqdex', reqs.dex);
	SetObjTextIsEquip('reqint', reqs.int);
	SetObjTextIsEquip('reqluk', reqs.luk);
	SetObjTextIsEquip('reqpop', reqs.pop);
	SetObjTextIsEquip('itemlevel', item.itemlevel);
	SetObjTextIsEquip('itemexp', item.itemexp);
	
	
	
	SetObjText('str', item.str);
	SetObjText('dex', item.dex);
	SetObjText('int', item.int);
	SetObjText('luk', item.luk);
	
	SetObjText('maxhp', item.maxhp);
	SetObjText('maxmp', item.maxmp);
	SetObjText('weaponatt', item.weaponatt);
	SetObjText('weapondef', item.weapondef);
	SetObjText('magicatt', item.magicatt);
	SetObjText('magicdef', item.magicdef);
	SetObjText('acc', item.acc);
	SetObjText('avo', item.avo);
	SetObjText('jump', item.jump);
	SetObjText('speed', item.speed);
	SetObjText('slots', item.slots);
	SetObjText('hands', item.hands);
	SetObjText('hammers', item.hammers, isequip);
	
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
	
	var haspotential = false;
	var hasbonuspotential = false;
	var hasnebulite = false;
	
	if (isequip) {
		if ((item.statusflag & 0x0001) != 0 && item.potential1 == 0 && item.potential2 == 0 && item.potential3 == 0) {
			var row = GetObj('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td width="150px">Hidden Potential.</td> </tr>';
			haspotential = true;
		}
		if ((item.statusflag & 0x0020) != 0 && item.potential4 == 0 && item.potential5 == 0 && item.potential6 == 0) {
			var row = GetObj('bonus_potentials').insertRow(-1);
			row.innerHTML = '<tr> <td width="150px">Hidden Bonus Potential.</td> </tr>';
			hasbonuspotential = true;
		}
		
		if (item.nebulite1 == 0) {
			GetObj('nebulite_info').innerHTML = '<span style="color: blue">You can mount a nebulite on this item</span>';
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

			var leveldata = potentialinfo.levels[potentiallevel];

			var result = potentialinfo.name;
			for (var leveloption in leveldata) {
				result = result.replace('#' + leveloption, leveldata[leveloption]);
			}

			var row = GetObj(isbonus ? 'bonus_potentials' : 'potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + result + '</td> </tr>';
		}
	}


	GetObj('item_info_potentials').style.display = isequip && haspotential ? 'block' : 'none';
	GetObj('item_info_bonus_potentials').style.display = isequip && hasbonuspotential ? 'block' : 'none';
	GetObj('item_nebulite_info_block').style.display = isequip && hasnebulite ? 'block' : 'none';

	var potentialName = obj.getAttribute('potential');
	GetObj('item_info').setAttribute('class', potentialName != null ? 'potential' + potentialName : '');

	GetObj('extra_item_info').innerHTML += '';

	
	GetObj('item_info').style.display = 'block';
	GetObj('req_job_list').style.display = isequip ? 'block' : 'none';
	
	lastSetWindow = GetObj('item_info');
	
	MoveWindow(event);
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


$(document).ready(function () {
	ChangeInventory(1);
	ChangeSkillList(1);
	ChangePet(0);
	ShowCashEquips(false);
});