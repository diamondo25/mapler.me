using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class ServerPacketHandlers
    {
        public static void HandleLogin(ClientConnection pConnection, MaplePacket pPacket)
        {
            int error = pPacket.ReadInt();
            pPacket.ReadShort();
            if (error != 0)
            {
                Logger.WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    Logger.WriteLine("Already logged in!");
                }
                return;
            }

            pConnection.UserID = pPacket.ReadInt();
            byte gender = pPacket.ReadByte(); // Gender or GenderSelect/PinSelect
            pPacket.ReadByte();
            short admin = pPacket.ReadShort();
            pPacket.ReadInt(); // ReadBytes(4)
            pPacket.ReadByte(); // 0x95
            string username = pPacket.ReadString(); // Username

            pPacket.ReadByte(); // 0?


            byte qban = pPacket.ReadByte(); // Quiet Ban
            DateTime qban_time = DateTime.FromFileTime(pPacket.ReadLong()); // Quiet Ban Time
            DateTime create_time = DateTime.FromFileTime(pPacket.ReadLong()); // Creation Time
            pPacket.ReadInt(); // 78?
            pPacket.Skip(2); // 1 1
            pPacket.ReadBytes(8); // CC key

            ParseLogin(pConnection, username, admin, gender, create_time, qban_time, qban);
        }

        public static void HandleLoginFromWeb(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte error = pPacket.ReadByte();
            if (error != 0)
            {
                Logger.WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    Logger.WriteLine("Already logged in!");
                }
                return;
            }

            pConnection.UserID = pPacket.ReadInt();
            byte gender = pPacket.ReadByte(); // Gender or GenderSelect/PinSelect
            pPacket.ReadByte();
            short admin = pPacket.ReadShort();
            pPacket.ReadInt(); // ReadBytes(4)
            pPacket.ReadByte(); // 0x95
            string username = pPacket.ReadString(); // Username

            pPacket.ReadByte(); // 0?


            byte qban = pPacket.ReadByte(); // Quiet Ban
            DateTime qban_time = DateTime.FromFileTime(pPacket.ReadLong()); // Quiet Ban Time
            string username2 = pPacket.ReadString();
            Logger.WriteLine("Username 1: {0} | Username 2: {1}", username, username2);
            DateTime create_time = DateTime.FromFileTime(pPacket.ReadLong()); // Creation Time
            pPacket.ReadInt(); // 78?
            pPacket.ReadBytes(8); // CC key
            string herpderp = pPacket.ReadString();
            if (herpderp != "")
            {
                Logger.WriteLine("Wat is dit: {0}", herpderp);
            }

            ParseLogin(pConnection, username, admin, gender, create_time, qban_time, qban);
        }

        private static void ParseLogin(ClientConnection pConnection, string pUsername, short pAdmin, byte pGender, DateTime pCreateTime, DateTime pQBan, byte pQBanReason)
        {

            Logger.WriteLine("[{0}] {1} ({2}) Created at {3}, Gender {4}", pConnection.UserID, pUsername, pAdmin, pCreateTime, pGender);

            pConnection.AccountID = 2;

            Action getWebLoginID = delegate
            {
                using (var result = MySQL_Connection.Instance.RunQuery("SELECT account_id FROM users_weblogin WHERE name = '" + MySql.Data.MySqlClient.MySqlHelper.EscapeString(pUsername) + "'") as MySql.Data.MySqlClient.MySqlDataReader)
                {
                    if (result.Read())
                    {
                        pConnection.AccountID = result.GetInt32(0);
                    }
                }
            };

            if (Internal_Storage.Store.Instance.KnownUserlist.ContainsKey(pConnection.UserID))
            {
                int tmp = Internal_Storage.Store.Instance.KnownUserlist[pConnection.UserID];
                if (tmp == 2)
                {
                    Logger.WriteLine("User bound to temporary account. Trying to find correct account...");
                    getWebLoginID();
                    if (tmp != pConnection.AccountID)
                    {
                        Logger.WriteLine("Found account for user!");
                        Internal_Storage.Store.Instance.KnownUserlist[pConnection.UserID] = pConnection.AccountID;
                    }
                    else
                    {
                        Logger.WriteLine("No account found, using temporary...");
                        pConnection.AccountID = tmp;
                    }
                }
                else
                {
                    pConnection.AccountID = tmp;
                }
                Logger.WriteLine("Already known (Account ID: {0})", pConnection.AccountID);
            }
            else
            {
                // Check if exists in users_weblogin

                getWebLoginID();

                Logger.WriteLine("Creating user for accountID {0}", pConnection.AccountID);

                // Adding user!

                string insertval = MySQL_Connection.QueryQuery(pConnection.AccountID, pConnection.UserID, pUsername, pAdmin, new MySQL_Connection.NowType(), pQBan, pQBanReason, pCreateTime);

                using (var result = MySQL_Connection.Instance.RunQuery("INSERT INTO users VALUES " + insertval) as MySql.Data.MySqlClient.MySqlDataReader)
                {
                    Logger.WriteLine("User added to account ID {0}", pConnection.AccountID);
                }

                Internal_Storage.Store.Instance.KnownUserlist.Add(pConnection.UserID, pConnection.AccountID);
            }
        }


        public static void HandleSpawnPlayer(ClientConnection pConnection, MaplePacket pPacket)
        {
            int id = pPacket.ReadInt();
            byte level = pPacket.ReadByte();
            string name = pPacket.ReadString();
            string successor = pPacket.ReadString();
            string guildname = pPacket.ReadString();
            Logger.WriteLine("I see {0}! ID {1} Level {2}{3}", name, id, level, successor.Length == 0 ? "" : " (" + name + "'s Successor)");

            using (InsertQueryBuilder characterViewsTable = new InsertQueryBuilder("character_views"))
            {
                characterViewsTable.OnDuplicateUpdate = true;
                characterViewsTable.AddColumn("character_id", false);
                characterViewsTable.AddColumn("world_id", true);
                characterViewsTable.AddColumn("name", true);
                characterViewsTable.AddColumn("level", true);
                characterViewsTable.AddColumn("guild", true);
                characterViewsTable.AddColumn("mapid", true);
                characterViewsTable.AddColumn("last_seen_when", true);
                characterViewsTable.AddColumn("last_seen_by", true);

                characterViewsTable.AddRow(id, pConnection.WorldID, name, level, guildname == "" ? null : guildname, pConnection.CharData.Stats.MapID, new MySQL_Connection.NowType(), pConnection.CharacterInternalID);

                MySQL_Connection.Instance.RunQuery(characterViewsTable.ToString());
            }
        }


        public static void HandleGuild(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            if (type == 0x20)
            {
                bool hasGuild = pPacket.ReadBool();
                if (hasGuild)
                {
                    Guild guild = new Guild();
                    guild.Decode(pPacket);
                    guild.Save(pConnection.WorldID);

                    Logger.WriteLine("{0} must be in Guild {1}", pConnection.LastLoggedName, guild.Name);
                }
            }
        }

        public static bool CheckFlag(long pFlag, long pExpectedFlag)
        {
            return (pFlag & pExpectedFlag) == pExpectedFlag;
        }


        public static void HandleStatUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            pPacket.ReadByte();
            long updateFlag = pPacket.ReadLong();
            if (updateFlag == 0) return; // Fake Update -.- / Unstuck

            bool didsomething = false;

            if (CheckFlag(updateFlag, 1)) // Skin
            {
                didsomething = true;
                pConnection.CharData.Stats.Skin = pPacket.ReadByte();
            }
            if (CheckFlag(updateFlag, 2)) // Eyes
            {
                didsomething = true;
                pConnection.CharData.Stats.Face = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 4)) // Eyes
            {
                didsomething = true;
               pConnection.CharData.Stats.Hair =  pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 8))
            {
                didsomething = true;
               pConnection.CharData.Stats.Pets[0] = pPacket.ReadLong();
            }
            if (CheckFlag(updateFlag, 0x80000))
            {
                pPacket.ReadLong();
            }
            if (CheckFlag(updateFlag, 0x100000))
            {
                pPacket.ReadLong();
            }
            if (CheckFlag(updateFlag, 0x10))
            {
                didsomething = true;
                var level = pPacket.ReadByte();
                Timeline.Instance.PushLevelUP(pConnection.CharacterInternalID, level);
                pConnection.CharData.Stats.Level = level;
                Logger.WriteLine("{0} leveled up to level {1}!!!", pConnection.CharData.Stats.Name, level);
            }
            if (CheckFlag(updateFlag, 0x20))
            {
                didsomething = true;
                var jobid = pPacket.ReadShort();
                Timeline.Instance.PushJobUP(pConnection.CharacterInternalID, (ushort)jobid);
                pConnection.CharData.Stats.JobID = jobid;
                Logger.WriteLine("{0} changed to job {1}!!!", pConnection.CharData.Stats.Name, jobid);
            }
            if (CheckFlag(updateFlag, 0x40))
            {
                didsomething = true;
                pConnection.CharData.Stats.Str = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x80))
            {
                didsomething = true;
                pConnection.CharData.Stats.Dex = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x100))
            {
                didsomething = true;
                pConnection.CharData.Stats.Int =  pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x200))
            {
                didsomething = true;
                pConnection.CharData.Stats.Luk = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x400))
            {
                didsomething = true;
                pConnection.CharData.Stats.HP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x800))
            {
                didsomething = true;
                pConnection.CharData.Stats.MaxHP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x1000))
            {
                didsomething = true;
                pConnection.CharData.Stats.MP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x2000))
            {
                didsomething = true;
               pConnection.CharData.Stats.MaxMP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x4000))
            {
                didsomething = true;
               pConnection.CharData.Stats.AP =  pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x8000))
            {
                didsomething = true;
                short a1 = pConnection.CharData.Stats.JobID;

                if (a1 / 1000 == 3 ||
                    a1 / 100 == 22 || a1 == 2001 ||
                    a1 / 100 == 23 || a1 == 2002 ||
                    a1 / 100 == 24 || a1 == 2003 ||
                    a1 / 100 == 51 || a1 == 5000 ||
                    a1 / 100 == 27 || a1 == 2004 ||
                    a1 / 100 == 61 || a1 == 6000 ||
                    a1 / 100 == 65 || a1 == 6001 ||
                    a1 / 10 == 57 || a1 == 508)
                {
                    pConnection.CharData.Stats.SPData.Clear();
                    byte amnt = pPacket.ReadByte();
                    for (int j = 0; j < amnt; j++)
                    {
                        byte v1 = pPacket.ReadByte(); // Job ID
                        int v2 = pPacket.ReadInt(); // Amount
                        pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(v1, v2));
                    }
                }
                else
                {
                    pConnection.CharData.Stats.SP = pPacket.ReadShort();
                }
            }
            if (CheckFlag(updateFlag, 0x10000))
            {
                didsomething = true;
                pConnection.CharData.Stats.EXP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x20000))
            {
                didsomething = true;
                int fame = pPacket.ReadInt();
                Timeline.Instance.PushGotFame(pConnection.CharacterInternalID, fame > pConnection.CharData.Stats.Fame);
                pConnection.CharData.Stats.Fame = fame;
            }
            if (CheckFlag(updateFlag, 0x40000))
            {
                didsomething = true;
                pConnection.CharData.Stats.Mesos = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x200000))
            {
                pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x400000))
            {
                pPacket.ReadByte();
            }
            if (CheckFlag(updateFlag, 0x800000))
            {
                pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x1000000))
            {
                pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x2000000))
            {
                pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x4000000))
            {
                pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x8000000))
            {
                pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x10000000))
            {
                pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x20000000))
            {
                pPacket.ReadBytes(21);
            }
            if (CheckFlag(updateFlag, 0x40000000))
            {
                pPacket.ReadByte();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadByte();
            }
            if (CheckFlag(updateFlag, 0x80000000))
            {
                for (byte i = 0; i < 6; i++)
                {
                    pPacket.ReadInt();
                    pPacket.ReadByte();
                    pPacket.ReadInt();
                }
            }
            if (CheckFlag(updateFlag, 0x100000000))
            {
                pPacket.ReadByte();
                pPacket.ReadByte();
            }
            if (CheckFlag(updateFlag, 0x200000000))
            {
                pPacket.ReadInt();
            }

            if (didsomething)
            {
                pConnection.CharData.SaveCharacterInfo(pConnection);

                pConnection.SendTimeUpdate();
            }
        }


        public static void HandleSkillMacros(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte count = pPacket.ReadByte();
            if (count == 0) return;
            string q = string.Format("DELETE FROM skillmacros WHERE character_id = {0};\r\n", pConnection.CharacterInternalID);
            q += "INSERT INTO skillmacros VALUES \r\n";
            for (int i = 0; i < count; i++)
            {
                string name = pPacket.ReadString();
                bool shout = pPacket.ReadBool();
                int skill1 = pPacket.ReadInt();
                int skill2 = pPacket.ReadInt();
                int skill3 = pPacket.ReadInt();
                q += string.Format("({0}, {1}, '{2}', {3}, {4}, {5}, {6}),", pConnection.CharacterInternalID, i, MySql.Data.MySqlClient.MySqlHelper.EscapeString(name), shout, skill1, skill2, skill3);
            }
            q = q.TrimEnd(',');
            MySQL_Connection.Instance.RunQuery(q);
        }

        public static void HandleSkillUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            if (type == 1)
            {
                pPacket.ReadByte();
                ushort amount = pPacket.ReadUShort();

                using (InsertQueryBuilder skillTable = new InsertQueryBuilder("skills"))
                {
                    skillTable.OnDuplicateUpdate = true;
                    skillTable.AddColumn("character_id", false);
                    skillTable.AddColumn("skillid", false);
                    skillTable.AddColumn("level", true);
                    skillTable.AddColumn("maxlevel", true);
                    skillTable.AddColumn("expires", true);

                    for (ushort i = 0; i < amount; i++)
                    {
                        int skillid = pPacket.ReadInt();
                        int level = pPacket.ReadInt();
                        int masterlevel = pPacket.ReadInt();
                        long expiration = pPacket.ReadLong();

                        Timeline.Instance.PushSkillUP(pConnection.CharacterInternalID, skillid, level);

                        skillTable.AddRow(pConnection.CharacterInternalID, skillid, level, masterlevel == 0 ? null : (object)masterlevel, expiration);
                    }

                    if (skillTable.RowCount > 0)
                    {
                        string q = skillTable.ToString();
                        System.IO.File.WriteAllText("insert-update-skills-packet.sql", q);
                        int result = (int)MySQL_Connection.Instance.RunQuery(q);
                        Logger.WriteLine("Result Skills: {0}", result);

                        pConnection.SendTimeUpdate();
                    }

                }
            }
        }

        public static void HandleInventorySlotsUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            CharacterInventory inventory = pConnection.CharData.Inventory;
            byte inv = pPacket.ReadByte();
            byte newslots = pPacket.ReadByte();
            if (inv < 1 || inv > 5) return;
            if (newslots < 24 || newslots > 96) return; // Just to be sure
            inventory.InventorySlots[inv - 1] = newslots;

            string slotname = "";

            switch (inv)
            {
                case 1: slotname = "eqp"; break;
                case 2: slotname = "use"; break;
                case 3: slotname = "setup"; break;
                case 4: slotname = "etc"; break;
                case 5: slotname = "cash"; break;
            }

            string query = string.Format("UPDATE characters SET {0}_slots = {1} WHERE internal_id = {2}", slotname, newslots, pConnection.CharacterInternalID);
            MySQL_Connection.Instance.RunQuery(query);

            pConnection.SendTimeUpdate();
        }

        public static void HandleInventoryUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            CharacterInventory inventory = pConnection.CharData.Inventory;

            byte type1 = pPacket.ReadByte();
            byte items = pPacket.ReadByte();
            byte type3 = pPacket.ReadByte();
            if (type3 == 0) // Add or update item
            {
                for (var amnt = 0; amnt < items; amnt++)
                {
                    byte type4 = pPacket.ReadByte();

                    if (type4 == 0) // New Item
                    {
                        byte inv = pPacket.ReadByte();
                        short slot = pPacket.ReadShort();
                        inv -= 1;

                        ItemBase item = ItemBase.DecodeItemData(pPacket);

                        if (inv == 0)
                        {
                            // Equip
                            byte internalInventory = CharacterInventory.GetEquipInventoryFromSlot(slot);
                            slot = CharacterInventory.CorrectEquipSlot(internalInventory, slot);

                            if (!inventory.EquipmentItems[internalInventory].ContainsKey(slot))
                                inventory.EquipmentItems[internalInventory].Add(slot, item as ItemEquip);
                            else
                                inventory.EquipmentItems[internalInventory][slot] = item as ItemEquip;
                        }
                        else
                        {
                            if (!inventory.InventoryItems[inv - 1].ContainsKey((byte)slot))
                                inventory.InventoryItems[inv - 1].Add((byte)slot, item);
                            else
                                inventory.InventoryItems[inv - 1][(byte)slot] = item;
                        }


                        Internal_Storage.Store.Instance.SetChecksumOfSlot(pConnection.CharacterID, pConnection.WorldID, inv, slot, item.GetChecksum());

                        using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items"))
                        {
                            itemsTable.OnDuplicateUpdate = true;
                            itemsTable.AddColumn("character_id", false);
                            itemsTable.AddColumn("itemid", true);
                            itemsTable.AddColumn("inventory", false);
                            itemsTable.AddColumn("slot", false);
                            itemsTable.AddColumn("checksum", true);
                            itemsTable.AddColumns(true, "cashid", "amount", "expires", "slots", "scrolls", "str", "dex", "int", "luk", "maxhp", "maxmp", "weaponatt", "weapondef", "magicatt", "magicdef", "acc", "avo", "hands", "jump", "speed", "name", "flags", "hammers", 
                                "itemlevel", "itemexp",
                                "potential1", "potential2", "potential3", "potential4", "potential5", 
                                "socketstate", "socket1", "socket2", "socket3");

                            if (item is ItemEquip)
                            {
                                var equip = item as ItemEquip;

                                itemsTable.AddRow(
                                    pConnection.CharacterInternalID,
                                    equip.ItemID,
                                    0,
                                    slot,
                                    equip.GetChecksum(),
                                    equip.CashID,
                                    equip.Amount,
                                    equip.Expires,
                                    equip.Slots, equip.Scrolls,
                                    equip.Str, equip.Dex, equip.Int, equip.Luk,
                                    equip.HP, equip.MP,
                                    equip.Watk, equip.Wdef, equip.Matk, equip.Mdef,
                                    equip.Acc, equip.Avo, equip.Hands, equip.Jump, equip.Speed,
                                    equip.Name, equip.Flags,
                                    equip.ViciousHammer,
                                    equip.ItemLevel, equip.ItemEXP,
                                    equip.Potential1, equip.Potential2, equip.Potential3, equip.Potential4, equip.Potential5,
                                    equip.SocketState, equip.Socket1, equip.Socket2, equip.Socket3
                                );
                            }
                            else
                            {

                                string name = item is ItemRechargable ? (item as ItemRechargable).CraftName : null;
                                int flags = item is ItemRechargable ? (item as ItemRechargable).Flags : 0;

                                itemsTable.AddRow(
                                    pConnection.CharacterInternalID,
                                    item.ItemID,
                                    inv,
                                    slot,
                                    (item is ItemRechargable ? (item as ItemRechargable).GetChecksum() : item.GetChecksum()),
                                    item.CashID,
                                    item.Amount,
                                    item.Expires,
                                    null, null,
                                    null, null, null, null,
                                    null, null,
                                    null, null, null, null,
                                    null, null, null, null, null,
                                    name, flags,
                                    null,
                                    null, null,
                                    null, null, null, null, null,
                                    null, null, null, null
                                    );

                            }

                            {
                                string q2 = itemsTable.ToString();
                                System.IO.File.WriteAllText("insert-item-packet.sql", q2);
                                int result = (int)MySQL_Connection.Instance.RunQuery(q2);
                                Logger.WriteLine("Result Item Addition: {0}", result);
                            }

                            if (item is ItemPet)
                            {
                                var pet = item as ItemPet;
                                using (InsertQueryBuilder petTable = new InsertQueryBuilder("pets"))
                                {
                                    petTable.OnDuplicateUpdate = true;
                                    petTable.AddColumn("cashid", false);
                                    petTable.AddColumn("name", true);
                                    petTable.AddColumn("closeness", true);
                                    petTable.AddColumn("fullness", true);
                                    petTable.AddColumn("level", true);

                                    petTable.AddRow(pet.CashID, pet.Petname, pet.Closeness, pet.Fullness, pet.Level);

                                    string q = petTable.ToString();
                                    System.IO.File.WriteAllText("insert-item-pet-packet.sql", q);
                                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                                    Logger.WriteLine("Result Pets: {0}", result);
                                }
                            }
                        }
                    }
                    else if (type4 == 1) // Update amount
                    {
                        byte inv = pPacket.ReadByte();
                        short slot = pPacket.ReadShort();
                        short amount = pPacket.ReadShort();
                        inv -= 1; // 1 (strange counting of Nexon) + 1 (no equip inventory)

                        ItemBase item = inventory.InventoryItems[inv - 1][(byte)slot];
                        item.Amount = amount;

                        Internal_Storage.Store.Instance.SetChecksumOfSlot(pConnection.CharacterID, pConnection.WorldID, inv, slot, item.GetChecksum());

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("amount", amount);
                            itemTable.SetColumn("checksum", item.GetChecksum());
                            itemTable.SetWhereColumn("inventory", inv);
                            itemTable.SetWhereColumn("slot", slot);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);


                            string q = itemTable.ToString();
                            System.IO.File.WriteAllText("update-item-packet.sql", q);
                            int result = (int)MySQL_Connection.Instance.RunQuery(q);
                            Logger.WriteLine("Result Item Amount Update: {0}", result);

                        }


                    }
                    else if (type4 == 2) // Swap
                    {
                        byte inv = pPacket.ReadByte();
                        short slotfrom = pPacket.ReadShort();
                        short slotto = pPacket.ReadShort();
                        inv -= 1;

                        bool founditem = false;

                        if (inv == 0)
                        {
                            // Equips!
                            byte internalInventoryFrom = CharacterInventory.GetEquipInventoryFromSlot(slotfrom);
                            byte internalInventoryTo = CharacterInventory.GetEquipInventoryFromSlot(slotto);
                            slotfrom = CharacterInventory.CorrectEquipSlot(internalInventoryFrom, slotfrom);
                            slotto = CharacterInventory.CorrectEquipSlot(internalInventoryTo, slotto);

                            // Switch Equips
                            ItemEquip item = inventory.EquipmentItems[internalInventoryFrom][slotfrom];
                            if (inventory.EquipmentItems[internalInventoryTo].ContainsKey(slotto))
                            {
                                inventory.EquipmentItems[internalInventoryFrom][slotfrom] =
                                    inventory.EquipmentItems[internalInventoryTo][slotto];

                                inventory.EquipmentItems[internalInventoryTo].Remove(slotto); // Remove item
                                founditem = true;
                            }
                            else
                            {
                                inventory.EquipmentItems[internalInventoryFrom].Remove(slotfrom);
                            }
                            inventory.EquipmentItems[internalInventoryTo].Add(slotto, item);
                        }
                        else
                        {
                            // Switch Items
                            ItemBase item = inventory.InventoryItems[inv - 1][(byte)slotfrom];
                            if (inventory.InventoryItems[inv - 1].ContainsKey((byte)slotto))
                            {
                                inventory.InventoryItems[inv - 1][(byte)slotfrom] =
                                    inventory.InventoryItems[inv - 1][(byte)slotto];
                                inventory.InventoryItems[inv - 1].Remove((byte)slotto); // Remove item
                                founditem = true;
                            }
                            else
                            {
                                inventory.InventoryItems[inv - 1].Remove((byte)slotfrom);
                            }
                            inventory.InventoryItems[inv - 1][(byte)slotto] = item;

                        }

                        if (founditem) // New slot contained item
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("inventory", inv);
                                itemTable.SetWhereColumn("slot", slotto);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);


                                string q = itemTable.ToString();
                                System.IO.File.WriteAllText("update-item-packet.sql", q);
                                int result = (int)MySQL_Connection.Instance.RunQuery(q);
                                Logger.WriteLine("Result Item Slot Switch FIX1: {0}", result);
                            }
                        }

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("slot", slotto);
                            itemTable.SetWhereColumn("inventory", inv);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);


                            string q = itemTable.ToString();
                            System.IO.File.WriteAllText("update-item-packet.sql", q);
                            int result = (int)MySQL_Connection.Instance.RunQuery(q);
                            Logger.WriteLine("Result Item Slot Switch: {0}", result);
                        }

                        if (founditem) // Fix other slot
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom);
                                itemTable.SetWhereColumn("inventory", inv);
                                itemTable.SetWhereColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);


                                string q = itemTable.ToString();
                                System.IO.File.WriteAllText("update-item-packet.sql", q);
                                int result = (int)MySQL_Connection.Instance.RunQuery(q);
                                Logger.WriteLine("Result Item Slot Switch FIX2: {0}", result);
                            }
                        }
                    }
                    else if (type4 == 3)
                    {
                        // Drop item.
                        byte inv = pPacket.ReadByte();
                        short slot = pPacket.ReadShort();
                        inv -= 1;

                        if (inv == 0)
                        {
                            // Equips!
                            byte internalInventory = CharacterInventory.GetEquipInventoryFromSlot(slot);
                            slot = CharacterInventory.CorrectEquipSlot(internalInventory, slot);

                            if (inventory.EquipmentItems[internalInventory].ContainsKey(slot))
                            {
                                inventory.EquipmentItems[internalInventory].Remove(slot);
                            }
                        }
                        else
                        {
                            if (inventory.InventoryItems[inv - 1].ContainsKey((byte)slot))
                            {
                                inventory.InventoryItems[inv - 1].Remove((byte)slot);
                            }
                        }

                        using (DeleteQueryBuilder itemTable = new DeleteQueryBuilder("items"))
                        {
                            itemTable.SetWhereColumn("inventory", inv);
                            itemTable.SetWhereColumn("slot", slot);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);


                            string q = itemTable.ToString();
                            System.IO.File.WriteAllText("delete-item-packet.sql", q);
                            int result = (int)MySQL_Connection.Instance.RunQuery(q);
                            Logger.WriteLine("Result Item Slot Removal: {0}", result);
                        }
                    }
                }
            }
            pConnection.SendTimeUpdate();
        }


        public static void HandleChangeMap(ClientConnection pConnection, MaplePacket pPacket)
        {
            int tmp = pPacket.ReadShort();
            pPacket.Skip(tmp * (4 + 4));

            int channelid = pPacket.ReadInt();
            pPacket.Skip(1 + 4);
            pPacket.Skip(1); // Portals taken
            pPacket.Skip(4);

            bool isConnecting = pPacket.ReadBool();
            tmp = pPacket.ReadShort(); // Contains Message
            if (tmp > 0)
            {
                pPacket.ReadString(); // Title
                for (int i = 0; i < tmp; i++)
                {
                    pPacket.ReadString(); // Line N
                }
            }

            if (isConnecting)
            {
                pPacket.Skip(12); // RNGs
                pPacket.Skip(8); // Flag
                pPacket.Skip(1);

                tmp = pPacket.ReadByte();
                pPacket.Skip(tmp * 4);

                tmp = pPacket.ReadInt();
                pPacket.Skip(tmp * (4 + 8));


                pPacket.Skip(1);

                if (pPacket.ReadBool())
                {
                    tmp = pPacket.ReadInt();
                    pPacket.Skip(tmp * 8);
                    tmp = pPacket.ReadInt();
                    pPacket.Skip(tmp * 8);
                }


                CharacterData data = new CharacterData();
                data.Decode(pPacket);

                data.SaveData(pConnection);

                pConnection.CharData = data;

                Logger.WriteLine("--------- Saved Character Info ----------");


                pConnection.LastLoggedName = pConnection.CharData.Stats.Name;
                pConnection.LastLoggedDate = pConnection.CharData.Stats.DateThing.ToString();

            }
            else
            {
                pPacket.ReadByte();

                int mapid = pPacket.ReadInt();
                byte mappos = pPacket.ReadByte();
                Logger.WriteLine("New MapID: {0} ({1})", mapid, mappos);

                pConnection.CharData.Stats.MapID = mapid;
                pConnection.CharData.Stats.MapPos = mappos;

                int hp = pPacket.ReadInt();
                pConnection.CharData.Stats.HP = hp;

                bool dosomething = pPacket.ReadBool();
                if (dosomething)
                {
                    pPacket.ReadInt();
                    pPacket.ReadInt();
                }
                MySQL_Connection.Instance.RunQuery(string.Format("UPDATE characters SET chp = {0}, map = {1}, pos = {2} WHERE internal_id = {3}", hp, mapid, mappos, pConnection.CharacterInternalID));
            }

            DateTime servertime = DateTime.FromFileTime(pPacket.ReadLong());
            Logger.WriteLine("Servertime: {0}", servertime.ToString());
            pPacket.ReadInt(); // 100?
            pPacket.ReadByte(); // 0
            pPacket.ReadByte(); // 0
            pPacket.ReadByte(); // 1

            pConnection.SendTimeUpdate();
        }
    }
}
