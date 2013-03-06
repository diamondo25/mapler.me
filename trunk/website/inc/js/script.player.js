function SetItemInfo(event, obj, itemid, isequip, reqjob, reqlevel, reqstr, reqdex, reqint, reqluk, reqpop, itemlevel, itemexp, str, dex, int, luk, maxhp, maxmp, weaponatt, weapondef, magicatt, magicdef, acc, avo, hands, jump, speed, slots, scrolls, expires, f_lock, f_spikes, f_coldprotection, f_tradeblock, questitem, f_karmad, potentialflag, potential1, potential2, potential3, potential4, potential5, one) {
	document.getElementById('item_info_title').innerHTML = obj.getAttribute('item-name');
	document.getElementById('item_info_icon').src = obj.src;

	document.getElementById('item_info_req_row_reqlevel').style.display = (!isequip && (reqlevel == '' || reqlevel == 0)) ? 'none' : '';
	document.getElementById('item_info_req_reqlevel').innerHTML = reqlevel;

	document.getElementById('item_info_req_row_reqstr').style.display = (!isequip && (reqstr == '' || reqstr == 0)) ? 'none' : '';
	document.getElementById('item_info_req_reqstr').innerHTML = reqstr;

	document.getElementById('item_info_req_row_reqdex').style.display = (!isequip && (reqdex == '' || reqdex == 0)) ? 'none' : '';
	document.getElementById('item_info_req_reqdex').innerHTML = reqdex;

	document.getElementById('item_info_req_row_reqint').style.display = (!isequip && (reqint == '' || reqint == 0)) ? 'none' : '';
	document.getElementById('item_info_req_reqint').innerHTML = reqint;

	document.getElementById('item_info_req_row_reqluk').style.display = (!isequip && (reqluk == '' || reqluk == 0)) ? 'none' : '';
	document.getElementById('item_info_req_reqluk').innerHTML = reqluk;

	document.getElementById('item_info_req_row_reqpop').style.display = (!isequip && (reqpop == '' || reqpop == 0)) ? 'none' : '';
	document.getElementById('item_info_req_reqpop').innerHTML = reqpop;

	document.getElementById('item_info_req_row_itemlevel').style.display = (!isequip && (itemlevel == '' || itemlevel == 0)) ? 'none' : '';
	document.getElementById('item_info_req_itemlevel').innerHTML = itemlevel;

	document.getElementById('item_info_req_row_itemexp').style.display = (!isequip && (itemexp == '' || itemexp == 0)) ? 'none' : '';
	document.getElementById('item_info_req_itemexp').innerHTML = itemexp;



	document.getElementById('item_info_row_str').style.display = (str == 0 || str == '') ? 'none' : '';
	document.getElementById('item_info_str').innerHTML = str;

	document.getElementById('item_info_row_dex').style.display = (dex == 0 || dex == '') ? 'none' : '';
	document.getElementById('item_info_dex').innerHTML = dex;

	document.getElementById('item_info_row_int').style.display = (int == 0 || int == '') ? 'none' : '';
	document.getElementById('item_info_int').innerHTML = int;

	document.getElementById('item_info_row_luk').style.display = (luk == 0 || luk == '') ? 'none' : '';
	document.getElementById('item_info_luk').innerHTML = luk;

	document.getElementById('item_info_row_maxhp').style.display = (maxhp == 0 || maxhp == '') ? 'none' : '';
	document.getElementById('item_info_maxhp').innerHTML = maxhp;

	document.getElementById('item_info_row_maxmp').style.display = (maxmp == 0 || maxmp == '') ? 'none' : '';
	document.getElementById('item_info_maxmp').innerHTML = maxmp;

	document.getElementById('item_info_row_weaponatt').style.display = (weaponatt == 0 || weaponatt == '') ? 'none' : '';
	document.getElementById('item_info_weaponatt').innerHTML = weaponatt;

	document.getElementById('item_info_row_weapondef').style.display = (weapondef == 0 || weapondef == '') ? 'none' : '';
	document.getElementById('item_info_weapondef').innerHTML = weapondef;

	document.getElementById('item_info_row_magicatt').style.display = (magicatt == 0 || magicatt == '') ? 'none' : '';
	document.getElementById('item_info_magicatt').innerHTML = magicatt;

	document.getElementById('item_info_row_magicdef').style.display = (magicdef == 0 || magicdef == '') ? 'none' : '';
	document.getElementById('item_info_magicdef').innerHTML = magicdef;

	document.getElementById('item_info_row_acc').style.display = (acc == 0 || acc == '') ? 'none' : '';
	document.getElementById('item_info_acc').innerHTML = acc;

	document.getElementById('item_info_row_avo').style.display = (avo == 0 || avo == '') ? 'none' : '';
	document.getElementById('item_info_avo').innerHTML = avo;

	document.getElementById('item_info_row_hands').style.display = (hands == 0 || hands == '') ? 'none' : '';
	document.getElementById('item_info_hands').innerHTML = hands;

	document.getElementById('item_info_row_jump').style.display = (jump == 0 || jump == '') ? 'none' : '';
	document.getElementById('item_info_jump').innerHTML = jump;

	document.getElementById('item_info_row_speed').style.display = (speed == 0 || speed == '') ? 'none' : '';
	document.getElementById('item_info_speed').innerHTML = speed;

	document.getElementById('item_info_row_slots').style.display = (slots == 0 || slots == '') ? 'none' : '';
	document.getElementById('item_info_slots').innerHTML = slots;


	var description = descriptions[itemid];

	if (description != '') {
		document.getElementById('item_info_description').style.display = '';
		document.getElementById('item_info_description').innerHTML = description;
	}
	else {
		document.getElementById('item_info_description').style.display = 'none';
	}

	var extrainfo = '';

	if (one)
		extrainfo += '<span>One of a Kind</span>';

	if (questitem)
		extrainfo += '<span>Quest item</span>';

	if (f_lock)
		extrainfo += '<span>Sealed untill ' + expires + '</span>';
	else if (expires != '')
		extrainfo += '<span>Expires on ' + expires + '</span>';
	if (f_spikes)
		extrainfo += '<span>Prevents slipping</span>';
	if (f_coldprotection)
		extrainfo += '<span>Cold prevention</span>';
	if (f_tradeblock) {
		var tradeInfo = 'Untradable';
		switch (f_tradeblock) {
			case 0x10: tradeInfo = 'Use the Sharing Tag to move an item to another character on the same account once.'; break;
			case 0x20: tradeInfo = 'Use the Scissors of Karma to enable an item to be traded one time'; break;
			case 0x21: tradeInfo = 'Use the Platinum Scissors of Karma to enable an item to be traded one time'; break;
			case 0x30: tradeInfo = 'Trade disabled when equipped'; break;
			case 0x10: tradeInfo = 'Can be traded once within an account (Cannot be traded after being moved)'; break;
		}
		extrainfo += '<span>' + tradeInfo + '</span>';
	}
	if (f_karmad)
		extrainfo += '<span>1 time trading (karma\'d)</span>';

	//extrainfo += '<span>ITEMID ' + itemid + '</span>';


	document.getElementById('item_info_extra').innerHTML = extrainfo;
	document.getElementById('item_info_extra').style.display = extrainfo == '' ? 'none' : 'block';

	// Classes

	if (reqjob == 0) reqjob = 255; // All classes
	SetJob(0, reqjob, 0x80); // Beginner
	SetJob(1, reqjob, 0x01); // Warrior
	SetJob(2, reqjob, 0x02); // Magician
	SetJob(3, reqjob, 0x04); // Bowman
	SetJob(4, reqjob, 0x08); // Thief
	SetJob(5, reqjob, 0x10); // Pirate

	document.getElementById('potentials').innerHTML = ""; // Clear potentials

	var potentiallevel = Math.round(reqlevel / 10);
	if (potentiallevel == 0) potentiallevel = 1;

	if (potentialflag == 1) { // 12 = unlocked...?
		var row = document.getElementById('potentials').insertRow(-1);
		row.innerHTML = '<tr> <td width="150px">Hidden Potential.</td> </tr>';
	}

	if (potential1 != 0) {
		var potentialinfo = potentialDescriptions[potential1];
		if (potentialinfo.name != null) {
			var leveldata = potentialinfo.levels[potentiallevel];

			var result = potentialinfo.name;
			for (var leveloption in leveldata) {
				result = result.replace('#' + leveloption, leveldata[leveloption]);
			}

			var row = document.getElementById('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + result + '</td> </tr>';
		}
	}
	if (potential2 != 0) {
		var potentialinfo = potentialDescriptions[potential2];
		if (potentialinfo.name != null) {
			var leveldata = potentialinfo.levels[potentiallevel];

			var result = potentialinfo.name;
			for (var leveloption in leveldata) {
				result = result.replace('#' + leveloption, leveldata[leveloption]);
			}

			var row = document.getElementById('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + result + '</td> </tr>';
		}
	}
	if (potential3 != 0) {
		var potentialinfo = potentialDescriptions[potential3];
		if (potentialinfo.name != null) {
			var leveldata = potentialinfo.levels[potentiallevel];

			var result = potentialinfo.name;
			for (var leveloption in leveldata) {
				result = result.replace('#' + leveloption, leveldata[leveloption]);
			}

			var row = document.getElementById('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + result + '</td> </tr>';
		}
	}
	if (potential4 != 0) {
		var potentialinfo = potentialDescriptions[potential4];
		if (potentialinfo.name != null) {
			var leveldata = potentialinfo.levels[potentiallevel];

			var result = potentialinfo.name;
			for (var leveloption in leveldata) {
				result = result.replace('#' + leveloption, leveldata[leveloption]);
			}

			var row = document.getElementById('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + result + '</td> </tr>';
		}
	}
	if (potential5 != 0) {
		var potentialinfo = potentialDescriptions[potential5];
		if (potentialinfo.name != null) {
			var leveldata = potentialinfo.levels[potentiallevel];

			var result = potentialinfo.name;
			for (var leveloption in leveldata) {
				result = result.replace('#' + leveloption, leveldata[leveloption]);
			}

			var row = document.getElementById('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + result + '</td> </tr>';
		}
	}

	document.getElementById('item_info_potentials').style.display = document.getElementById('potentials').innerHTML == '' ? 'none' : 'block';

	var potentialName = obj.getAttribute('potential');
	document.getElementById('item_info').setAttribute('class', potentialName != null ? 'potential' + potentialName : '');


	document.getElementById('item_info').style.display = 'block';
	document.getElementById('req_job_list').style.display = isequip ? 'block' : 'none';
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
	var expectedBottom = expectedTop + parseInt(document.getElementById('item_info').clientHeight);
	if (document.body.clientHeight < expectedBottom) {
		expectedTop -= (expectedBottom - document.body.clientHeight) + 10;
	}
	document.getElementById('item_info').style.top = expectedTop + 'px';
	document.getElementById('item_info').style.left = event.pageX + 10 + 'px';
}