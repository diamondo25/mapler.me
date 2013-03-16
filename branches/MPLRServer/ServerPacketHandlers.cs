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
                pConnection.Logger_WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    pConnection.Logger_WriteLine("Already logged in!");
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
                pConnection.Logger_WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    pConnection.Logger_WriteLine("Already logged in!");
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
            pConnection.Logger_WriteLine("Username 1: {0} | Username 2: {1}", username, username2);
            DateTime create_time = DateTime.FromFileTime(pPacket.ReadLong()); // Creation Time
            pPacket.ReadInt(); // 78?
            pPacket.ReadBytes(8); // CC key
            string herpderp = pPacket.ReadString();
            if (herpderp != "")
            {
                pConnection.Logger_WriteLine("Wat is dit: {0}", herpderp);
            }

            ParseLogin(pConnection, username, admin, gender, create_time, qban_time, qban);
        }

        private static void ParseLogin(ClientConnection pConnection, string pUsername, short pAdmin, byte pGender, DateTime pCreateTime, DateTime pQBan, byte pQBanReason)
        {

            pConnection.Logger_WriteLine("[{0}] {1} ({2}) Created at {3}, Gender {4}", pConnection.UserID, pUsername, pAdmin, pCreateTime, pGender);

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
                    pConnection.Logger_WriteLine("User bound to temporary account. Trying to find correct account...");
                    getWebLoginID();
                    if (tmp != pConnection.AccountID)
                    {
                        pConnection.Logger_WriteLine("Found account for user!");
                        Internal_Storage.Store.Instance.KnownUserlist[pConnection.UserID] = pConnection.AccountID;
                    }
                    else
                    {
                        pConnection.Logger_WriteLine("No account found, using temporary...");
                        pConnection.AccountID = tmp;
                    }
                }
                else
                {
                    pConnection.AccountID = tmp;
                }
                pConnection.Logger_WriteLine("Already known (Account ID: {0})", pConnection.AccountID);
            }
            else
            {
                // Check if exists in users_weblogin

                getWebLoginID();

                pConnection.Logger_WriteLine("Creating user for accountID {0}", pConnection.AccountID);

                // Adding user!

                string insertval = MySQL_Connection.QueryQuery(pConnection.AccountID, pConnection.UserID, pUsername, pAdmin, new MySQL_Connection.NowType(), pQBan, pQBanReason, pCreateTime);

                using (var result = MySQL_Connection.Instance.RunQuery("INSERT INTO users VALUES " + insertval) as MySql.Data.MySqlClient.MySqlDataReader)
                {
                    pConnection.Logger_WriteLine("User added to account ID {0}", pConnection.AccountID);
                }

                Internal_Storage.Store.Instance.KnownUserlist.Add(pConnection.UserID, pConnection.AccountID);
            }

            pConnection.SendInfoText("Identified account {0} (made at {1})", pUsername, pCreateTime);
        }


        public static void HandleSpawnPlayer(ClientConnection pConnection, MaplePacket pPacket)
        {
            int id = pPacket.ReadInt();
            byte level = pPacket.ReadByte();
            string name = pPacket.ReadString();
            string successor = pPacket.ReadString();
            string guildname = pPacket.ReadString();
            pConnection.Logger_WriteLine("I see {0}! ID {1} Level {2}{3}", name, id, level, successor.Length == 0 ? "" : " (" + name + "'s Successor)");

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

                    pConnection.Logger_WriteLine("{0} must be in Guild {1}", pConnection.LastLoggedName, guild.Name);
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
                pConnection.Logger_WriteLine("{0} leveled up to level {1}!!!", pConnection.CharData.Stats.Name, level);
            }
            if (CheckFlag(updateFlag, 0x20))
            {
                didsomething = true;
                var jobid = pPacket.ReadShort();
                Timeline.Instance.PushJobUP(pConnection.CharacterInternalID, (ushort)jobid);
                pConnection.CharData.Stats.JobID = jobid;
                pConnection.Logger_WriteLine("{0} changed to job {1}!!!", pConnection.CharData.Stats.Name, jobid);
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
                pConnection.CharData.Stats.SPData.Clear();

                if (GameHelper.IsExtendedSPJob(pConnection.CharData.Stats.JobID))
                {

                    byte amnt = pPacket.ReadByte();
                    List<byte> haslist = new List<byte>();
                    for (int j = 0; j < amnt; j++)
                    {
                        byte v1 = pPacket.ReadByte(); // Job ID
                        int v2 = pPacket.ReadInt(); // Amount
                        pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(v1, v2));

                        haslist.Add(v1);
                    }
                    for (byte j = 1; j < 20; j++)
                    {
                        if (!haslist.Contains(j))
                            pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(j, 0));
                    }

                }
                else
                {
                    pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(0, pPacket.ReadShort()));
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
                        pConnection.Logger_WriteLine("Result Skills: {0}", result);

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
                    byte inv = pPacket.ReadByte();
                    short slot = pPacket.ReadShort();
                    inv -= 1;

                    if (type4 == 0) // New Item
                    {

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

                        using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items"))
                        {
                            Queries.SaveItem(pConnection, inv, slot, item, itemsTable);
                            MySQL_Connection.Instance.RunQuery(itemsTable.ToString());

                            if (item is ItemPet)
                            {
                                var pet = item as ItemPet;
                                using (InsertQueryBuilder petTable = new InsertQueryBuilder("pets"))
                                {
                                    Queries.SavePet(pet, petTable);
                                    MySQL_Connection.Instance.RunQuery(petTable.ToString());
                                }
                            }
                        }


                    }
                    else if (type4 == 1) // Update amount
                    {
                        short amount = pPacket.ReadShort();
                        if (inv == 0)
                        {
                            pConnection.Logger_WriteLine("WUTWUT"); // Should _never_ happen
                            continue;
                        }

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

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }


                    }
                    else if (type4 == 2) // Swap
                    {
                        short slotfrom = slot;
                        short slotto = pPacket.ReadShort();

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
                            inventory.InventoryItems[inv - 1].Add((byte)slotto, item);

                        }

                        if (founditem) // New slot contained item
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("inventory", inv);
                                itemTable.SetWhereColumn("slot", slotto);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("slot", slotto);
                            itemTable.SetWhereColumn("inventory", inv);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }

                        if (founditem) // Fix other slot
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom);
                                itemTable.SetWhereColumn("inventory", inv);
                                itemTable.SetWhereColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }
                    }
                    else if (type4 == 3)
                    {
                        // Drop item.

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

                            int result = (int)MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }
                    }








                    else if (type4 == 4)
                    {
                        pPacket.ReadInt(); // Unknown..?
                    }
                    else if (type4 == 5)
                    {
                        // 'Swap' items from and to bags
                        inv -= 1;

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        short to = pPacket.ReadShort();
                        byte slotto = (byte)(to % 100);
                        byte bagto = (byte)(to / 100);


                        slotfrom -= 1;
                        slotto -= 1;
                        if (bagto == 0)
                            bagto = 255;
                        else
                            bagto -= 1;
                        if (bagfrom == 0)
                            bagfrom = 255;
                        else
                            bagfrom -= 1;


                        if (
                            (bagfrom != 255 && bagto != 255) ||
                            (bagfrom == bagto) || // Check if the item is being moved to itself or something

                            (bagfrom == 255 && !inventory.InventoryItems[inv].ContainsKey(slotfrom)) ||
                            (bagfrom != 255 && (!inventory.BagItems.ContainsKey(bagfrom) || !inventory.BagItems[bagfrom].Items.ContainsKey(slotfrom))) ||

                            (bagto != 255 && !inventory.BagItems.ContainsKey(bagto)) // Only check if bag exists
                            )
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag !!!");
                            continue;
                        }

                        bool founditem = false;

                        byte invto = bagto == 255 ? inv : (byte)(10 + bagto);
                        byte invfrom = bagfrom == 255 ? inv : (byte)(10 + bagfrom);

                        if (bagfrom == 255)
                        {
                            // Move to bag
                            ItemBase ib = inventory.InventoryItems[inv][slotfrom];
                            if (inventory.BagItems[bagto].Items.ContainsKey(slotto))
                            {
                                inventory.InventoryItems[inv][slotfrom] = inventory.BagItems[bagto].Items[slotto];
                                inventory.BagItems[bagto].Items.Remove(slotto);
                                founditem = true;
                            }

                            inventory.BagItems[bagto].Items.Add(slotto, ib);



                        }
                        else
                        {
                            // Move to normal slot
                            ItemBase ib = inventory.BagItems[bagfrom].Items[slotfrom];
                            if (inventory.InventoryItems[inv].ContainsKey(slotto))
                            {
                                inventory.BagItems[bagfrom].Items[slotfrom] = inventory.InventoryItems[inv][slotto];
                                inventory.InventoryItems[inv].Remove(slotto);
                            }

                            inventory.InventoryItems[inv].Add(slotto, ib);



                            using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items"))
                            {
                                Queries.SaveItem(pConnection, inv, slot, ib, itemsTable);
                                MySQL_Connection.Instance.RunQuery(itemsTable.ToString());
                            }


                        }

                        /*
                         * Item A: item being used to move/swap | inv 3, slot 21 (Etc) | slotfrom, invfrom
                         * Item B: item that is being swapped with A | inv 11, slot 3 (Bag 2) | slotto, invto
                         * 
                         * Move B to a temp slot, to the new inventory: inv 11 -> inv 3, slot 3 -> slot 3021
                         * Move A to B: inv 3 -> inv 11, slot 21 -> slot 3
                         * Move B to A: slot 3021 -> slot 21
                         * 
                        */


                        if (founditem) // New slot contained item
                        {
                            // Temporary moving item
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom + 3000);
                                itemTable.SetColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("inventory", invto);
                                itemTable.SetWhereColumn("slot", slotto);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("slot", slotto);
                            itemTable.SetColumn("inventory", invto);
                            itemTable.SetWhereColumn("inventory", invfrom);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }

                        if (founditem) // Fix other slot
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom);
                                itemTable.SetWhereColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }
                    }
                    else if (type4 == 6)
                    {
                        // Update bag item amount

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        short amount = pPacket.ReadShort();


                        slotfrom -= 1;
                        if (bagfrom == 0)
                        {
                            pConnection.Logger_WriteLine("Invalid item bag!");
                            continue;
                        }
                        else
                            bagfrom -= 1;

                        byte invfrom = (byte)(10 + bagfrom);

                        if (
                            !inventory.BagItems.ContainsKey(bagfrom) || !inventory.BagItems[bagfrom].Items.ContainsKey(slotfrom)
                            )
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag (item did not exist)!!!");
                            continue;
                        }



                        ItemBase item = inventory.BagItems[bagfrom].Items[slotfrom];
                        item.Amount = amount;

                        Internal_Storage.Store.Instance.SetChecksumOfSlot(pConnection.CharacterID, pConnection.WorldID, inv, slot, item.GetChecksum());

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("amount", amount);
                            itemTable.SetColumn("checksum", item.GetChecksum());
                            itemTable.SetWhereColumn("inventory", invfrom);
                            itemTable.SetWhereColumn("slot", slot);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }



                    }
                    else if (type4 == 8)
                    {
                        // Swap/move item in bags
                        inv -= 2; // 4 = use, internally it's 1 (no equip, starts with 0 O.o)

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        short to = pPacket.ReadShort();
                        byte slotto = (byte)(to % 100);
                        byte bagto = (byte)(to / 100);

                        slotfrom -= 1;
                        slotto -= 1;
                        bagto -= 1;
                        bagfrom -= 1;


                        byte invto = (byte)(10 + bagto);
                        byte invfrom = (byte)(10 + bagfrom);


                        if (!inventory.BagItems.ContainsKey(bagfrom) || !inventory.BagItems.ContainsKey(bagto))
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag");
                            continue;
                        }

                        if (!inventory.BagItems[bagfrom].Items.ContainsKey(slotfrom))
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag (item not found)");
                            continue;
                        }

                        ItemBase item = inventory.BagItems[bagfrom].Items[slotfrom];

                        bool founditem = false;

                        if (inventory.BagItems[bagto].Items.ContainsKey(slotto))
                        {
                            // Swap

                            inventory.BagItems[bagfrom].Items[slotfrom] = inventory.BagItems[bagto].Items[slotto];
                            inventory.BagItems[bagto].Items.Remove(slotto); // Delete item
                            founditem = true;
                        }
                        inventory.BagItems[bagto].Items.Add(slotto, item);




                        if (founditem) // New slot contained item
                        {
                            // Temporary moving item
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom + 3000);
                                itemTable.SetColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("inventory", invto);
                                itemTable.SetWhereColumn("slot", slotto);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("slot", slotto);
                            itemTable.SetColumn("inventory", invto);
                            itemTable.SetWhereColumn("inventory", invfrom);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }

                        if (founditem) // Fix other slot
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom);
                                itemTable.SetWhereColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }



                    }
                    else if (type4 == 10)
                    {
                        pConnection.Logger_WriteLine("Player probably removed some bag item... O.o?");
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

                pConnection.Logger_WriteLine("--------- Started parsing Character Info ----------");

                CharacterData data = new CharacterData();
                data.Decode(pConnection, pPacket);

                data.SaveData(pConnection);

                pConnection.CharData = data;

                pConnection.Logger_WriteLine("--------- Saved parsed Character Info ----------");


                pConnection.LastLoggedName = pConnection.CharData.Stats.Name;
                pConnection.LastLoggedDate = pConnection.CharData.Stats.DateThing.ToString();


                pConnection.SendInfoText("Your character {0} has been added to the database!", pConnection.LastLoggedName);
            }
            else
            {
                pPacket.ReadByte();

                int mapid = pPacket.ReadInt();
                byte mappos = pPacket.ReadByte();
                pConnection.Logger_WriteLine("New MapID: {0} ({1})", mapid, mappos);

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

            if (!isConnecting)
            {
                DateTime servertime = DateTime.FromFileTime(pPacket.ReadLong());
                pConnection.Logger_WriteLine("Servertime: {0}", servertime.ToString());
                pPacket.ReadInt(); // 100?
                pPacket.ReadByte(); // 0
                pPacket.ReadByte(); // 0
                pPacket.ReadByte(); // 1
            }

            pConnection.SendTimeUpdate();
        }
    }
}
