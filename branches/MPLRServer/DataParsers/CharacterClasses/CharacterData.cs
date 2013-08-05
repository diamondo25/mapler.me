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

        public string MarriedWith { get; private set; }


        public class EvolutionCard
        {
            // wut
            public short ID { get; set; }
            public int ItemID { get; set; }
            public byte Level { get; set; }
            public byte Block { get; set; }

            public void Decode(ClientConnection pConnection, MaplePacket pPacket)
            {
                ID = pPacket.ReadShort();
                ItemID = pPacket.ReadInt();
                Level = (byte)pPacket.ReadInt();
            }
        }


        public class Ring
        {
            public enum Type
            {
                Couple,
                Friend,
                Marriage
            }

            public Ring.Type RingType { get; private set; }
            public int FriendID { get; private set; }
            public string FriendName { get; private set; }
            public long RingCashID1 { get; private set; }
            public long RingCashID2 { get; private set; }

            public Ring(Ring.Type pType, MaplePacket pPacket, string pOwnName = null)
            {
                RingType = pType;
                if (pType == Type.Marriage)
                {
                    int marriageID = pPacket.ReadInt();

                    int characterID = pPacket.ReadInt();
                    int partnerID = pPacket.ReadInt();
                    pPacket.ReadShort(); // Most likely 3, marriage type?
                    int characterItemID = pPacket.ReadInt();
                    int partnerItemID = pPacket.ReadInt();
                    string characterName = pPacket.ReadString(13);
                    string partnerName = pPacket.ReadString(13);

                    if (pOwnName != FriendName)
                    {
                        FriendID = partnerID;
                        FriendName = partnerName;
                        RingCashID1 = characterItemID;
                        RingCashID2 = partnerItemID;
                    }
                    else
                    {
                        FriendID = characterID;
                        FriendName = characterName;
                        RingCashID1 = partnerItemID;
                        RingCashID2 = characterItemID;
                    }
                }
                else
                {
                    FriendID = pPacket.ReadInt();
                    FriendName = pPacket.ReadString(13);
                    RingCashID1 = pPacket.ReadLong();
                    RingCashID2 = pPacket.ReadLong();

                    if (pType == Type.Friend)
                    {
                        int itemID = pPacket.ReadInt();
                    }

                }
            }
        }

        public List<int> UnknownIntegerListNumber2 { get; private set; }
        public Dictionary<int, long> UnknownIntegerListNumber3 { get; private set; }
        public Dictionary<long, long> UnknownIntegerListNumber4 { get; private set; }
        public List<EvolutionCard> EvolutionCards { get; private set; }
        public List<Ring> Rings { get; private set; }
        public List<Tuple<byte, int, byte>> Abilities { get; private set; }

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

            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadLong();
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


            for (int i = pPacket.ReadInt(); i > 0; i--) // V.137
            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt(); 
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }

            pPacket.ReadInt();
            for (int i = 6; i > 0; i--)
            {
                pPacket.ReadInt();
            }


            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                pPacket.ReadInt();
            }


            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }


            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }

            Inventory = new CharacterInventory();
            Inventory.Decode(pConnection, pPacket);

            //UnknownIntegerListNumber3 = new Dictionary<int, long>();
            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                pPacket.ReadInt();
                pPacket.ReadLong();
                //UnknownIntegerListNumber3.Add(pPacket.ReadInt(), pPacket.ReadLong());
            }

            //UnknownIntegerListNumber4 = new Dictionary<long, long>();
            for (int i = pPacket.ReadInt(); i > 0; i--)
            {
                pPacket.ReadLong();
                pPacket.ReadLong();
                //UnknownIntegerListNumber4.Add(pPacket.ReadLong(), pPacket.ReadLong());
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


            {

                Rings = new List<Ring>();
                MarriedWith = null;

                // Couple
                for (int i = pPacket.ReadShort(); i > 0; i--)
                {
                    Rings.Add(new Ring(Ring.Type.Couple, pPacket));
                }

                // Friend
                for (int i = pPacket.ReadShort(); i > 0; i--)
                {
                    Rings.Add(new Ring(Ring.Type.Friend, pPacket));
                }

                // Marriage
                for (int i = pPacket.ReadShort(); i > 0; i--)
                {
                    Ring ring = new Ring(Ring.Type.Marriage, pPacket, Stats.Name);
                    Rings.Add(ring);

                    MarriedWith = ring.FriendName;
                }
            }


            Inventory.DecodeTeleportRocks(pPacket);


            Monsterbook = new CharacterMonsterBook();
            Monsterbook.Decode(pPacket);

            pPacket.ReadInt(); // -1?

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                pPacket.ReadShort();
            }

            {
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
            }

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
                int unk = pPacket.ReadInt(); // 9010040 | Conor (NPC)
                if (cnt > 0 && unk > 0)
                {
                    for (short j = 0; j < cnt; j++)
                    {
                        pPacket.ReadInt(); // 9010040 | Conor (NPC)
                        pPacket.ReadShort();
                        pPacket.ReadInt(); // 4330019 | Pink Coin Purse
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
            Abilities = new List<Tuple<byte, int, byte>>();
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                byte id = pPacket.ReadByte(); // 'ID'
                int skillid = pPacket.ReadInt(); // Skill ID
                byte level = pPacket.ReadByte(); // Level
                pPacket.ReadByte(); // Rank
                Abilities.Add(new Tuple<byte, int, byte>(id, skillid, level));
            }

            {
                // V.134
                for (int i = pPacket.ReadInt(); i > 0; i--)
                {
                    pPacket.ReadString();

                    pPacket.ReadInt();
                    pPacket.ReadString();

                    for (int j = pPacket.ReadInt(); j > 0; j--)
                    {
                        pPacket.ReadByte();
                    }
                }

                pPacket.ReadByte();
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
                ItemBase.DecodeItemData(pConnection, pPacket);
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

            {
                EvolutionCards = new List<EvolutionCard>();

                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    var card = new EvolutionCard();
                    card.Decode(pConnection, pPacket);
                    card.Block = 1;
                    EvolutionCards.Add(card);
                }

                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    var card = new EvolutionCard();
                    card.Decode(pConnection, pPacket);
                    card.Block = 2;
                    EvolutionCards.Add(card);
                }

            }

            {
                // V.134
                for (byte i = pPacket.ReadByte(); i > 0; i--)
                {
                    pPacket.ReadInt();
                    pPacket.ReadLong();
                }
            }

            {
                // V.134
                pPacket.ReadString(); // Creating
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadByte();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();

                pPacket.ReadInt();
                pPacket.ReadInt();
            }

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

            if (pPacket.ReadBool())
            {
                pPacket.ReadByte();
                pPacket.ReadInt();
                pPacket.ReadInt();
            }

            pPacket.ReadInt(); // I DONT EVEN D:
        }

        public int SaveCharacterInfo(ClientConnection pConnection)
        {
            return Queries.AddOrUpdateCharacter(pConnection, 
                Stats.ID, Stats.Name, pConnection.UserID, pConnection.WorldID, pConnection.ChannelID, Stats.Level, Stats.JobID,
                Stats.Str, Stats.Dex, Stats.Int, Stats.Luk,
                Stats.HP, Stats.MaxHP, Stats.MP, Stats.MaxMP, Stats.AP, Stats.SP,
                Stats.EXP, Stats.Fame, Stats.MapID, Stats.MapPos,
                Stats.HonourLevel, Stats.HonourExp, Stats.Mesos, Stats.DemonMark,
                Stats.Gender, Stats.Skin,
                Stats.Face, Stats.Hair,
                Inventory.InventorySlots, BlessingOfTheFairy, BlessingOfEmpress, UltimateExplorer,
                Stats.Pets, Stats.Traits, 
                MarriedWith
                );
        }

        public bool SaveData(ClientConnection pConnection)
        {
            int internalid = pConnection.CharacterInternalID = SaveCharacterInfo(pConnection);
            if (internalid == -1)
            {
                // Failure!
                return false;
            }

            using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items"))
            using (InsertQueryBuilder petTable = new InsertQueryBuilder("pets"))
            {
                if (Internal_Storage.Store.Instance.KnownCharlist.ContainsKey(Stats.ID) &&
                    Internal_Storage.Store.Instance.KnownCharlist[Stats.ID].ContainsKey(pConnection.WorldID))
                {
                    // Update info!
                    var internalinfo = Internal_Storage.Store.Instance.KnownCharlist[Stats.ID][pConnection.WorldID];

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
                                    Queries.SavePet(pConnection.CharacterInternalID, item as ItemPet, petTable);
                                }
                            }
                        }
                    }
                    pConnection.Logger_WriteLine("Done items");

                    foreach (var bag in Inventory.BagItems)
                    {
                        ushort i = (ushort)bag.Key;
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
                                    Queries.SavePet(pConnection.CharacterInternalID, item as ItemPet, petTable);
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
                                Queries.SavePet(pConnection.CharacterInternalID, item as ItemPet, petTable);
                            }
                        }
                    }

                    pConnection.Logger_WriteLine("Done items");

                    foreach (var bag in Inventory.BagItems)
                    {
                        ushort i = (ushort)(bag.Key);
                        foreach (var itemdata in bag.Value.Items)
                        {
                            var item = itemdata.Value;
                            Queries.SaveItem(pConnection, i, itemdata.Key, item, itemsTable, true);

                            if (item is ItemPet)
                            {
                                Queries.SavePet(pConnection.CharacterInternalID, item as ItemPet, petTable);
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



                MySQL_Connection.Instance.RunQuery("DELETE FROM quests_running WHERE character_id = " + internalid);
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

                MySQL_Connection.Instance.RunQuery("DELETE FROM quests_done WHERE character_id = " + internalid);
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

                MySQL_Connection.Instance.RunQuery("DELETE FROM quests_running_party WHERE character_id = " + internalid);
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

                MySQL_Connection.Instance.RunQuery("DELETE FROM quests_done_party WHERE character_id = " + internalid);
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

                MySQL_Connection.Instance.RunQuery("DELETE FROM skills WHERE character_id = " + internalid);
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

                MySQL_Connection.Instance.RunQuery("DELETE FROM sp_data WHERE character_id = " + internalid);
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

                using (InsertQueryBuilder teleportRocks = new InsertQueryBuilder("teleport_rock_locations"))
                {
                    teleportRocks.OnDuplicateUpdate = true;

                    teleportRocks.AddColumn("character_id");
                    teleportRocks.AddColumn("index");
                    teleportRocks.AddColumn("map", true);

                    for (int i = 0; i < Inventory.TeleportRocks.Length; i++)
                    {
                        teleportRocks.AddRow(
                            pConnection.CharacterInternalID,
                            i,
                            Inventory.TeleportRocks[i]
                            );

                    }

                    teleportRocks.RunQuery();
                }

                MySQL_Connection.Instance.RunQuery("DELETE FROM evolution_levels WHERE character_id = " + internalid);
                using (InsertQueryBuilder table = new InsertQueryBuilder("evolution_levels"))
                {
                    table.AddColumn("character_id");
                    table.AddColumn("block");
                    table.AddColumn("index");
                    table.AddColumn("card", true);
                    table.AddColumn("level", true);

                    foreach (var card in EvolutionCards)
                    {
                        table.AddRow(
                            pConnection.CharacterInternalID,
                            card.Block,
                            card.ID,
                            card.ItemID,
                            card.Level
                            );

                    }

                    table.RunQuery();
                }


                MySQL_Connection.Instance.RunQuery("DELETE FROM character_rings WHERE character_id = " + pConnection.CharacterInternalID);
                using (InsertQueryBuilder table = new InsertQueryBuilder("character_rings"))
                {
                    table.AddColumn("character_id");
                    table.AddColumn("friend_name");
                    table.AddColumn("cashid1");
                    table.AddColumn("cashid2");
                    table.AddColumn("type");

                    foreach (var ring in Rings)
                    {
                        string type = "";
                        switch (ring.RingType)
                        {
                            case Ring.Type.Couple: type = "couple"; break;
                            case Ring.Type.Marriage: type = "marriage"; break;
                            case Ring.Type.Friend: type = "friend"; break;
                        }

                        table.AddRow(
                            pConnection.CharacterInternalID,
                            ring.FriendName,
                            ring.RingCashID1,
                            ring.RingCashID1,
                            type
                            );

                    }

                    table.RunQuery();
                }


                MySQL_Connection.Instance.RunQuery("DELETE FROM character_abilities WHERE character_id = " + pConnection.CharacterInternalID);
                using (InsertQueryBuilder table = new InsertQueryBuilder("character_abilities"))
                {
                    table.AddColumn("character_id");
                    table.AddColumn("id");
                    table.AddColumn("skill_id");
                    table.AddColumn("level");

                    foreach (var stat in Abilities)
                    {
                        table.AddRow(
                            pConnection.CharacterInternalID,
                            stat.Item1,
                            stat.Item2,
                            stat.Item3
                            );
                    }

                    table.RunQuery();
                }

                return true;
            }
        }
    }
}
