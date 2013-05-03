using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class Queries
    {
        public static int AddOrUpdateCharacter(
            ClientConnection pConnection, int pID, string pName, int pUserID, byte pWorldID, byte pChannelID, byte pLevel,
            short pJob, short pStr, short pDex, short pInt, short pLuk,
            int pHP, int pMaxHP, int pMP, int pMaxMP, short pAP, short pSP,
            int pEXP, int pFame, int pMap, byte pMapPos,
            int pHonourLevel, int pHonourEXP, int pMesos, int pDemonMark,
            byte pGender, byte pSkin,
            int pEyes, int pHair,

            byte[] pSlots, string pBoF, string pBoE, string pUE
            )
        {
            if (Internal_Storage.Store.Instance.KnownCharlist.ContainsKey(pID) &&
                Internal_Storage.Store.Instance.KnownCharlist[pID].ContainsKey(pWorldID))
            {
                int internal_id = Internal_Storage.Store.Instance.KnownCharlist[pID][pWorldID].InternalID;
                using (UpdateQueryBuilder query = new UpdateQueryBuilder("characters"))
                {
                    query.SetColumn("name", pName);
                    query.SetColumn("userid", pUserID);
                    query.SetColumn("world_id", pWorldID);
                    query.SetColumn("channel_id", pChannelID);
                    query.SetColumn("level", pLevel);
                    query.SetColumn("job", pJob);
                    query.SetColumn("str", pStr);
                    query.SetColumn("dex", pDex);
                    query.SetColumn("int", pInt);
                    query.SetColumn("luk", pLuk);
                    query.SetColumn("chp", pHP);
                    query.SetColumn("mhp", pMaxHP);
                    query.SetColumn("cmp", pMP);
                    query.SetColumn("mmp", pMaxMP);
                    query.SetColumn("ap", pAP);
                    query.SetColumn("sp", pSP);
                    query.SetColumn("exp", pEXP);
                    query.SetColumn("fame", pFame);
                    query.SetColumn("map", pMap);
                    query.SetColumn("pos", pMapPos);
                    query.SetColumn("gender", pGender);
                    query.SetColumn("skin", pSkin);
                    query.SetColumn("eyes", pEyes);
                    query.SetColumn("hair", pHair);
                    query.SetColumn("last_update", new MySQL_Connection.NowType());

                    query.SetColumn("honourlevel", pHonourLevel);
                    query.SetColumn("honourexp", pHonourEXP);
                    query.SetColumn("mesos", pMesos);
                    query.SetColumn("demonmark", pDemonMark);

                    query.SetColumn("eqp_slots", pSlots[0]);
                    query.SetColumn("use_slots", pSlots[1]);
                    query.SetColumn("setup_slots", pSlots[2]);
                    query.SetColumn("etc_slots", pSlots[3]);
                    query.SetColumn("cash_slots", pSlots[4]);

                    query.SetColumn("blessingoffairy", pBoF);
                    query.SetColumn("blessingofempress", pBoE);
                    query.SetColumn("ultimateexplorer", pUE);

                    query.SetWhereColumn("internal_id", internal_id);

                    string q = query.ToString();
                    System.IO.File.WriteAllText("sql_addorupdate.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);

                    if (result >= 1)
                    {
                        pConnection.Logger_WriteLine("Updated character info: {0}", internal_id);
                    }
                    else
                    {
                        pConnection.Logger_WriteLine("Failed to update character info");
                    }
                }
            }
            else
            {
                using (InsertQueryBuilder query = new InsertQueryBuilder("characters"))
                {
                    query.OnDuplicateUpdate = false;
                    query.AddColumnWithValue("internal_id", null);

                    query.AddColumnWithValue("id", pID);
                    query.AddColumnWithValue("name", pName);
                    query.AddColumnWithValue("userid", pUserID);
                    query.AddColumnWithValue("world_id", pWorldID);
                    query.AddColumnWithValue("channel_id", pChannelID);
                    query.AddColumnWithValue("level", pLevel);
                    query.AddColumnWithValue("job", pJob);
                    query.AddColumnWithValue("str", pStr);
                    query.AddColumnWithValue("dex", pDex);
                    query.AddColumnWithValue("int", pInt);
                    query.AddColumnWithValue("luk", pLuk);
                    query.AddColumnWithValue("chp", pHP);
                    query.AddColumnWithValue("mhp", pMaxHP);
                    query.AddColumnWithValue("cmp", pMP);
                    query.AddColumnWithValue("mmp", pMaxMP);
                    query.AddColumnWithValue("ap", pAP);
                    query.AddColumnWithValue("sp", pSP);
                    query.AddColumnWithValue("exp", pEXP);
                    query.AddColumnWithValue("fame", pFame);
                    query.AddColumnWithValue("map", pMap);
                    query.AddColumnWithValue("pos", pMapPos);
                    query.AddColumnWithValue("gender", pGender);
                    query.AddColumnWithValue("skin", pSkin);
                    query.AddColumnWithValue("eyes", pEyes);
                    query.AddColumnWithValue("hair", pHair);
                    query.AddColumnWithValue("last_update", new MySQL_Connection.NowType());

                    query.AddColumnWithValue("honourlevel", pHonourLevel);
                    query.AddColumnWithValue("honourexp", pHonourEXP);
                    query.AddColumnWithValue("mesos", pMesos);
                    query.AddColumnWithValue("demonmark", pDemonMark);

                    query.AddColumnWithValue("eqp_slots", pSlots[0]);
                    query.AddColumnWithValue("use_slots", pSlots[1]);
                    query.AddColumnWithValue("setup_slots", pSlots[2]);
                    query.AddColumnWithValue("etc_slots", pSlots[3]);
                    query.AddColumnWithValue("cash_slots", pSlots[4]);

                    query.AddColumnWithValue("blessingoffairy", pBoF);
                    query.AddColumnWithValue("blessingofempress", pBoE);
                    query.AddColumnWithValue("ultimateexplorer", pUE);

                    string q = query.ToString();

                    System.IO.File.WriteAllText("sql_addorupdate.sql", q);

                    int result = (int)MySQL_Connection.Instance.RunQuery(q);

                    if (result >= 1)
                    {
                        int id = MySQL_Connection.Instance.GetLastInsertId();
                        pConnection.Logger_WriteLine("Inserted character info: {0}", id);
                    }
                    else
                    {
                        pConnection.Logger_WriteLine("Failed to insert character info");
                    }
                }
            }

            using (var reader = MySQL_Connection.Instance.RunQuery("SELECT internal_id FROM characters WHERE id = " + pID) as MySql.Data.MySqlClient.MySqlDataReader)
            {
                if (reader.Read())
                {
                    return reader.GetInt32(0);
                }
            }
            Logger.WriteLine("!!!!!! Character not found O.O");
            return -1;
        }




        public static string GetAccountOptionValue(int pAccountID, string pKey)
        {
            string query = "SELECT option_value FROM account_options WHERE account_id = " + pAccountID + " AND option_key = '" + MySql.Data.MySqlClient.MySqlHelper.EscapeString(pKey) + "'";
            using (var reader = MySQL_Connection.Instance.RunQuery(query) as MySql.Data.MySqlClient.MySqlDataReader)
            {
                if (reader.Read())
                {
                    return reader.GetString(0);
                }
            }

            return null;
        }

        public static void SaveItem(ClientConnection pConnection, ushort pInventory, short pSlot, ItemBase pItem, InsertQueryBuilder itemsTable, bool pDontSetChecksum = false)
        {
            if (!itemsTable.HasColumns)
            {
                itemsTable.OnDuplicateUpdate = true;
                itemsTable.AddColumn("character_id", false);
                itemsTable.AddColumn("itemid", true);
                itemsTable.AddColumn("inventory", false);
                itemsTable.AddColumn("slot", false);
                itemsTable.AddColumn("checksum", true);
                itemsTable.AddColumns(true, 
                    "cashid", "amount", "expires", "slots", "scrolls", 
                    "str", "dex", "int", "luk", "maxhp", "maxmp", 
                    "weaponatt", "weapondef", "magicatt", "magicdef", "acc", "avo", "hands", "jump", "speed", "name", "flags",
                    "itemlevel", "itemexp",
                    "hammers",
                    "battlemodedmg",
                    "statusflag",
                    "potential1", "potential2", "potential3", 
                    "potential4", "potential5", "potential6",
                    "display_id",
                    "socketstate", 
                    "nebulite1", "nebulite2", "nebulite3",
                    "uniqueid",
                    "bagid"
                    );
            }

            int checksum = pItem.GetChecksum();


            if (pItem is ItemEquip)
            {
                var equip = pItem as ItemEquip;

                itemsTable.AddRow(
                    pConnection.CharacterInternalID,
                    equip.ItemID,
                    pInventory,
                    pSlot,
                    checksum,
                    equip.CashID,
                    equip.Amount,
                    equip.Expires,
                    equip.Slots, equip.Scrolls,
                    equip.Str, equip.Dex, equip.Int, equip.Luk,
                    equip.HP, equip.MP,
                    equip.Watk, equip.Wdef, equip.Matk, equip.Mdef,
                    equip.Acc, equip.Avo, equip.Hands, equip.Jump, equip.Speed,
                    equip.Name, equip.Flags,
                    equip.ItemLevel, equip.ItemEXP, 
                    equip.ViciousHammer, 
                    equip.BattleModeDamage,
                    equip.StatusFlags,
                    equip.Potential1, equip.Potential2, equip.Potential3, 
                    equip.Potential4, equip.Potential5, equip.Potential6,
                    equip.DisplayID,
                    equip.SocketState, 
                    equip.Nebulite1, equip.Nebulite2, equip.Nebulite3,
                    equip.UniqueID,
                    pItem.BagID
                );
            }
            else
            {

                string name = pItem is ItemRechargable ? (pItem as ItemRechargable).CraftName : null;
                int flags = pItem is ItemRechargable ? (pItem as ItemRechargable).Flags : 0;

                itemsTable.AddRow(
                    pConnection.CharacterInternalID,
                    pItem.ItemID,
                    pInventory,
                    pSlot,
                    checksum,
                    pItem.CashID,
                    pItem.Amount,
                    pItem.Expires,
                    null, null,
                    null, null, null, null,
                    null, null,
                    null, null, null, null,
                    null, null, null, null, null,
                    name, flags,
                    null, null, 
                    null, 
                    null,
                    null,
                    null, null, null, 
                    null, null, null,
                    null,
                    null, 
                    null, null, null,
                    pItem is ItemRechargable ? (pItem as ItemRechargable).UniqueID : 0,
                    pItem.BagID
                    );

            }

            if (!pDontSetChecksum)
                Internal_Storage.Store.Instance.SetChecksumOfSlot(pConnection.CharacterID, pConnection.WorldID, pInventory, pSlot, checksum);
        }

        public static void SavePet(ItemPet pPet, InsertQueryBuilder pInsertBuilder)
        {
            if (!pInsertBuilder.HasColumns)
            {
                pInsertBuilder.OnDuplicateUpdate = true;
                pInsertBuilder.AddColumn("cashid", false);
                pInsertBuilder.AddColumn("name", true);
                pInsertBuilder.AddColumn("closeness", true);
                pInsertBuilder.AddColumn("fullness", true);
                pInsertBuilder.AddColumn("level", true);
            }
            pInsertBuilder.AddRow(pPet.CashID, pPet.Petname, pPet.Closeness, pPet.Fullness, pPet.Level);

        }

        public static void SeePlayer(int pCharacterID, string pName, byte pWorld, byte pLevel, string pGuild, int pMapID, int pSeenBy)
        {
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

                characterViewsTable.AddRow(pCharacterID, pWorld, pName, pLevel, pGuild == "" ? null : pGuild, pMapID, new MySQL_Connection.NowType(), pSeenBy);

                characterViewsTable.RunQuery();
            }
        }
    }
}
