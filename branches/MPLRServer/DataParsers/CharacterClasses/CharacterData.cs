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
            public void Decode(ClientConnection pConnection, MaplePacket pPacket)
            {
                Values = new int[AmountOfValues];

                string tmp = "";

                for (int i = 0; i < AmountOfValues; i++)
                {
                    Values[i] = pPacket.ReadInt();
                    tmp += string.Format("{0} |", Values[i]);
                }

                pConnection.Logger_WriteLine("ULOI: {0}", tmp);
            }
        }

        public List<UnknownListOfIntegers> UnknownIntegerList { get; private set; }
        public List<int> UnknownIntegerListNumber2 { get; private set; }
        public Dictionary<int, long> UnknownIntegerListNumber3 { get; private set; }
        public Dictionary<long, long> UnknownIntegerListNumber4 { get; private set; }

        public void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            pPacket.Skip(8); // Flag

            pPacket.Skip(1);

            { // V.132
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }

            int tmp = pPacket.ReadByte();
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
                vals.Decode(pConnection, pPacket);
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
            Inventory.Decode(pConnection, pPacket);

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
            Skills.Decode(pConnection, pPacket);

            Quests = new CharacterQuests();
            Quests.Decode(pConnection, pPacket);

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

            Quests.DecodePQ(pConnection, pPacket);

            if (GameHelper.IsWildHunter(Stats.JobID))
            {
                pPacket.ReadByte(); // Level

                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }

            Quests.DecodePQDone(pConnection, pPacket);

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

            {
                byte unk = pPacket.ReadByte();
                if (unk == 1)
                {
                    while (true)
                    {
                        tmp = pPacket.ReadUShort();
                        if (tmp <= 0) break;

                        while (true)
                        {
                            ushort tmp2 = pPacket.ReadUShort();
                            if (tmp2 <= 0) break;

                            pPacket.ReadInt();
                            pPacket.ReadInt();
                        }
                    }
                }
                else
                {
                    while (true)
                    {
                        tmp = pPacket.ReadUShort();
                        if (tmp <= 0) break;

                        pPacket.ReadUShort();
                        pPacket.ReadInt();
                        pPacket.ReadInt();
                    }
                }
            }

            if (pPacket.ReadBool())
            {
                // Wat.
                ItemBase unkitem = ItemBase.DecodeItemData(pPacket);
            }


            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadByte();
            }


            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadLong();
            }

            pPacket.ReadInt(); // New v.132

            pPacket.Skip(84); // I don't even

            pPacket.ReadByte();

            {
                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadShort();
                    pPacket.ReadShort();
                }
            }

            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();

                pPacket.Skip(32);
            }

            pPacket.ReadInt(); // I DONT EVEN D:
        }

        public void SaveCharacterInfo(ClientConnection pConnection)
        {
            Queries.AddOrUpdateCharacter(pConnection, 
                Stats.ID, Stats.Name, pConnection.UserID, pConnection.WorldID, pConnection.ChannelID, Stats.Level, Stats.JobID,
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

            InsertQueryBuilder petTable = new InsertQueryBuilder("pets");

            int internalid = 0;


            if (Internal_Storage.Store.Instance.KnownCharlist.ContainsKey(Stats.ID) &&
                Internal_Storage.Store.Instance.KnownCharlist[Stats.ID].ContainsKey(pConnection.WorldID))
            {
                // Update info!
                var internalinfo = Internal_Storage.Store.Instance.KnownCharlist[Stats.ID][pConnection.WorldID];
                internalid = internalinfo.InternalID;

                pConnection.CharacterInternalID = internalid;
                pConnection.CharacterID = Stats.ID;

                CharacterInventory inventory = Inventory;

                for (byte i = 0; i < Inventory.EquipmentItems.Length; i++)
                {
                    var equips = Inventory.EquipmentItems[i];
                    foreach (var equipdata in equips)
                    {
                        short slot = equipdata.Key;
                        var equip = equipdata.Value;
                        bool addrow = false;

                        if (internalinfo.SlotHashes.ContainsKey(0) && internalinfo.SlotHashes[0].ContainsKey(slot))
                        {
                            int hash = internalinfo.SlotHashes[0][slot];
                            int objhash = equipdata.Value.GetChecksum();
                            if (hash != objhash)
                            {
                                pConnection.Logger_WriteLine("{0} != {1}", hash, objhash);
                                addrow = true;
                            }

                            internalinfo.SlotHashes[0].Remove(slot);
                        }
                        else
                        {
                            addrow = true;
                        }

                        if (addrow)
                            Queries.SaveItem(pConnection, 0, slot, equip, itemsTable, true);
                    }
                }

                pConnection.Logger_WriteLine("Done equips");

                for (int i = 0; i < Inventory.InventoryItems.Length; i++)
                {
                    var items = Inventory.InventoryItems[i];
                    foreach (var itemdata in items)
                    {
                        var item = itemdata.Value;
                        bool addrow = false;
                        if (internalinfo.SlotHashes.ContainsKey(i + 1) && internalinfo.SlotHashes[i + 1].ContainsKey(itemdata.Key))
                        {
                            int hash = internalinfo.SlotHashes[i + 1][itemdata.Key];
                            int objhash = itemdata.Value.GetChecksum();
                            if (hash != objhash)
                            {
                                pConnection.Logger_WriteLine("{0} != {1}", hash, objhash);
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
                            Queries.SaveItem(pConnection, (byte)(i + 1), itemdata.Key, item, itemsTable, true);

                            if (item is ItemPet)
                            {
                                Queries.SavePet(item as ItemPet, petTable);
                            }
                        }
                    }
                }
                pConnection.Logger_WriteLine("Done items");

                foreach (var bag in Inventory.BagItems)
                {
                    byte i = (byte)(10 + bag.Key);
                    foreach (var itemdata in bag.Value.Items)
                    {
                        var item = itemdata.Value;
                        bool addrow = false;
                        if (internalinfo.SlotHashes.ContainsKey(i) && internalinfo.SlotHashes[i].ContainsKey(itemdata.Key))
                        {
                            int hash = internalinfo.SlotHashes[i][itemdata.Key];
                            int objhash = itemdata.Value.GetChecksum();
                            if (hash != objhash)
                            {
                                pConnection.Logger_WriteLine("{0} != {1}", hash, objhash);
                                addrow = true;
                            }


                            internalinfo.SlotHashes[i].Remove(itemdata.Key);
                        }
                        else
                        {
                            addrow = true;
                        }

                        if (addrow)
                        {
                            Queries.SaveItem(pConnection, i, itemdata.Key, item, itemsTable, true);

                            if (item is ItemPet)
                            {
                                Queries.SavePet(item as ItemPet, petTable);
                            }
                        }
                    }
                }

                pConnection.Logger_WriteLine("Done bag items");

                {
                    string removequery = "";
                    bool added = false;
                    foreach (var hashlist in internalinfo.SlotHashes)
                    {
                        foreach (var leftovers in hashlist.Value)
                        {
                            pConnection.Logger_WriteLine("Deleting item @ inv {0} slot {1}", hashlist.Key, leftovers.Key);
                            added = true;
                            removequery += string.Format("DELETE FROM items WHERE character_id = {2} AND inventory = {0} AND slot = {1};\r\n", hashlist.Key, leftovers.Key, internalid);
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

                pConnection.Logger_WriteLine("Saving Items");

                using (var result = MySQL_Connection.Instance.RunQuery("SELECT internal_id FROM characters WHERE id = " + Stats.ID + " AND world_id = " + pConnection.WorldID + "") as MySql.Data.MySqlClient.MySqlDataReader)
                {
                    if (result.Read())
                    {
                        internalid = result.GetInt32(0);
                    }
                    else
                    {
                        pConnection.Logger_WriteLine("OH GOD COULD NOT GET INTERNAL ID");
                        return;
                    }
                }

                pConnection.CharacterInternalID = internalid;
                pConnection.CharacterID = Stats.ID;

                string itemlist = "";
                itemlist += "INSERT INTO items VALUES ";
                List<string> querylist = new List<string>();
                for (byte i = 0; i < Inventory.EquipmentItems.Length; i++)
                {
                    var equips = Inventory.EquipmentItems[i];
                    foreach (var equipdata in equips)
                    {
                        var equip = equipdata.Value;
                        Queries.SaveItem(pConnection, 0, equipdata.Key, equip, itemsTable, true);
                    }
                }

                pConnection.Logger_WriteLine("Done equips");

                for (int i = 0; i < Inventory.InventoryItems.Length; i++)
                {
                    var items = Inventory.InventoryItems[i];
                    foreach (var itemdata in items)
                    {
                        var item = itemdata.Value;

                        Queries.SaveItem(pConnection, (byte)(i + 1), itemdata.Key, item, itemsTable, true);

                        if (item is ItemPet)
                        {
                            Queries.SavePet(item as ItemPet, petTable);
                        }
                    }
                }

                pConnection.Logger_WriteLine("Done items");

                foreach (var bag in Inventory.BagItems)
                {
                    byte i = (byte)(10 + bag.Key);
                    foreach (var itemdata in bag.Value.Items)
                    {
                        var item = itemdata.Value;
                        Queries.SaveItem(pConnection, i, itemdata.Key, item, itemsTable, true);

                        if (item is ItemPet)
                        {
                            Queries.SavePet(item as ItemPet, petTable);
                        }
                    }
                }

                pConnection.Logger_WriteLine("Done bag items");

                Internal_Storage.Store.Instance.LoadBaseData(Stats.Name);

            }

            itemsTable.RunQuery();

            pConnection.Logger_WriteLine("Saved item data");

            petTable.RunQuery();

            pConnection.Logger_WriteLine("Saved pet data");


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

                questsTable.RunQuery("insert-update-quests.sql");
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

                doneTable.RunQuery("insert-update-quests-done.sql");
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

                questsTable.RunQuery();
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

                doneTable.RunQuery();
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

                skillTable.RunQuery();
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

                spTable.RunQuery();
            }


            itemsTable.Dispose();
            itemsTable = null;
            petTable.Dispose();
            petTable = null;
        }
    }
}
