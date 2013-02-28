using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class CharacterData
    {
        public GW_CharacterStat Stats { get; private set; }
        public CharacterInventory Inventory { get; private set; }
        public CharacterSkills Skills { get; private set; }
        public CharacterQuests Quests { get; private set; }
        public CharacterMonsterBook Monsterbook { get; private set; }

        public byte BuddylistSize { get; private set; }

        public string BlessingOfTheFairy { get; private set; }
        public string BlessingOfEmpress { get; private set; }
        public string UltimateExplorer { get; private set; }

        public class UnknownListOfIntegers
        {
            public const int AmountOfValues = 4;

            public int[] Values { get; private set; }
            public void Decode(MaplePacket pPacket)
            {
                Values = new int[AmountOfValues];

                string tmp = "";

                for (int i = 0; i < AmountOfValues; i++)
                {
                    Values[i] = pPacket.ReadInt();
                    tmp += string.Format("{0} |", Values[i]);
                }

                Logger.WriteLine("ULOI: {0}", tmp);
            }
        }

        public List<UnknownListOfIntegers> UnknownIntegerList { get; private set; }
        public List<int> UnknownIntegerListNumber2 { get; private set; }
        public Dictionary<int, long> UnknownIntegerListNumber3 { get; private set; }
        public Dictionary<long, long> UnknownIntegerListNumber4 { get; private set; }

        public void Decode(MaplePacket pPacket)
        {
            Stats = new GW_CharacterStat();
            Stats.Decode(pPacket);

            this.BuddylistSize = pPacket.ReadByte();

            if (pPacket.ReadBool()) BlessingOfTheFairy = pPacket.ReadString();
            else BlessingOfTheFairy = null;
            if (pPacket.ReadBool()) BlessingOfEmpress = pPacket.ReadString();
            else BlessingOfEmpress = null;
            if (pPacket.ReadBool()) UltimateExplorer = pPacket.ReadString();
            else UltimateExplorer = null;

            Stats.DecodeMesos(pPacket); // .-.

            // Unknown stuff here

            UnknownIntegerList = new List<UnknownListOfIntegers>();
            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                UnknownListOfIntegers vals = new UnknownListOfIntegers();
                vals.Decode(pPacket);
                UnknownIntegerList.Add(vals);
            }

            // Magical potion pots!!!
            for (int i = pPacket.ReadInt(); i > 0; i--) // V.126
            {
                pPacket.ReadInt(); // Potion pot ID
                pPacket.ReadInt(); // Max value
                pPacket.ReadInt(); // HP
                pPacket.ReadInt(); // ??? (Not max value of MP)
                pPacket.ReadInt(); // MP

                pPacket.ReadLong(); // Start date O.o?
                pPacket.ReadLong(); // End date O.o?
            }

            Inventory = new CharacterInventory();
            Inventory.Decode(pPacket);

            UnknownIntegerListNumber3 = new Dictionary<int, long>();
            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                UnknownIntegerListNumber3.Add(pPacket.ReadInt(), pPacket.ReadLong());
            }

            UnknownIntegerListNumber4 = new Dictionary<long, long>();
            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                UnknownIntegerListNumber4.Add(pPacket.ReadLong(), pPacket.ReadLong());
            }


            while (true)
            {

                byte val = pPacket.ReadByte();
                if (val == 0) break;
                {
                    pPacket.ReadInt();
                    pPacket.ReadByte();
                    pPacket.ReadByte();
                    pPacket.ReadInt();
                    pPacket.ReadInt();
                    pPacket.ReadInt();
                    pPacket.ReadInt();
                    pPacket.ReadByte();
                    pPacket.ReadInt();
                    pPacket.ReadLong();
                    pPacket.ReadLong();
                    pPacket.ReadLong();
                    pPacket.ReadLong();
                }
            }


            Skills = new CharacterSkills();
            Skills.Decode(pPacket);

            Quests = new CharacterQuests();
            Quests.Decode(pPacket);

            // Match
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }

            // Couple
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                int partnerID = pPacket.ReadInt();
                string partnerName = pPacket.ReadString(13);
                long ringCashID1 = pPacket.ReadLong();
                long ringCashID2 = pPacket.ReadLong();
            }

            // Friend
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                int partnerID = pPacket.ReadInt();
                string partnerName = pPacket.ReadString(13);
                long ringCashID1 = pPacket.ReadLong();
                long ringCashID2 = pPacket.ReadLong();
                int itemID = pPacket.ReadInt();
            }

            // Marriage
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                int marriageID = pPacket.ReadInt();

                int characterID = pPacket.ReadInt();
                int partnerID = pPacket.ReadInt();
                pPacket.ReadShort(); // Most likely 3
                int characterItemID = pPacket.ReadInt();
                int partnerItemID = pPacket.ReadInt();
                string characterName = pPacket.ReadString(13);
                string partnerName = pPacket.ReadString(13);
            }

            Inventory.DecodeTeleportRocks(pPacket);


            Monsterbook = new CharacterMonsterBook();
            Monsterbook.Decode(pPacket);

            pPacket.ReadInt(); // -1?

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                pPacket.ReadShort();
            }

            // Newyear cards... meh
            pPacket.ReadShort();
            /*
             	StartNode("Cards");
		        for (i = 0; i < tmp; i++) {
			        StartNode("Card " + i);
			        AddInt("?");
			        AddInt("?");
			        AddString("?");
			        AddByte("?");
			        AddLong("?");
			        AddInt("?");
			        AddString("?");
			        AddByte("?");
			        AddByte("?");
			        AddLong("?");
			        AddString("?");
			        EndNode(false);
		        }
		        EndNode(false);
             */

            Quests.DecodePQ(pPacket);

            // Wildhunter
            if (GameHelper.IsWildHunter(Stats.JobID))
            {
                pPacket.ReadByte(); // Level

                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }

            Quests.DecodePQDone(pPacket);

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                short cnt = pPacket.ReadShort();
                int unk = pPacket.ReadInt();
                if (cnt > 0 && unk > 0)
                {
                    for (short j = 0; j < cnt; j++)
                    {
                        pPacket.ReadInt();
                        pPacket.ReadShort();
                        pPacket.ReadInt();
                        pPacket.ReadShort();
                    }
                }
            }

            for (int i = 13; i > 0; i--)
            {
                pPacket.ReadInt(); // Stolen Skills
            }

            for (int i = 4; i > 0; i--)
            {
                pPacket.ReadInt(); // Chosen Skills?
            }

            // Inner Stats
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                pPacket.ReadByte();
                pPacket.ReadInt(); // Skill ID
                pPacket.ReadByte(); // Level
                pPacket.ReadByte(); // Rank
            }

            Stats.HonourLevel = pPacket.ReadInt();
            Stats.HonourExp = pPacket.ReadInt();
        }

        public void SaveCharacterInfo(ClientConnection pConnection)
        {
            Queries.AddOrUpdateCharacter(Stats.ID, Stats.Name, pConnection.UserID, pConnection.WorldID, Stats.Level, Stats.JobID,
                Stats.Str, Stats.Dex, Stats.Int, Stats.Luk,
                Stats.HP, Stats.MaxHP, Stats.MP, Stats.MaxMP, Stats.AP, Stats.SP,
                Stats.EXP, Stats.Fame, Stats.MapID, Stats.MapPos,
                Stats.HonourLevel, Stats.HonourExp, Stats.Mesos, Stats.DemonMark,
                Stats.Gender, Stats.Skin,
                Stats.Face, Stats.Hair,
                Inventory.InventorySlots, BlessingOfTheFairy, BlessingOfEmpress, UltimateExplorer
                );
        }

        public void SaveData(ClientConnection pConnection)
        {
            SaveCharacterInfo(pConnection);

            InsertQueryBuilder itemsTable = new InsertQueryBuilder("items");
            itemsTable.OnDuplicateUpdate = true;
            itemsTable.AddColumn("character_id", false);
            itemsTable.AddColumn("itemid", true);
            itemsTable.AddColumn("inventory", false);
            itemsTable.AddColumn("slot", false);
            itemsTable.AddColumn("checksum", true);
            itemsTable.AddColumns(true, "cashid", "amount", "expires", "slots", "scrolls", "str", "dex", "int", "luk", "maxhp", "maxmp", "weaponatt", "weapondef", "magicatt", "magicdef", "acc", "avo", "hands", "jump", "speed", "name", "flags", "hammers", 
                 "itemlevel", "itemexp",
                "potential1", "potential2", "potential3", "potential4", "potential5", "socketstate", 
                "socket1", "socket2", "socket3");

            InsertQueryBuilder petTable = new InsertQueryBuilder("pets");
            petTable.OnDuplicateUpdate = true;
            petTable.AddColumn("cashid", false);
            petTable.AddColumn("name", true);
            petTable.AddColumn("closeness", true);
            petTable.AddColumn("fullness", true);
            petTable.AddColumn("level", true);

            int internalid = 0;


            if (Internal_Storage.Store.Instance.KnownCharlist.ContainsKey(Stats.ID) &&
                Internal_Storage.Store.Instance.KnownCharlist[Stats.ID].ContainsKey(pConnection.WorldID))
            {
                // Update info!
                var internalinfo = Internal_Storage.Store.Instance.KnownCharlist[Stats.ID][pConnection.WorldID];
                internalid = internalinfo.InternalID;
                Dictionary<byte, short> updatethese = new Dictionary<byte, short>();
                Dictionary<byte, short> addthese = new Dictionary<byte, short>();

                CharacterInventory inventory = Inventory;

                for (byte i = 0; i < Inventory.EquipmentItems.Length; i++)
                {
                    var equips = Inventory.EquipmentItems[i];
                    foreach (var equipdata in equips)
                    {
                        short slot = equipdata.Key;
                        var equip = equipdata.Value;
                        bool addrow = false;

                        if (internalinfo.SlotHashes[0].ContainsKey(slot))
                        {
                            int hash = internalinfo.SlotHashes[0][slot];
                            int objhash = equipdata.Value.GetChecksum();
                            if (hash != objhash)
                            {
                                Logger.WriteLine("{0} != {1}", hash, objhash);
                                addrow = true;
                            }

                            internalinfo.SlotHashes[0].Remove(slot);
                        }
                        else
                        {
                            addrow = true;
                        }

                        if (addrow)
                        {
                            itemsTable.AddRow(
                                internalid,
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
                    }
                }

                Logger.WriteLine("Done equips");

                for (int i = 0; i < Inventory.InventoryItems.Length; i++)
                {
                    var items = Inventory.InventoryItems[i];
                    foreach (var itemdata in items)
                    {
                        var item = itemdata.Value;
                        bool addrow = false;
                        if (internalinfo.SlotHashes[i + 1].ContainsKey(itemdata.Key))
                        {
                            int hash = internalinfo.SlotHashes[i + 1][itemdata.Key];
                            int objhash = itemdata.Value.GetChecksum();
                            if (hash != objhash)
                            {
                                Logger.WriteLine("{0} != {1}", hash, objhash);
                                addrow = true;
                            }


                            internalinfo.SlotHashes[i + 1].Remove(itemdata.Key);
                        }
                        else
                        {
                            addrow = true;
                        }

                        if (addrow)
                        {
                            string name = item is ItemRechargable ? (item as ItemRechargable).CraftName : null;
                            int flags = item is ItemRechargable ? (item as ItemRechargable).Flags : 0;

                            itemsTable.AddRow(
                                internalid,
                                item.ItemID,
                                i + 1,
                                itemdata.Key,
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

                            if (item is ItemPet)
                            {
                                var pet = item as ItemPet;
                                petTable.AddRow(pet.CashID, pet.Petname, pet.Closeness, pet.Fullness, pet.Level);
                            }
                        }
                    }
                }

                Logger.WriteLine("Done items");

                {
                    string removequery = "";
                    bool added = false;
                    for (byte i = 0; i < internalinfo.SlotHashes.Length; i++)
                    {
                        foreach (var leftovers in internalinfo.SlotHashes[i])
                        {
                            Logger.WriteLine("Deleting item @ inv {0} slot {1}", i, leftovers.Key);
                            added = true;
                            removequery += string.Format("DELETE FROM items WHERE character_id = {2} AND inventory = {0} AND slot = {1};\r\n", i, leftovers.Key, internalid);
                        }
                    }
                    if (added)
                    {
                        System.IO.File.WriteAllText("removequery.sql", removequery);
                        MySQL_Connection.Instance.RunQuery(removequery);
                    }
                }

            }
            else
            {
                Logger.WriteLine("Saving Items");

                using (var result = MySQL_Connection.Instance.RunQuery("SELECT internal_id FROM characters WHERE id = " + Stats.ID + " AND world_id = " + pConnection.WorldID + "") as MySql.Data.MySqlClient.MySqlDataReader)
                {
                    if (result.Read())
                    {
                        internalid = result.GetInt32(0);
                    }
                    else
                    {
                        Logger.WriteLine("OH GOD COULD NOT GET INTERNAL ID");
                        return;
                    }
                }


                string itemlist = "";
                itemlist += "INSERT INTO items VALUES ";
                List<string> querylist = new List<string>();
                for (byte i = 0; i < Inventory.EquipmentItems.Length; i++)
                {
                    var equips = Inventory.EquipmentItems[i];
                    foreach (var equipdata in equips)
                    {
                        var equip = equipdata.Value;

                        itemsTable.AddRow(
                            internalid,
                            equip.ItemID,
                            0,
                            equipdata.Key,
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
                }

                Logger.WriteLine("Done equips");

                for (int i = 0; i < Inventory.InventoryItems.Length; i++)
                {
                    var items = Inventory.InventoryItems[i];
                    foreach (var itemdata in items)
                    {
                        var item = itemdata.Value;

                        string name = item is ItemRechargable ? (item as ItemRechargable).CraftName : null;
                        int flags = item is ItemRechargable ? (item as ItemRechargable).Flags : 0;

                        itemsTable.AddRow(
                            internalid,
                            item.ItemID,
                            i + 1,
                            itemdata.Key,
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

                        if (item is ItemPet)
                        {
                            var pet = item as ItemPet;
                            petTable.AddRow(pet.CashID, pet.Petname, pet.Closeness, pet.Fullness, pet.Level);
                        }
                    }
                }

                Logger.WriteLine("Done items");


                Internal_Storage.Store.Instance.LoadBaseData(Stats.Name);

            }

            if (itemsTable.RowCount > 0)
            {
                string q = itemsTable.ToString();
                System.IO.File.WriteAllText("insert-update.sql", q);
                int result = (int)MySQL_Connection.Instance.RunQuery(q);
                Logger.WriteLine("Result: {0}", result);
            }

            Logger.WriteLine("Saved item data");

            if (petTable.RowCount > 0)
            {
                string q = petTable.ToString();
                System.IO.File.WriteAllText("insert-update-pet.sql", q);
                int result = (int)MySQL_Connection.Instance.RunQuery(q);
                Logger.WriteLine("Result: {0}", result);
            }

            Logger.WriteLine("Saved item data");
            Internal_Storage.Store.Instance.LoadInventoryHashes(internalid, true);

            using (InsertQueryBuilder questsTable = new InsertQueryBuilder("quests_running"))
            {
                questsTable.OnDuplicateUpdate = true;
                questsTable.AddColumn("character_id", false);
                questsTable.AddColumn("questid", true);
                questsTable.AddColumn("data", true);

                foreach (var quest in Quests.Running)
                {
                    questsTable.AddRow(internalid, quest.Key, quest.Value);
                }

                if (questsTable.RowCount > 0)
                {
                    string q = questsTable.ToString();
                    System.IO.File.WriteAllText("insert-update-quests.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                    Logger.WriteLine("Result Quests Running: {0}", result);
                }
            }

            using (InsertQueryBuilder doneTable = new InsertQueryBuilder("quests_done")) 
            {
                doneTable.AddColumn("character_id", false);
                doneTable.AddColumn("questid", false);
                doneTable.AddColumn("time", false);

                foreach (var quest in Quests.Done)
                {
                    doneTable.AddRow(internalid, quest.Key, quest.Value);
                }

                if (doneTable.RowCount > 0)
                {
                    string q = doneTable.ToString();
                    System.IO.File.WriteAllText("insert-update-quests-done.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                    Logger.WriteLine("Result Quests Done: {0}", result);
                }
            }

            using (InsertQueryBuilder questsTable = new InsertQueryBuilder("quests_running_party"))
            {
                questsTable.OnDuplicateUpdate = true;
                questsTable.AddColumn("character_id", false);
                questsTable.AddColumn("questid", true);
                questsTable.AddColumn("data", true);

                foreach (var quest in Quests.PartyQuestsRunning)
                {
                    questsTable.AddRow(internalid, quest.Key, quest.Value);
                }

                if (questsTable.RowCount > 0)
                {
                    string q = questsTable.ToString();
                    System.IO.File.WriteAllText("insert-update-quests.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                    Logger.WriteLine("Result Party Quests Running: {0}", result);
                }
            }

            using (InsertQueryBuilder doneTable = new InsertQueryBuilder("quests_done_party"))
            {
                doneTable.AddColumn("character_id", false);
                doneTable.AddColumn("questid", false);
                doneTable.AddColumn("time", false);

                foreach (var quest in Quests.PartyQuestsDone)
                {
                    doneTable.AddRow(internalid, quest.Key, quest.Value);
                }

                if (doneTable.RowCount > 0)
                {
                    string q = doneTable.ToString();
                    System.IO.File.WriteAllText("insert-update-quests-done.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                    Logger.WriteLine("Result Party Quests Done: {0}", result);
                }
            }

            using (InsertQueryBuilder skillTable = new InsertQueryBuilder("skills"))
            {
                skillTable.OnDuplicateUpdate = true;
                skillTable.AddColumn("character_id", false);
                skillTable.AddColumn("skillid", false);
                skillTable.AddColumn("level", true);
                skillTable.AddColumn("maxlevel", true);
                skillTable.AddColumn("expires", true);

                foreach (var skill in Skills.SkillList)
                {
                    skillTable.AddRow(internalid, skill.Key, skill.Value.Level, skill.Value.MasterLevel != -1 ? (object)skill.Value.MasterLevel : null, skill.Value.Expiration);
                }

                if (skillTable.RowCount > 0)
                {
                    string q = skillTable.ToString();
                    System.IO.File.WriteAllText("insert-update-skills.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                    Logger.WriteLine("Result Skills: {0}", result);
                }
            }

            using (InsertQueryBuilder spTable = new InsertQueryBuilder("sp_data"))
            {
                spTable.OnDuplicateUpdate = true;
                spTable.AddColumn("character_id", false);
                spTable.AddColumn("slot", false);
                spTable.AddColumn("amount", true);

                foreach (var kvp in Stats.SPData)
                {
                    spTable.AddRow(internalid, kvp.Key, kvp.Value);
                }

                if (spTable.RowCount > 0)
                {
                    string q = spTable.ToString();
                    System.IO.File.WriteAllText("insert-update-sp_data.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                    Logger.WriteLine("Result sp_data: {0}", result);
                }

            }

            pConnection.CharacterInternalID = internalid;
            pConnection.CharacterID = Internal_Storage.Store.Instance.KnownCharlist_INTERNAL[internalid].ID;

            itemsTable.Dispose();
            itemsTable = null;
            petTable.Dispose();
            petTable = null;
        }
    }
}
